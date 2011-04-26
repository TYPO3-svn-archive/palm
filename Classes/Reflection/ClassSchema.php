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
class Tx_Palm_Reflection_ClassSchema extends Tx_Extbase_Reflection_ClassSchema {

	/**
	 * @var bool
	 */
	protected $ignoreUnmappedProperties = false;

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
	 * Adds (defines) a specific property and its type.
	 *
	 * @param string $name Name of the property
	 * @param string $type Type of the property
	 * @param boolean $lazy Whether the property should be lazy-loaded when reconstituting
	 * @param string $cascade Strategy to cascade the object graph.
	 * @return void
	 */
	public function addProperty($name, $type, $lazy = FALSE, $cascade = '', $xml = array()) {
		$type = Tx_Extbase_Utility_TypeHandling::parseType($type);
		$this->properties[$name] = array(
			'type' => $type['type'],
			'elementType' => $type['elementType'],
			'lazy' => $lazy,
			'cascade' => $cascade,
			'xml' => $xml,
		);
	}


	/**
	 * Adds (defines) a specific xml element wrapper.
	 *
	 * @param string $name Name of the xml element
	 * @param string $propertyName name of the class property
	 * @return void
	 */
	public function addXmlElementWrapper($name, $propertyName) {
		if(array_key_exists($name, $this->xmlElements)) {
			$definedElement = $this->xmlElementWrappers[$name];
			throw new Tx_Palm_Reflection_Exception_DuplicateXmlElementWrapper('The xml element wrapper "' . $name .'" defined at ' . $this->className . '::' . $propertyName . ' is already defined at ' . $this->className . '::' . $definedElement['propertyName'], 1290779975);
		}
		$this->xmlElementWrappers[$name] = array(
			'propertyName'	=> $propertyName,
		);
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
		if(array_key_exists($name, $this->xmlElements)) {
			$definedElement = $this->xmlElements[$name];
			throw new Tx_Palm_Reflection_Exception_DuplicateXmlElement('The xml element "' . $name .'" defined at ' . $this->className . '::' . $propertyName . ' is already defined at ' . $this->className . '::' . $definedElement['propertyName'], 1289410457);
		}
		$type = Tx_Extbase_Utility_TypeHandling::normalizeType($type);
		$this->xmlElements[$name] = array(
			'propertyName'	=> $propertyName,
			'type'			=> $type,
			'description'	=> $description,
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
		if(array_key_exists($name, $this->xmlAttributes)) {
			$definedElement = $this->xmlAttributes[$name];
			throw new Tx_Palm_Reflection_Exception_DuplicateXmlAttribute('The xml attribute "' . $name .'" defined at ' . $this->className . '::' . $propertyName . ' is already defined at ' . $this->className . '::' . $definedElement['propertyName'], 1289410831);
		}
		$type = Tx_Extbase_Utility_TypeHandling::normalizeType($type);
		$this->xmlAttributes[$name] = array(
			'propertyName'	=> $propertyName,
			'type'			=> $type,
			'description'	=> $description,
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
		if(!empty($this->xmlValues) && isset($this->xmlValues[$valueType])) {
			$definedElement = $this->xmlValues[$valueType];
			throw new Tx_Palm_Reflection_Exception_DuplicateXmlValueType('The xml value type "' . $valueType .'" defined at ' . $this->className . '::' . $propertyName . ' is already defined at ' . $this->className . '::' . $definedElement['propertyName'], 1289410839);
		}
		$valueType = Tx_Extbase_Utility_TypeHandling::normalizeType($valueType);
		$this->xmlValues[$valueType] = array(
			'propertyName'	=> $propertyName,
			'description'	=> $description,
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
		if(!empty($this->xmlRawValues) && isset($this->xmlRawValues[$valueType])) {
			$definedElement = $this->xmlRawValues[$valueType];
			throw new Tx_Palm_Reflection_Exception_DuplicateXmlRawValueType('The xml raw value type "' . $valueType .'" defined at ' . $this->className . '::' . $propertyName . ' is already defined at ' . $this->className . '::' . $definedElement['propertyName'], 1302538814);
		}
		$valueType = Tx_Extbase_Utility_TypeHandling::normalizeType($valueType);
		$this->xmlRawValues[$valueType] = array(
			'propertyName'	=> $propertyName,
			'description'	=> $description,
		);
	}

	/**
	 * Sets $ignoreUnmappedProperties
	 *
	 * @param bool $ignoreUnmappedProperties
	 */
	public function setIgnoreUnmappedProperties($ignoreUnmappedProperties) {
		$this->ignoreUnmappedProperties = (bool) $ignoreUnmappedProperties;
	}

	/**
	 * Returns $ignoreUnmappedProperties
	 *
	 * @return bool
	 */
	public function getIgnoreUnmappedProperties() {
		return $this->ignoreUnmappedProperties;
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
		if(isset($this->properties[$propertyName]['xml'])) {
			foreach($this->properties[$propertyName]['xml'] as $binding) {
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
		if(isset($this->properties[$propertyName]['xml'])) {
			foreach($this->properties[$propertyName]['xml'] as $binding) {
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
		if(isset($this->properties[$propertyName]['xml'])) {
			foreach($this->properties[$propertyName]['xml'] as $valueType=>$binding) {
				if(isset($this->properties[$propertyName]['xml'][$valueType]['isValue'])) {
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
		if(isset($this->properties[$propertyName]['xml'])) {
			foreach($this->properties[$propertyName]['xml'] as $valueType=>$binding) {
				if(isset($this->properties[$propertyName]['xml'][$valueType]['isRawValue'])) {
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
		return (isset($this->properties[$propertyName]['xml']['wrapperName'])) ? $this->properties[$propertyName]['xml']['wrapperName'] : null;
	}

	/**
	 * Returns the xml element name for a specific property with specific value type
	 * @param string $propertyName
	 * @param string $valueType
	 * @return null|string
	 */
	public function getXmlElementNameForProperty($propertyName, $valueType) {
		if($this->isXmlElementForProperty($propertyName, $valueType)) {
			return $this->properties[$propertyName]['xml'][$valueType]['elementName'];
		} else {
			if (class_exists($valueType)) {
				// look for descendant classes
				foreach ($this->properties[$propertyName]['xml'] as $potentialValueType=>$config) {
					if (is_subclass_of($valueType, $potentialValueType) && $this->isXmlElementForProperty($propertyName, $potentialValueType)) {
						return $this->properties[$propertyName]['xml'][$potentialValueType]['elementName'];
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
			return $this->properties[$propertyName]['xml'][$valueType]['attributeName'];
		} else {
			if (class_exists($valueType)) {
				// look for descendant classes
				foreach ($this->properties[$propertyName]['xml'] as $potentialValueType=>$config) {
					if (is_subclass_of($valueType, $potentialValueType) && $this->isXmlAttributeForProperty($propertyName, $potentialValueType)) {
						$this->properties[$propertyName]['xml'][$valueType]['attributeName'];
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
		return $this->xmlElementWrappers[$xmlElementName]['propertyName'];
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
		return array_key_exists($propertyName, $this->properties) && !empty($this->properties[$propertyName]['xml']);
	}


	/**
	 * Returns whether the given property with specific value type has any xml binding
	 * @param string $propertyName
	 * @param string $valueType
	 * @return boolean
	 */
	public function isXmlNameForPropertyWithValueType($propertyName, $valueType) {
		return array_key_exists($propertyName, $this->properties) && !empty($this->properties[$propertyName]['xml']) && array_key_exists($valueType, $this->properties[$propertyName]['xml']);
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
		return isset($this->properties[$propertyName]['xml'][$valueType]['elementName']);
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
		return isset($this->properties[$propertyName]['xml'][$valueType]['attributeName']);
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
		return isset($this->properties[$propertyName]['xml'][$valueType]['isValue']);
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
		return isset($this->properties[$propertyName]['xml'][$valueType]['isRawValue']);
	}

	/**
	 * Returns whether a specific xml element wrapper has a property binding
	 * @param string $xmlElementName
	 * @return boolean
	 */
	public function isPropertyForXmlElementWrapper($xmlElementName) {
		return array_key_exists($xmlElementName, $this->xmlElementWrappers);
	}


	/**
	 * Returns whether a specific xml element has a property binding
	 * @param string $xmlElementName
	 * @return boolean
	 */
	public function isPropertyForXmlElement($xmlElementName) {
		return array_key_exists($xmlElementName, $this->xmlElements);
	}


	/**
	 * Returns whether a specific xml attribute has a property binding
	 * @param string $xmlAttributeName
	 * @return boolean
	 */
	public function isPropertyForXmlAttribute($xmlAttributeName) {
		return array_key_exists($xmlAttributeName, $this->xmlAttributes);
	}


}
?>