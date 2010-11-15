<?php
//namespace Lexa\XmlSerialization;

class Tx_Palm_Xml_ClassMeta {

	private $shortName;
	private $namespace;
	private $xmlRoot;

	private $reflectors	= array();
	private $props		= array();	// [propName][valueType] -> array(xmlName, isElement)
	private $attrs		= array();	// [attrName] -> array(propName, valueType)
	private $els		= array();	// [elementName] -> array(propName, valueType)
	private $values		= array();	// array(propName, valueType)

	/** @param mixed $class An instance or a class name */
	public function __construct($class) {
		$r = new ReflectionClass($class);
		if(method_exists($r, 'getShortName')) {
			$this->shortName = $r->getShortName();
			$this->namespace = $r->getNamespaceName();
		} else {
			$this->shortName = $r->getName();
		}
		$this->xmlRoot = $this->receiveXmlRoot($r->getDocComment());

		$current = $r;
		while($current) {
			foreach($current->getProperties() as $p) {
				if($p->getDeclaringClass()->name != $current->name)
					continue;
				$this->processProperty($p);
			}
			$current = $current->getParentClass();
		}
	}

	private function receiveXmlRoot($docComment) {
		foreach(Tx_Palm_Xml_Annotation::parse($docComment) as $a) {
			if($a->getName() == "root" && $a->getParamCount() > 0)
				return $a->getParam(0);
		}
	}

	private function processProperty(ReflectionProperty $p) {
		$registered = false;
		foreach(Tx_Palm_Xml_Annotation::parse($p->getDocComment()) as $a) {

			if($a->getName() != "element" && $a->getName() != "attribute" && $a->getName() != "value")
				continue;

			if($p->isStatic())
				$this->fail("Static property '{$p->name}' cannot be serialized");

			if(!$registered) {
				if(method_exists($p, 'setAccessible')) {
					$p->setAccessible(true);
				}
				$this->reflectors[$p->name] = $p;
				$registered = true;
			}

			$type = "";
			if($a->getParamCount() > 0)
				$type = $a->getParam(0);
			$type = $this->resolveType($type);

			$xmlName = $p->name;
			if($a->getParamCount() > 1)
				$xmlName = $a->getParam(1);

			$description = $a->getDescription();

			$isElement	= $a->getName() == "element";
			$isValue	= $a->getName() == "value";

			if($this->hasXmlNameForProperty($p->name, $type))
				$this->fail("Duplicate xml name for property name '{$p->name}' and value type '$type'");

			if(!array_key_exists($p->name, $this->props))
				$this->props[$p->name] = array();

			$this->props[$p->name][$type] = array($xmlName, $isElement,$isValue);

			switch($a->getName()) {
				case 'element':
					if($this->hasPropertyForElement($xmlName))
						$this->fail("Duplicate element '$xmlName'");
					$this->els[$xmlName] = array($p->name, $type, $description);
					break;
				case 'attribute':
					if($this->hasPropertyForAttribute($xmlName))
						$this->fail("Duplicate attribute '$xmlName'");
					$this->attrs[$xmlName] = array($p->name, $type, $description);
					break;
				case 'value':
					if($this->hasPropertyForValue($xmlName))
						$this->fail("Duplicate element '$xmlName'");
					$this->values = array($p->name, $type, $description);
					break;
			}
		}
	}


	private function resolveType($type) {
		if(!$type || $type == "string")
			return "string";
		if($type == "int" || $type == "integer")
			return "integer";
		if($type == "bool" || $type == "boolean")
			return "boolean";
		if($type == "float" || $type == "double")
			return "double";
		if($type == "date" || $type == "datetime")
			return "DateTime";

		if(strpos($type, "\\") === false)
			$type = $this->getNamespace() . "\\" . $type;
		return ltrim($type, "\\");
	}

	function getClassName() {
		if($this->namespace) {
			return $this->namespace . "\\" . $this->shortName;
		}
		return $this->shortName;
	}

	function getNamespace() {
		return $this->namespace;
	}

	function getXmlRoot() {
		if(!$this->xmlRoot)
			return $this->shortName;
		return $this->xmlRoot;
	}

	function getPropertyNames() {
		return array_keys($this->props);
	}

	function getAttributeNames() {
		return array_keys($this->attrs);
	}

	function getAttributeNamesForProperty($propName) {
		$result = array();
		foreach($this->attrs as $attrName => $data) {
			if($data[0] == $propName)
				$result[] = $attrName;
		}
		return $result;
	}

	function getElementNames() {
		return array_keys($this->els);
	}

	function getElementNamesForProperty($propName) {
		$result = array();
		foreach($this->els as $elementName => $data) {
			if($data[0] == $propName)
				$result[] = $elementName;
		}
		return $result;
	}

	function getPropertyValue($obj, $propName) {
		$getterName = 'get'.ucfirst($propName);
		if(!method_exists($obj, $getterName)) {
			$className = get_class($obj);
			throw new Exception("No getter for property '{$propName}' in '{$className}' defined");
		}
		return $obj->$getterName();
	}

	function setPropertyValue($obj, $propName, $value) {
		$this->reflectors[$propName]->setValue($obj, $value);
	}

	function getAttributeName($propName, $valueType) {
		if(!$this->hasAttributeForProperty($propName, $valueType))
			return null;
		return $this->props[$propName][$valueType][0];
	}

	function getElementName($propName, $valueType) {
		if(!$this->hasElementForProperty($propName, $valueType))
			return null;
		return $this->props[$propName][$valueType][0];
	}

	function getValueName($propName, $valueType) {
		if(!$this->hasValueForProperty($propName, $valueType))
			return null;
		return $this->props[$propName][$valueType][2];
	}

	function getPropertyNameForAttribute($attrName) {
		if(!$this->hasPropertyForAttribute($attrName))
			return null;
		return $this->attrs[$attrName][0];
	}

	function getPropertyNameForElement($elementName) {
		if(!$this->hasPropertyForElement($elementName))
			return null;
		return $this->els[$elementName][0];
	}

	function getPropertyNameForValue($valueName) {
		if(!$this->hasPropertyForValue($valueName))
			return null;
		return $this->values[$valueName][0];
	}

	function getPropertyTypeForAttribute($attrName) {
		if(!$this->hasPropertyForAttribute($attrName))
			return null;
		return $this->attrs[$attrName][1];
	}

	function getPropertyTypeForElement($elementName) {
		if(!$this->hasPropertyForElement($elementName))
			return null;
		return $this->els[$elementName][1];
	}

	function getPropertyTypeForValue($elementName) {
		if(!$this->hasPropertyForElement($elementName))
			return null;
		return $this->values[$elementName][1];
	}

	function getPropertyDescriptionForAttribute($attrName) {
		if(!$this->hasPropertyForAttribute($attrName))
			return null;
		return $this->attrs[$attrName][2];
	}

	function getPropertyDescriptionForElement($elementName) {
		if(!$this->hasPropertyForElement($elementName))
			return null;
		return $this->els[$elementName][2];
	}

	function getPropertyDescriptionForValue($elementName) {
		if(!$this->hasPropertyForElement($elementName))
			return null;
		return $this->values[$elementName][2];
	}

	private function hasXmlNameForProperty($propName, $valueType) {
		return array_key_exists($propName, $this->props) && array_key_exists($valueType, $this->props[$propName]);
	}

	private function hasAttributeForProperty($propName, $valueType) {
		if(!$this->hasXmlNameForProperty($propName, $valueType))
			return false;
		return !$this->props[$propName][$valueType][1] && !$this->props[$propName][$valueType][2];
	}

	private function hasElementForProperty($propName, $valueType) {
		if(!$this->hasXmlNameForProperty($propName, $valueType))
			return false;
		return $this->props[$propName][$valueType][1];
	}

	private function hasValueForProperty($propName, $valueType) {
		if(!$this->hasXmlNameForProperty($propName, $valueType))
			return false;
		return $this->props[$propName][$valueType][2];
	}

	private function hasPropertyForAttribute($attrName) {
		return array_key_exists($attrName, $this->attrs);
	}

	private function hasPropertyForElement($elementName) {
		return array_key_exists($elementName, $this->els);
	}

	private function hasPropertyForValue($valueName) {
		return array_key_exists($valueName, $this->values);
	}

	private function fail($message) {
		throw new RuntimeException("Xml metadata error for {$this->getClassName()}: $message");
	}

}

?>
