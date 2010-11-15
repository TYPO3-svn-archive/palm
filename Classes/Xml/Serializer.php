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
	 * Injector Method for schema generator
	 * @param Tx_Palm_Xml_SchemaGenerator $schemaGenerator
	 */
	public function injectSchemaGenerator(Tx_Palm_Xml_SchemaGenerator $schemaGenerator) {
		$this->schemaGenerator = $schemaGenerator;
	}

	/**
	 * @param Object $obj
	 * @return DOMDocument
	 */
	function serialize($obj) {
		$doc = $this->objectManager->create('DOMDocument');
		$root = $doc->createElement(Tx_Palm_Xml_ClassMetaStore::getMeta($obj)->getXmlRoot());
		$this->serializeObject($obj, $root);
		$doc->appendChild($root);
		return $doc;
	}


	/**
	 * @param Object $obj
	 * @param DOMElement $target
	 */
	private function serializeObject($obj, DOMElement $target) {
		$meta = Tx_Palm_Xml_ClassMetaStore::getMeta($obj);

		foreach($meta->getPropertyNames() as $propName) {
			$value = $meta->getPropertyValue($obj, $propName);

			if(is_array($value) || $value instanceof Traversable) {
				foreach($value as $key => $item) {
//					if(!is_int($key))
//						throw new RuntimeException("Collections with associative indexing cannot be serialized");
					$this->serializeProperty($meta, $propName, $item, $target);
				}
			} else {
				$this->serializeProperty($meta, $propName, $value, $target);
			}
		}
	}


	/**
	 * @param Tx_Palm_Xml_ClassMeta $meta
	 * @param string $propName
	 * @param unknown_type $value
	 * @param DOMElement $target
	 */
	private function serializeProperty(Tx_Palm_Xml_ClassMeta $meta, $propName, $value, DOMElement $target) {
		if($value === null)
			return;

		$valueType = $this->getValueType($value);

		$attrName = $meta->getAttributeName($propName, $valueType);
		if($attrName) {
			$target->setAttribute($attrName, $this->formatAtomicValue($value));
		}

		$elementName = $meta->getElementName($propName, $valueType);
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

		$valueElement = $meta->getValueName($propName, $valueType);
		if($valueElement && !$elementName) {
			$text = $this->formatAtomicValue($value);
			$target->appendChild($target->ownerDocument->createTextNode($text));
		}

		if(!$attrName && !$elementName && !$valueElement) {
			throw new RuntimeException("Don't know how to serialize value of type '$valueType' for property '$propName' of class '{$meta->getClassName()}'");
		}
	}


	/**
	 * @param unknown_type $value
	 * @return string|string
	 */
	private function getValueType($value) {
		if(is_object($value))
			return get_class($value);
		return gettype($value);
	}


	/**
	 * @param unknown_type $value
	 * @return boolean
	 */
	private function isObject($value) {
		return is_object($value) && !($value instanceof DateTime);
	}


	/**
	 * @param unknown_type $value
	 * @return string|unknown|string
	 */
	private function formatAtomicValue($value) {
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
	 * @param unknown_type $className
	 * @return unknown
	 */
	function unserialize(DOMDocument $doc, $className) {
		$result = $this->objectManager->create($className);
		$this->unserializeObject($result, $doc->documentElement);
		return $result;
	}


	/**
	 * @param unknown_type $obj
	 * @param DOMElement $source
	 */
	private function unserializeObject($obj, DOMElement $source) {
		$meta = Tx_Palm_Xml_ClassMetaStore::getMeta($obj);

		$bag = array();

		foreach($source->attributes as $attribute) {
			$propName = $meta->getPropertyNameForAttribute($attribute->name);
			if(!$propName)
				continue;
			$valueType = $meta->getPropertyTypeForAttribute($attribute->name);
			$value = $this->parseAtomicValue($attribute->value, $valueType);
			$this->addPropertyToBag($propName, $value, $bag);
		}

		foreach ($source->childNodes as $child) {
			if(!($child instanceof DOMElement))
				continue;
			$propName = $meta->getPropertyNameForElement($child->tagName);
			if(!$propName)
				continue;
			$valueType = $meta->getPropertyTypeForElement($child->tagName);
			$isObject = !$this->isAtomicType($valueType);
			$value = $isObject
				? new $valueType
				: $this->parseAtomicValue(trim($child->textContent), $valueType);
			$this->addPropertyToBag($propName, $value, $bag);
			if($isObject)
				$this->unserializeObject($value, $child);
		}

		foreach($bag as $propName => $data) {
			$currentValue = $meta->getPropertyValue($obj, $propName);
			if(is_array($currentValue)) {
				if(is_array($data)) {
					$meta->setPropertyValue($obj, $propName, array_merge($currentValue, $data));
				} else {
					array_push($currentValue, $data);
					$meta->setPropertyValue($obj, $propName, $currentValue);
				}
			} elseif($currentValue instanceof ArrayAccess) {
				if(is_array($data)) {
					foreach($data as $item)
						$currentValue[] = $item;
				} else {
					$currentValue[] = $data;
				}
			} else {
				$meta->setPropertyValue($obj, $propName, is_array($data) ? $data[count($data) - 1] : $data);
			}
		}
	}


	/**
	 * @param unknown_type $name
	 * @param unknown_type $value
	 * @param array $bag
	 */
	private function addPropertyToBag($name, $value, array &$bag) {
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
	 * @param unknown_type $type
	 * @return boolean
	 */
	private function isAtomicType($type) {
		return $type == "string"
			|| $type == "integer"
			|| $type == "boolean"
			|| $type == "double"
			|| $type == "DateTime";
	}


	/**
	 * @param unknown_type $value
	 * @param unknown_type $type
	 * @return int|bool|double|DateTime
	 */
	private function parseAtomicValue($value, $type) {
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
