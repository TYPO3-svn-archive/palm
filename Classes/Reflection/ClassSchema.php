<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 Thomas Maroschik <tmaroschik@dfau.de>
*  All rights reserved
*
*  This class is a mixup of the initial extbase class and the lexa-xml-serialization class meta
*  All credits go to the v5 team and http://code.google.com/p/lexa-xml-serialization/.
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * A class schema
 *
 * @version $Id$
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class Tx_Palm_Reflection_ClassSchema {

	/**
	 * Properties of the class which need to be persisted
	 *
	 * @var array
	 */
	protected $properties = array();

	/**
	 * Value of the Xml Root Name
	 *
	 * @var array
	 */
	protected $xmlRoot = Array();

	/**
	 * Child Element Wrappers of the Xml Element
	 *
	 * @var array
	 */
	protected $xmlElementWrappers = Array();

	/**
	 * Child Elements of the Xml Element
	 *
	 * @var array
	 */
	protected $xmlElements = Array();

	/**
	 * Attributes of the Xml Element
	 *
	 * @var array
	 */
	protected $xmlAttributes = Array();

	/**
	 * Values of the Xml Element
	 *
	 * @var array
	 */
	protected $xmlValues = Array();

	/**
	 * Raw Values of the Xml Element
	 *
	 * @var array
	 */
	protected $xmlRawValues = Array();

	/**
	 * Constructs this class schema
	 *
	 * @param string $className Name of the class this schema is referring to
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function __construct($className) {
		$this->className = $className;
	}

	/**
	 * Returns the class name this schema is referring to
	 *
	 * @return string The class name
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function getClassName() {
		return $this->className;
	}

	/**
	 * Adds (defines) a specific xml element wrapper.
	 *
	 * @param string $name Name of the xml element
	 * @param string $propertyName name of the class property
	 * @return void
	 */
	public function addXmlElementWrapper($name, $propertyName) {
		$this->checkDuplication($propertyName, NULL, $name);
		$this->xmlElements[$name] = array(
			'isWrapper'		=> true,
			'propertyName'	=> $propertyName,
		);
		if (!isset($this->properties[$propertyName])) $this->properties[$propertyName] = array();
		$this->properties[$propertyName]['wrapperName'] = $name;
	}


	/**
	 * Adds (defines) a specific xml element and its type.
	 *
	 * @param string $name Name of the xml element
	 * @param string $type Type of the xml element
	 * @param string $propertyName name of the class property
	 * @param string $description Description of the class property
	 * @return void
	 */
	public function addXmlElement($name, $type, $propertyName, $description=null) {
		$this->checkDuplication($propertyName, $type, $name);
		$this->xmlElements[$name] = array(
			'propertyName'	=> $propertyName,
			'type'			=> $type,
			'description'	=> $description,
		);
		if (!isset($this->properties[$propertyName])) $this->properties[$propertyName] = Array();
		if (!isset($this->properties[$propertyName][$type])) $this->properties[$propertyName][$type] = Array();
		$this->properties[$propertyName][$type] =  Array(
			'elementName'	=> $name,
			'description'	=> $description
		);
	}


	/**
	 * Adds (defines) a specific xml attribute and its type.
	 *
	 * @param string $name Name of the xml element
	 * @param string $type Type of the xml element
	 * @param string $propertyName name of the class property
	 * @param string $description Description of the class property
	 * @return void
	 */
	public function addXmlAttribute($name, $type, $propertyName, $description=null) {
		$this->checkDuplication($propertyName, $type, $name);
		$this->xmlAttributes[$name] = array(
			'propertyName'	=> $propertyName,
			'type'			=> $type,
			'description'	=> $description,
		);
		if (!isset($this->properties[$propertyName])) $this->properties[$propertyName] = Array();
		if (!isset($this->properties[$propertyName][$type])) $this->properties[$propertyName][$type] = Array();
		$this->properties[$propertyName][$type] =  Array(
			'attributeName'	=> $name,
			'description'	=> $description
		);
	}


	/**
	 * Adds (defines) a xml value and its type.
	 *
	 * @param string $type Type of the xml element
	 * @param string $propertyName name of the class property
	 * @param string $description Description of the class property
	 * @return void
	 */
	public function addXmlValue($valueType, $propertyName, $description=null) {
		$this->checkDuplication($propertyName, $valueType);
		$this->xmlValues[$valueType] = array(
			'propertyName'	=> $propertyName,
			'description'	=> $description,
		);
		if (!isset($this->properties[$propertyName])) $this->properties[$propertyName] = Array();
		if (!isset($this->properties[$propertyName][$valueType])) $this->properties[$propertyName][$valueType] = Array();
		$this->properties[$propertyName][$valueType] =  Array(
			'isValue'		=> true,
			'description'	=> $description
		);
	}

	/**
	 * Adds (defines) a xml raw value and its type.
	 *
	 * @param string $type Type of the xml element
	 * @param string $propertyName name of the class property
	 * @param string $description Description of the class property
	 * @return void
	 */
	public function addXmlRawValue($valueType, $propertyName, $description=null) {
		$this->checkDuplication($propertyName, $valueType);
		$this->xmlRawValues[$valueType] = array(
			'propertyName'	=> $propertyName,
			'description'	=> $description,
		);
		if (!isset($this->properties[$propertyName])) $this->properties[$propertyName] = Array();
		if (!isset($this->properties[$propertyName][$valueType])) $this->properties[$propertyName][$valueType] = Array();
		$this->properties[$propertyName][$valueType] =  Array(
			'isRawValue'		=> true,
			'description'	=> $description
		);
	}

	/**
	 * Sets xmlRootName
	 *
	 * @param string $xmlRootName
	 * @param string $xmlRootDescription
	 * @return void
	 */
	public function setXmlRoot($xmlRootName, $xmlRootDescription='') {
		$this->xmlRoot = Array(
			'name'			=> $xmlRootName,
			'description'	=> $xmlRootDescription
		);
	}

	/**
	 * Returns xmlRootName
	 *
	 * @return boolean
	 */
	public function getXmlRootName() {
		return $this->xmlRoot['name'];
	}

	/**
	 * Returns xmlRootName
	 *
	 * @return boolean
	 */
	public function getXmlRootDescription() {
		return $this->xmlRoot['description'];
	}

	/**
	 * Check if this class is xml root
	 *
	 * @return boolean
	 */
	public function isXmlRoot() {
		return !empty($this->xmlRoot);
	}

	/**
	 * If the class schema has a certain property.
	 * @param string $propertyName Name of the property
	 * @return boolean
	 */
	public function hasProperty($propertyName) {
		return array_key_exists($propertyName, $this->properties);
	}

	/**
	 * Returns the given property defined in this schema. Check with
	 * hasProperty($propertyName) before!
	 * @return array
	 */
	public function getProperty($propertyName) {
		return is_array($this->properties[$propertyName]) ? $this->properties[$propertyName] : array();
	}

	/**
	 * Returns all properties defined in this schema
	 * @return array
	 */
	public function getProperties() {
		return $this->properties;
	}

	/**
	 * Returns the property names
	 * @return array
	 */
	public function getPropertyNames() {
		return array_keys($this->properties);
	}

	/**
	 * Returns the xml element names
	 * @return array
	 */
	public function getXmlElementNames() {
		return array_keys($this->xmlElements);
	}

	/**
	 * Returns the xml attribute names
	 * @return array
	 */
	public function getXmlAttributeNames() {
		return array_keys($this->xmlAttributes);
	}

	/**
	 * Returns the xml value types
	 * @return array
	 */
	public function getXmlValueTypes() {
		return array_keys($this->xmlValues);
	}

	/**
	 * Returns the xml raw value types
	 * @return array
	 */
	public function getXmlRawValueTypes() {
		return array_keys($this->xmlRawValues);
	}

	/**
	 * Returns the xml element names for a specific property
	 * @param string $propertyName
	 * @return array
	 */
	public function getXmlElementNamesForProperty($propertyName) {
		$xmlElementNames = Array();
		if(isset($this->properties[$propertyName])) {
			foreach($this->properties[$propertyName] as $binding) {
				if(isset($binding['elementName'])) {
					$xmlElementNames[] = $binding['elementName'];
				}
			}
		}
		return $xmlElementNames;
	}


	/**
	 * Returns the xml attribute names for a specific property
	 * @param string $propertyName
	 * @return array
	 */
	public function getXmlAttributeNamesForProperty($propertyName) {
		$xmlAttributeNames = Array();
		if(isset($this->properties[$propertyName])) {
			foreach($this->properties[$propertyName] as $binding) {
				if(isset($binding['attributeName'])) {
					$xmlAttributeNames[] = $binding['attributeName'];
				}
			}
		}
		return $xmlAttributeNames;
	}


	/**
	 * Returns the xml values for a specific property
	 * @param string $propertyName
	 * @return array
	 */
	public function getXmlValuesForProperty($propertyName) {
		$xmlValues = Array();
		if(isset($this->properties[$propertyName])) {
			foreach($this->properties[$propertyName] as $valueType=>$binding) {
				if(isset($this->properties[$propertyName][$valueType]['isValue'])) {
					$xmlValues[] = $valueType;
				}
			}
		}
		return $xmlValues;
	}

	/**
	 * Returns the xml raw values for a specific property
	 * @param string $propertyName
	 * @return array
	 */
	public function getXmlRawValuesForProperty($propertyName) {
		$xmlRawValues = Array();
		if(isset($this->properties[$propertyName])) {
			foreach($this->properties[$propertyName] as $valueType=>$binding) {
				if(isset($this->properties[$propertyName][$valueType]['isRawValue'])) {
					$xmlRawValues[] = $valueType;
				}
			}
		}
		return $xmlRawValues;
	}

	/**
	 * Returns the xml wrapper for a specific property
	 * @param string $propertyName
	 * @return string
	 */
	public function getXmlWrapperForProperty($propertyName) {
		return (isset($this->properties[$propertyName]['wrapperName'])) ? $this->properties[$propertyName]['wrapperName'] : null;
	}

	/**
	 * Returns the xml element name for a specific property with specific value type
	 * @param string $propertyName
	 * @param string $valueType
	 * @return null|string
	 */
	public function getXmlElementNameForProperty($propertyName, $valueType) {
		if($this->isXmlElementForProperty($propertyName, $valueType)) {
			return $this->properties[$propertyName][$valueType]['elementName'];
		} else {
			if (class_exists($valueType)) {
				// look for descendant classes
				foreach ($this->properties[$propertyName] as $potentialValueType=>$config) {
					if (is_subclass_of($valueType, $potentialValueType) && $this->isXmlElementForProperty($propertyName, $potentialValueType)) {
						return $this->properties[$propertyName][$potentialValueType]['elementName'];
					}
				}
			}
		}
	}


	/**
	 * Returns the xml attribute name for a specific property with specific value type
	 * @param string $propertyName
	 * @param string $valueType
	 * @return null|string
	 */
	public function getXmlAttributeNameForProperty($propertyName, $valueType) {
		if($this->isXmlAttributeForProperty($propertyName, $valueType)) {
			return $this->properties[$propertyName][$valueType]['attributeName'];
		} else {
			if (class_exists($valueType)) {
				// look for descendant classes
				foreach ($this->properties[$propertyName] as $potentialValueType=>$config) {
					if (is_subclass_of($valueType, $potentialValueType) && $this->isXmlAttributeForProperty($propertyName, $potentialValueType)) {
						$this->properties[$propertyName][$valueType]['attributeName'];
					}
				}
			}
		}
	}


	/**
	 * Returns the property name for a specific xml element wrapper
	 * @param string $xmlElementName
	 * @return null|string
	 */
	public function getPropertyNameForXmlElementWrapper($xmlElementName) {
		if(!$this->isPropertyForXmlElementWrapper($xmlElementName))
			return null;
		return $this->xmlElements[$xmlElementName]['propertyName'];
	}


	/**
	 * Returns the property name for a specific xml element
	 * @param string $xmlElementName
	 * @return null|string
	 */
	public function getPropertyNameForXmlElement($xmlElementName) {
		if(!$this->isPropertyForXmlElement($xmlElementName))
			return null;
		return $this->xmlElements[$xmlElementName]['propertyName'];
	}


	/**
	 * Returns the property name for a specific xml attribute
	 * @param string $xmlAttributeName
	 * @return null|string
	 */
	public function getPropertyNameForXmlAttribute($xmlAttributeName) {
		if(!$this->isPropertyForXmlAttribute($xmlAttributeName))
			return null;
		return $this->xmlAttributes[$xmlAttributeName]['propertyName'];
	}


	/**
	 * Returns the property name for a specific value type
	 * @param string $valueType
	 * @return null|string
	 */
	public function getPropertyNameForXmlValueType($valueType) {
		if(!isset($this->xmlValues[$valueType]['propertyName']))
			return null;
		return $this->xmlValues[$valueType]['propertyName'];
	}


	/**
	 * Returns the property name for a specific value type
	 * @param string $valueType
	 * @return null|string
	 */
	public function getPropertyNameForXmlRawValueType($valueType) {
		if(!isset($this->xmlRawValues[$valueType]['propertyName']))
			return null;
		return $this->xmlRawValues[$valueType]['propertyName'];
	}

	/**
	 * Returns the property type for a specific xml element
	 * @param string $xmlElementName
	 * @return null|string
	 */
	public function getPropertyTypeForXmlElement($xmlElementName) {
		if(!$this->isPropertyForXmlElement($xmlElementName))
			return null;
		return $this->xmlElements[$xmlElementName]['type'];
	}


	/**
	 * Returns the property type for a specific xml attribute
	 * @param string $xmlAttributeName
	 * @return null|string
	 */
	public function getPropertyTypeForXmlAttribute($xmlAttributeName) {
		if(!$this->isPropertyForXmlAttribute($xmlAttributeName))
			return null;
		return $this->xmlAttributes[$xmlAttributeName]['type'];
	}


	/**
	 * Returns the property description for a specific xml element
	 * @param string $xmlElementName
	 * @return null|string
	 */
	public function getPropertyDescriptionForXmlElement($xmlElementName) {
		if(!$this->isPropertyForXmlElement($xmlElementName))
			return null;
		return $this->xmlElements[$xmlElementName]['description'];
	}


	/**
	 * Returns the property description for a specific xml attribute
	 * @param string $xmlAttributeName
	 * @return null|string
	 */
	public function getPropertyDescriptionForXmlAttribute($xmlAttributeName) {
		if(!$this->isPropertyForXmlAttribute($xmlAttributeName))
			return null;
		return $this->xmlAttributes[$xmlAttributeName]['description'];
	}


	/**
	 * Returns whether the given property has any xml binding
	 * @param string $propertyName
	 * @param string $valueType
	 * @return boolean
	 */
	public function isXmlNameForProperty($propertyName) {
		return array_key_exists($propertyName, $this->properties) && !empty($this->properties[$propertyName]);
	}


	/**
	 * Returns whether the given property with specific value type has any xml binding
	 * @param string $propertyName
	 * @param string $valueType
	 * @return boolean
	 */
	public function isXmlNameForPropertyWithValueType($propertyName, $valueType) {
		return array_key_exists($propertyName, $this->properties) && !empty($this->properties[$propertyName]) && array_key_exists($valueType, $this->properties[$propertyName]);
	}


	/**
	 * Returns whether the given property has any xml element for the specific value type
	 * @param string $propertyName
	 * @param string $valueType
	 * @return boolean
	 */
	public function isXmlElementForProperty($propertyName, $valueType) {
		if(!$this->isXmlNameForPropertyWithValueType($propertyName, $valueType))
			return false;
		return isset($this->properties[$propertyName][$valueType]['elementName']);
	}

	/**
	 * Returns whether the given property has any xml attribute for the specific value type
	 * @param string $propertyName
	 * @param string $valueType
	 * @return boolean
	 */
	public function isXmlAttributeForProperty($propertyName, $valueType) {
		if(!$this->isXmlNameForPropertyWithValueType($propertyName, $valueType))
			return false;
		return isset($this->properties[$propertyName][$valueType]['attributeName']);
	}

	/**
	 * Returns whether the given property has an xml value binding for the specific value type
	 * @param string $propertyName
	 * @param string $valueType
	 * @return boolean
	 */
	public function isXmlValueForProperty($propertyName, $valueType) {
		if(!$this->isXmlNameForPropertyWithValueType($propertyName, $valueType))
			return false;
		return isset($this->properties[$propertyName][$valueType]['isValue']);
	}

	/**
	 * Returns whether the given property has an xml raw value binding for the specific value type
	 * @param string $propertyName
	 * @param string $valueType
	 * @return boolean
	 */
	public function isXmlRawValueForProperty($propertyName, $valueType) {
		if(!$this->isXmlNameForPropertyWithValueType($propertyName, $valueType))
			return false;
		return isset($this->properties[$propertyName][$valueType]['isRawValue']);
	}

	/**
	 * Returns whether a specific xml element wrapper has a property binding
	 * @param string $xmlElementName
	 * @return boolean
	 */
	public function isPropertyForXmlElementWrapper($xmlElementName) {
		return array_key_exists($xmlElementName, $this->xmlElements) && !empty($this->xmlElements[$xmlElementName]) && array_key_exists('isWrapper', $this->xmlElements[$xmlElementName]);
	}

	/**
	 * Returns whether a specific xml element has a property binding
	 * @param string $xmlElementName
	 * @return boolean
	 */
	public function isPropertyForXmlElement($xmlElementName) {
		return array_key_exists($xmlElementName, $this->xmlElements) && !array_key_exists('isWrapper', $this->xmlElements[$xmlElementName]);
	}

	/**
	 * Returns whether a specific xml attribute has a property binding
	 * @param string $xmlAttributeName
	 * @return boolean
	 */
	public function isPropertyForXmlAttribute($xmlAttributeName) {
		return array_key_exists($xmlAttributeName, $this->xmlAttributes);
	}

	/**
	 * @throws Tx_Palm_Reflection_Exception_DuplicateXmlAttribute|Tx_Palm_Reflection_Exception_DuplicateXmlElement|Tx_Palm_Reflection_Exception_DuplicateXmlRawValueType|Tx_Palm_Reflection_Exception_DuplicateXmlValueType
	 * @param string $propertyName
	 * @param string $type
	 * @param string $name
	 * @return void
	 */
	protected function checkDuplication($propertyName, $type = NULL, $name = NULL) {
		if ($type !== NULL) {
			if (isset($this->properties[$propertyName][$type])) {
				$typeDefinition = $this->properties[$propertyName][$type];
				switch ($typeDefinition) {
					case array_key_exists('elementName', $typeDefinition);
						throw new Tx_Palm_Reflection_Exception_DuplicateXmlElement('The xml element type"' . $type .'" defined at ' . $this->className . '::' . $propertyName . ' is already defined.', 1322482617);
						break;
					case array_key_exists('attributeName', $typeDefinition);
						throw new Tx_Palm_Reflection_Exception_DuplicateXmlAttribute('The xml attribute type "' . $type .'" defined at ' . $this->className . '::' . $propertyName . ' is already defined.', 1322482618);
						break;
					case array_key_exists('isValue', $typeDefinition);
						throw new Tx_Palm_Reflection_Exception_DuplicateXmlValueType('The xml value type "' . $type .'" defined at ' . $this->className . '::' . $propertyName . ' is already defined.', 1322482618);
						break;
					case array_key_exists('isRawValue', $typeDefinition);
						throw new Tx_Palm_Reflection_Exception_DuplicateXmlRawValueType('The xml raw value type "' . $type .'" defined at ' . $this->className . '::' . $propertyName . ' is already defined.', 1322482620);
						break;
				}
			}
		}
		if ($type === NULL && $name !== NULL) {
			if($this->xmlElements[$name]) {
				$definedElement = $this->xmlElementWrappers[$name];
				throw new Tx_Palm_Reflection_Exception_DuplicateXmlElementWrapper('The xml element wrapper "' . $name .'" defined at ' . $this->className . '::' . $propertyName . ' is already defined at ' . $this->className . '::' . $definedElement['propertyName'], 1290779975);
			}
		}
		if ($type !== NULL && $name !== NULL) {
			if (isset($this->xmlElements[$name])) {
				throw new Tx_Palm_Reflection_Exception_DuplicateXmlElement('The xml element name"' . $name .'" defined at ' . $this->className . '::' . $propertyName . ' is already defined at ' . $this->className . '::' . $this->xmlElements[$name]['propertyName'] . '.', 1322482716);
			}
			if (isset($this->xmlAttributes[$name])) {
				throw new Tx_Palm_Reflection_Exception_DuplicateXmlAttribute('The xml attribute name "' . $name .'" defined at ' . $this->className . '::' . $propertyName . ' is already defined at ' . $this->className . '::' . $this->xmlAttributes[$name]['propertyName'] . '.', 1322482839);
			}
		}
	}

}