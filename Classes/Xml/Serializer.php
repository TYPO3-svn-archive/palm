<?php
//namespace Lexa\XmlSerialization;

class Tx_Palm_Xml_Serializer implements t3lib_Singleton {

	/**
	 * @var Tx_Extbase_Object_ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * @var Tx_Palm_Reflection_Service
	 */
	protected $reflectionService;

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
	 * @param Object $obj
	 * @return DOMDocument
	 */
	function serialize($obj) {
		$classSchema = $this->reflectionService->getClassSchema($obj);
		$doc = $this->objectManager->create('Tx_Palm_DOM_Document');
		$root = $doc->createElement($classSchema->getXmlRootName());
		$this->serializeObject($obj, $root);
		$doc->appendChild($root);
		return $doc;
	}


	/**
	 * @param Object $obj
	 * @param DOMElement $target
	 */
	protected function serializeObject($obj, DOMElement $target) {
		$classSchema = $this->reflectionService->getClassSchema($obj);
		foreach($classSchema->getPropertyNames() as $propName) {
			if($classSchema->isXmlNameForProperty($propName)) {
				$propertyConfig = $classSchema->getProperty($propName);
				$value = Tx_Extbase_Reflection_ObjectAccess::getProperty($obj, $propName);
				if($propertyConfig['type'] == 'Tx_Extbase_Persistence_ObjectStorage' || is_subclass_of($propertyConfig['type'], 'Tx_Extbase_Persistence_ObjectStorage')) {
					foreach($value as $key => $item) {
						$this->serializeProperty($classSchema, $propName, $item, $target);
					}
				} else {
					$this->serializeProperty($classSchema, $propName, $value, $target);
				}
			}
		}
	}


	/**
	 * @param Tx_Palm_Reflection_ClassSchema $classSchema
	 * @param string $propName
	 * @param unknown $value
	 * @param DOMElement $target
	 */
	protected function serializeProperty(Tx_Palm_Reflection_ClassSchema $classSchema, $propName, $value, DOMElement $target) {
		if($value === null)
			return;

		$valueType = $this->getValueType($value);

		$attrName = $classSchema->getXmlAttributeNameForProperty($propName, $valueType);
		if($attrName) {
			$target->setAttribute($attrName, $this->formatAtomicValue($value));
		}

		$elementName = $classSchema->getXmlElementNameForProperty($propName, $valueType);
		if($elementName) {
			$child = $target->ownerDocument->createElement($elementName);
			if($this->isObject($value)) {
				 $this->serializeObject($value, $child);
			} else {
				$text = $this->formatAtomicValue($value);
				$child->appendChild($target->ownerDocument->createTextNode($text));
			}
			$target->appendChild($child);
		}

		if(!$elementName && $classSchema->isXmlValueForProperty($propName, $valueType)) {
			$text = $this->formatAtomicValue($value);
			$target->appendChild($target->ownerDocument->createTextNode($text));
		}

		if(!$attrName && !$elementName && !$valueElement) {
			throw new RuntimeException("Don't know how to serialize value of type '$valueType' for property '$propName' of class '{$classSchema->getClassName()}'");
		}
	}


	/**
	 * @param unknown $value
	 * @return string|string
	 */
	protected function getValueType($value) {
		if(is_object($value))
			return get_class($value);
		return gettype($value);
	}


	/**
	 * @param unknown $value
	 * @return boolean
	 */
	protected function isObject($value) {
		return is_object($value) && !($value instanceof DateTime);
	}


	/**
	 * @param unknown $value
	 * @return string|DateTime|string
	 */
	protected function formatAtomicValue($value) {
		if(is_bool($value))
			return $value ? "true" : "false";

		if($value instanceof DateTime) {
			$result = $value->format("o-m-d\TH:i:s\Z");
//			$time = $value->format("H:i:s");
//			if($time != "00:00:00")
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
	function unserialize(DOMDocument $doc, $className) {
		$result = $this->objectManager->create($className);
		$this->unserializeObject($result, $doc->documentElement);
		return $result;
	}


	/**
	 * @param Object $obj
	 * @param DOMElement $source
	 */
	protected function unserializeObject($obj, DOMElement $source) {
		$classSchema = $this->reflectionService->getClassSchema($rootClassName);

		$bag = array();

		foreach($source->attributes as $attribute) {
			$propName = $classSchema->getPropertyNameForAttribute($attribute->name);
			if(!$propName)
				continue;
			$valueType = $classSchema->getPropertyTypeForAttribute($attribute->name);
			$value = $this->parseAtomicValue($attribute->value, $valueType);
			$this->addPropertyToBag($propName, $value, $bag);
		}

		foreach ($source->childNodes as $child) {
			if(!($child instanceof DOMElement))
				continue;
			$propName = $classSchema->getPropertyNameForElement($child->tagName);
			if(!$propName)
				continue;
			$valueType = $classSchema->getPropertyTypeForElement($child->tagName);
			$isObject = !$this->isAtomicType($valueType);
			$value = $isObject
				? new $valueType
				: $this->parseAtomicValue(trim($child->textContent), $valueType);
			$this->addPropertyToBag($propName, $value, $bag);
			if($isObject)
				$this->unserializeObject($value, $child);
		}

		foreach($bag as $propName => $data) {
			$currentValue = $classSchema->getPropertyValue($obj, $propName);
			if(is_array($currentValue)) {
				if(is_array($data)) {
					$classSchema->setPropertyValue($obj, $propName, array_merge($currentValue, $data));
				} else {
					array_push($currentValue, $data);
					$classSchema->setPropertyValue($obj, $propName, $currentValue);
				}
			} elseif($currentValue instanceof ArrayAccess) {
				if(is_array($data)) {
					foreach($data as $item)
						$currentValue[] = $item;
				} else {
					$currentValue[] = $data;
				}
			} else {
				$classSchema->setPropertyValue($obj, $propName, is_array($data) ? $data[count($data) - 1] : $data);
			}
		}
	}


	/**
	 * @param string $name
	 * @param unknown $value
	 * @param array $bag
	 */
	protected function addPropertyToBag($name, $value, array &$bag) {
		if(!array_key_exists($name, $bag)) {
			$bag[$name] = $value;
		} else {
			if(is_array($bag[$name])) {
				array_push($bag[$name], $value);
			} else {
				$bag[$name] = array($bag[$name], $value);
			}
		}
	}


	/**
	 * @param unknown $type
	 * @return boolean
	 */
	protected function isAtomicType($type) {
		return $type == "string"
			|| $type == "integer"
			|| $type == "boolean"
			|| $type == "double"
			|| $type == "DateTime";
	}


	/**
	 * @param unknown $value
	 * @param string $type
	 * @return int|bool|double|DateTime
	 */
	protected function parseAtomicValue($value, $type) {
		if($type == "integer")
			return intval($value);

		if($type == "boolean")
			return strtolower($value) == "true" || intval($value) > 0;

		if($type == "double")
			return doubleval($value);

		if($type == "DateTime")
			return new DateTime($value);

		return $value;
	}


}

?>
