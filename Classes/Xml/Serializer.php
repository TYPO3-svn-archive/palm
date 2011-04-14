<?php
//namespace Lexa\XmlSerialization;

class Tx_Palm_Xml_Serializer implements t3lib_Singleton {

	/**
	 * @var tslib_cObj
	 */
	protected $contentObject;

	/**
	 * @var Tx_Palm_Configuration_ConfigurationManager
	 */
	protected $configurationManager;

	/**
	 * @var array
	 */
	protected $configuration;

	/**
	 * @var Tx_Extbase_Object_ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * @var Tx_Palm_Reflection_Service
	 */
	protected $reflectionService;

	/**
	 * @var Tx_Extbase_Property_Mapper
	 */
	protected $propertyMapper;

	/**
	 * @var Tx_Extbase_Validation_ValidatorResolver
	 */
	protected $validatorResolver;


	/**
	 * @param tslib_cObj $contentObject
	 * @return void
	 */
	public function injectContentObject(tslib_cObj $contentObject) {
		$this->contentObject = $contentObject;
	}

	/**
	 * @param Tx_Palm_Configuration_ConfigurationManager $configurationManager
	 * @return void
	 */
	public function injectConfigurationManager(Tx_Palm_Configuration_ConfigurationManager $configurationManager) {
		$this->configurationManager = $configurationManager;
		$this->configuration = $configurationManager->getConfiguration(Tx_Palm_Configuration_ConfigurationManager::CONFIGURATION_TYPE_FRAMEWORK);
	}

	/**
	 * Injector Method for object manager
	 * @param Tx_Extbase_Object_ObjectManagerInterface $objectManager
	 */
	public function injectObjectMananger(Tx_Extbase_Object_ObjectManagerInterface $objectManager) {
		$this->objectManager = $objectManager;
	}


	/**
	 * Injector Method for reflection service
	 * @param Tx_Palm_Reflection_Service $reflectionService
	 */
	public function injectReflectionService(Tx_Palm_Reflection_Service $reflectionService) {
		$this->reflectionService = $reflectionService;
	}

	/**
	 * Injector Method for property mapper
	 * @param Tx_Extbase_Property_Mapper $propertyMapper
	 */
	public function injectPropertyMapper(Tx_Extbase_Property_Mapper $propertyMapper) {
		$this->propertyMapper = $propertyMapper;
	}

	/**
	 * Injector Method for validator resolver
	 * @param Tx_Extbase_Validation_ValidatorResolver $validatorResolver
	 */
	public function injectValidatorResolver(Tx_Extbase_Validation_ValidatorResolver $validatorResolver) {
		$this->validatorResolver = $validatorResolver;
	}

	/**
	 * Returns the property mapper
	 *
	 * @return Tx_Extbase_Property_Mapper
	 */
	public function getPropertyMapper() {
		return $this->propertyMapper;
	}
	/**
	 * @param Object $obj
	 * @return DOMDocument
	 */
	public function serialize($obj) {
		$classSchema = $this->reflectionService->getClassSchema($obj);
		$doc = $this->objectManager->create('Tx_Palm_DOM_Document');
		$root = $doc->createElement($classSchema->getXmlRootName());
		$doc->appendChild($root);
		$this->serializeObject($obj, $root);
//		die();
		return $doc;
	}


	/**
	 * @param Object $obj
	 * @param DOMElement $target
	 */
	protected function serializeObject($obj, DOMElement $target) {
		$classSchema = $this->reflectionService->getClassSchema($obj);
		if (isset($this->configuration['mapping']['xml']['classes'][$classSchema->getClassName()]['debug']) && $this->configuration['mapping']['xml']['classes'][$classSchema->getClassName()]['debug']) {
			t3lib_utility_Debug::debug($classSchema->getProperties());
		}
		foreach($classSchema->getPropertyNames() as $propName) {
			if ($classSchema->isXmlNameForProperty($propName)) {
				$propertyMeta = $classSchema->getProperty($propName);
				$value = Tx_Extbase_Reflection_ObjectAccess::getProperty($obj, $propName);
				$wrapperName = $classSchema->getXmlWrapperForProperty($propName);
				if ($wrapperName) {
					$tempTarget = $target->appendChild($target->ownerDocument->createElement($wrapperName));
				} else {
					$tempTarget = $target;
				}
				if ($propertyMeta['type'] == 'Tx_Extbase_Persistence_ObjectStorage' || is_subclass_of($propertyMeta['type'], 'Tx_Extbase_Persistence_ObjectStorage')) {
					foreach($value as $item) {
						$value = $this->transformForOutput($item, $classSchema->getClassName(), $propName, $value);
						$this->serializeProperty($classSchema, $propName, $item, $tempTarget, $obj);
					}
				} else {
					$value = $this->transformForOutput($obj, $classSchema->getClassName(), $propName, $value);
					$this->serializeProperty($classSchema, $propName, $value, $tempTarget, $obj);
				}
			}
		}
	}


	/**
	 * @param Tx_Palm_Reflection_ClassSchema $classSchema
	 * @param string $propName
	 * @param mixed $value
	 * @param DOMElement $target
	 * @param Object $source
	 */
	protected function serializeProperty(Tx_Palm_Reflection_ClassSchema $classSchema, $propName, $value, DOMElement $target) {
		// This could be an issue, but if this field is required it should be required by a validator anyways
		if (empty($value)) {
			return;
		}

		// Resolve lazy loading
		if ($value instanceof Tx_Extbase_Persistence_LoadingStrategyInterface) {
			if (method_exists($value, '_loadRealInstance')) {
				$value = $value->_loadRealInstance();
			}
		}

		$valueType = $this->getValueType($value);

		$attrName = $classSchema->getXmlAttributeNameForProperty($propName, $valueType);
		if ($attrName) {
			$target->setAttribute($attrName, $this->formatAtomicValue($value));
		}

		$elementName = $classSchema->getXmlElementNameForProperty($propName, $valueType);
		if ($elementName) {
			$child = $target->ownerDocument->createElement($elementName);
			$target->appendChild($child);
			if ($this->isObject($value)) {
				 $this->serializeObject($value, $child);
			} else {
				$text = $this->formatAtomicValue($value);
				$child->appendChild($target->ownerDocument->createTextNode($text));
			}
		}

		if (!$elementName && $classSchema->isXmlValueForProperty($propName, $valueType)) {
			$text = $this->formatAtomicValue($value);
			$target->appendChild($target->ownerDocument->createTextNode($text));
		}

		if (!$elementName && $classSchema->isXmlRawValueForProperty($propName, $valueType)) {
			$documentFragment = $target->ownerDocument->createDocumentFragment();
			if (!$documentFragment->appendXML($value)) {
				throw new InvalidArgumentException('The raw xml value "' . htmlspecialchars($value) . '" could not be appended to a DOMDocumentFragment.', 1302539885);
			}
			$target->appendChild($documentFragment);
		}

		if (!$classSchema->getIgnoreUnmappedProperties() && !$attrName && !$elementName && !$classSchema->isXmlValueForProperty($propName, $valueType)) {
			throw new RuntimeException("Don't know how to serialize value of type '$valueType' for property '$propName' of class '{$classSchema->getClassName()}'");
		}
	}

	/**
	 * @param Object $sourceObject
	 * @param string $className
	 * @param string $propertyName
	 * @param mixed $value
	 * @param string $format
	 * @return mixed
	 */
	protected function transformForOutput($sourceObject, $className, $propertyName, $value, $format = 'xml') {
		if (isset($this->configuration['transformOut']['xml']['classes'][$className]['properties'][$propertyName])) {
			$propertyTransformationObject = $this->configuration['transformOut'][$format]['classes'][$className]['properties'][$propertyName]['_typoScriptNodeValue'];
			$propertyTransformation = Tx_Extbase_Utility_TypoScript::convertPlainArrayToTypoScriptArray($this->configuration['transformOut'][$format]['classes'][$className]['properties'][$propertyName]);
			$sourceValues = Tx_Extbase_Reflection_ObjectAccess::getGettableProperties($sourceObject);
			foreach ($sourceValues as $key=>$sourceValue) {
				if ($this->isObject($sourceValue)) {
					unset($sourceValues[$key]);
				} elseif (is_array($sourceValue)) {
					$sourceValues[$key] = implode(';', $sourceValue);
				}
			}
			$this->contentObject->start($sourceValues);
			$this->contentObject->setCurrentVal($value);
			$value = $this->contentObject->cObjGetSingle($propertyTransformationObject, $propertyTransformation);
		}
		return $value;
	}

	/**
	 * @param mixed $value
	 * @return string|string
	 */
	protected function getValueType($value) {
		if (is_object($value))
			return get_class($value);
		$type = gettype($value);
		return ($type == 'double') ? 'float' : $type ;
	}


	/**
	 * @param mixed $value
	 * @return boolean
	 */
	protected function isObject($value) {
		return is_object($value) && !($value instanceof DateTime);
	}


	/**
	 * @param mixed $value
	 * @return string|DateTime|string
	 */
	protected function formatAtomicValue($value) {
		if (is_bool($value))
			return $value ? "true" : "false";

		if ($value instanceof DateTime) {
			$result = $value->format("o-m-d\TH:i:s\Z");
//			$time = $value->format("H:i:s");
//			if ($time != "00:00:00")
//				$result .= " $time";
			return $result;
		}

		return (string)$value;
	}


	/**
	 * Unserialization
	 * @param DOMDocument $doc
	 * @param string $className
	 * @return Object
	 */
	public function unserialize(DOMDocument $doc, $className) {
		$classSchema = $this->reflectionService->getClassSchema($className);
		$source = $this->mapXmlToArray($doc->documentElement, $className);
		$target = $this->objectManager->create($className);
		$validator = $this->validatorResolver->createValidator('GenericObject');
		$this->propertyMapper->mapAndValidate(array_keys($source), $source, $target, array_keys($classSchema->getProperties()), $validator);

		return $target;
	}


	/**
	 * Maps the given DOMElement to an property mapper conform array
	 * @param DOMElement $source
	 * @param unknown_type $class
	 * @return array
	 */
	protected function mapXmlToArray(DOMElement $source, $class) {
		$classSchema = $this->reflectionService->getClassSchema($class);

		$bag = array();
		$potentialProperties = array();

		foreach($source->attributes as $attribute) {
			$potentialProperties[] = $attribute;
		}
		foreach($source->childNodes as $child) {
			$potentialProperties[] = $child;
		}

		foreach($potentialProperties as $potentialProperty) {
			$propertyName	= null;
			$propertyType	= null;
			$nodeValue		= null;
			switch($potentialProperty) {
				case $potentialProperty instanceof DOMAttr:
					$propertyName = $classSchema->getPropertyNameForXmlAttribute($potentialProperty->name);
					$propertyType = $classSchema->getPropertyTypeForXmlAttribute($potentialProperty->name);
					$nodeValue = $this->parseAtomicValue($potentialProperty->value, $propertyType);
					break;
				case $potentialProperty instanceof DOMElement:
					$propertyName = $classSchema->getPropertyNameForXmlElement($potentialProperty->tagName);
					if (!$propertyName) {
						// Could this be a Wrapper Element?
						$propertyName = $classSchema->getPropertyNameForXmlElementWrapper($potentialProperty->tagName);
						$nodeValue = $this->mapXmlToArray($potentialProperty, $class);
						// Skip the type parsing. Types are already parsed.
						if ($propertyName !== NULL) {
							$bag[$propertyName] = $nodeValue[$propertyName];
						}
					} else {
						$propertyType = $classSchema->getPropertyTypeForXmlElement($potentialProperty->tagName);
						if ($this->isAtomicType($propertyType)) {
							$nodeValue = $this->parseAtomicValue($potentialProperty->textContent, $propertyType);
						} elseif ($propertyType) {
							$nodeValue = $this->mapXmlToArray($potentialProperty, $propertyType);
						}
					}
					break;
				case $potentialProperty instanceof DOMText:
					// TODO: This handling doesn't machtch the handling of serializer
					$potentialPropertyText = trim($potentialProperty->wholeText);
					$xmlValueTypes =$classSchema->getXmlValueTypes();
					if (!empty($potentialPropertyText) && empty($xmlValueTypes)) {
						// Potentially someone wants to give an existing id
						return $potentialPropertyText;
					}
					break;
				default:
					continue 2;
					break;
			}
			if ($propertyName !== NULL && $propertyType) {
				$propertyMetaData = $classSchema->getProperty($propertyName);
				if ($propertyType != $propertyMetaData['type']) {
					// XML Type does not equal Model Type
					if ($propertyType != $propertyMetaData['elementType']) {
						// Probably we're dealing with subclassed objects
						$bag[$propertyName] = $nodeValue;
					} elseif ($propertyType == $propertyMetaData['elementType'] && in_array($propertyMetaData['type'], array('array', 'ArrayObject', 'Tx_Extbase_Persistence_ObjectStorage'))) {
						// Oh, nice. We're dealing with a persistence storage
						$bag[$propertyName][] = $nodeValue;
					} else {
						// TODO: Check what to do here. Potentially nothing
					}
				} else {
					$bag[$propertyName] = $nodeValue;
				}
			} else {
				continue;
			}
		}
		return $bag;
	}


	/**
	 * @param mixed $type
	 * @return boolean
	 */
	protected function isAtomicType($type) {
		return $type == "string"
			|| $type == "integer"
			|| $type == "boolean"
			|| $type == "float"
			|| $type == "double"
			|| $type == "DateTime";
	}


	/**
	 * @param mixed $value
	 * @param string $type
	 * @return int|bool|double|DateTime
	 */
	protected function parseAtomicValue($value, $type) {
		if (!$type) {
			return;
		}
		switch ($type) {
			case 'integer':
				return (int) $value;
			break;
			case 'float':
			case 'double':
				return (float) $value;
			break;
			case 'boolean':
				return (boolean) strtolower($value) == "true" || intval($value) > 0;
			break;
			case 'string':
				return (string) $value;
			break;
			case $type === 'DateTime' || in_array('DateTime', class_parents($type)):
				$dateParts = explode('-', $value);
				if (strlen($dateParts[0]) < 4) {
					$dateParts = array_reverse($dateParts);
					$value = implode('-', $dateParts);
				}
				return $value;
			break;
		}
	}


}

?>
