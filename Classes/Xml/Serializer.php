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
	 * @var Tx_Extbase_Property_Mapper
	 */
	protected $propertyMapper;

	/**
	 * @var Tx_Extbase_Validation_ValidatorResolver
	 */
	protected $validatorResolver;

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
	 * @param Object $obj
	 * @return DOMDocument
	 */
	public function serialize($obj) {
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
				$propertyMeta = $classSchema->getProperty($propName);
				$value = Tx_Extbase_Reflection_ObjectAccess::getProperty($obj, $propName);
				if($propertyMeta['type'] == 'Tx_Extbase_Persistence_ObjectStorage' || is_subclass_of($propertyMeta['type'], 'Tx_Extbase_Persistence_ObjectStorage')) {
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
		// This could be an issue, but if this field is required it should be required by a validator anyways
		if(empty($value)) {
			return;
		}

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

		if(!$attrName && !$elementName && !$classSchema->isXmlValueForProperty($propName, $valueType)) {
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
	public function unserialize(DOMDocument $doc, $className) {
		$source = $this->mapXmlToPropertyArray($doc->documentElement, $className);
		$target = $this->objectManager->create($className);

		$validator = $this->validatorResolver->getBaseValidatorConjunction($className);
		var_dump($validator);

		$this->propertyMapper->mapAndValidate(array_keys($source), $source, $target, array(), $validator);

		return $target;
	}


	/**
	 * @param DOMElement $source
	 * @param string $className
	 */
	protected function mapXmlToPropertyArray(DOMElement $source, $class) {
		$classSchema = $this->reflectionService->getClassSchema($class);

		$bag = array();

		foreach($source->attributes as $attribute) {
			$propName = $classSchema->getPropertyNameForXmlAttribute($attribute->name);
			if(!$propName)
				continue;
			$valueType = $classSchema->getPropertyTypeForXmlAttribute($attribute->name);
			$value = $this->parseAtomicValue($attribute->value, $valueType);
			$bag[$propName] = $value;
		}

		foreach ($source->childNodes as $child) {
			if(!($child instanceof DOMElement))
				continue;
			$propName = $classSchema->getPropertyNameForXmlElement($child->tagName);
			$propertyMeta = $classSchema->getProperty($propName);
			if(!$propName || !$propertyMeta)
				continue;
			$valueType = $classSchema->getPropertyTypeForXmlElement($child->tagName);
			$isObject = !$this->isAtomicType($valueType);
			$value = $isObject
				? $valueType
				: $this->parseAtomicValue(trim($child->textContent), $valueType);
			if($isObject)
				$bag[$propName] = $this->mapXmlToPropertyArray($child, $value);
		}

		return $bag;
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
