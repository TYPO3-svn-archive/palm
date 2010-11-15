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
	 * Value of the Xml Root Name
	 *
	 * @var array
	 */
	protected $xmlRoot = Array();

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
	 * Adds (defines) a specific xml element and its type.
	 *
	 * @param string $name Name of the xml element
	 * @param string $type Type of the xml element
	 * @param string $propertyName name of the class property
	 * @param string $description Description of the class property
	 * @return void
	 */
	public function addXmlElement($name, $type, $propertyName, $description='') {
		if(array_key_exists($name, $this->xmlElements)) {
			$definedElement = $this->xmlElements[$name];
			throw new Tx_Palm_Reflection_Exception_DuplicateXmlElement('The xml element "' . $name .'" defined at ' . $this->className . '::' . $propertyName . ' is already defined at ' . $this->className . '::' . $definedElement['propertyName'], 1289410457);
		}
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
	public function addXmlAttribute($name, $type, $propertyName, $description='') {
		if(array_key_exists($name, $this->xmlAttributes)) {
			$definedElement = $this->xmlAttributes[$name];
			throw new Tx_Palm_Reflection_Exception_DuplicateXmlAttribute('The xml attribute "' . $name .'" defined at ' . $this->className . '::' . $propertyName . ' is already defined at ' . $this->className . '::' . $definedElement['propertyName'], 1289410831);
		}
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
	public function addXmlValue($valueType, $propertyName, $description='') {
		if(!empty($this->xmlValues) && isset($this->xmlValues[$valueType])) {
			$definedElement = $this->xmlValues[$valueType];
			throw new Tx_Palm_Reflection_Exception_DuplicateXmlValueType('The xml value type "' . $valueType .'" defined at ' . $this->className . '::' . $propertyName . ' is already defined at ' . $this->className . '::' . $definedElement['propertyName'], 1289410839);
		}
		$this->xmlValues[$valueType] = array(
			'propertyName'	=> $propertyName,
			'description'	=> $description,
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
	 * Returns xmlValue
	 *
	 * @return array
	 */
	public function getXmlValue() {
		//
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


	public function getXmlElementNameForProperty($propertyName, $valueType) {
		if(!$this->hasXmlElementForProperty($propertyName, $valueType))
			return null;
		return $this->properties[$propertyName]['xml'][$valueType]['elementName'];
	}


	public function getXmlAttributeNameForProperty($propertyName, $valueType) {
		if(!$this->hasXmlAttributeForProperty($propertyName, $valueType))
			return null;
		return $this->properties[$propertyName]['xml'][$valueType]['attributeName'];
	}


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


	public function getPropertyNameForXmlElement($xmlElementName) {
		if(!$this->hasPropertyForXmlElement($xmlElementName))
			return null;
		return $this->xmlElements[$xmlElementName]['propertyName'];
	}


	public function getPropertyNameForXmlAttribute($xmlAttributeName) {
		if(!$this->hasPropertyForXmlAttribute($xmlAttributeName))
			return null;
		return $this->xmlAttributes[$xmlAttributeName]['propertyName'];
	}


	public function getPropertyNameForXmlValueType($valueType) {
		if(!$this->hasPropertyForXmlValue($valueName))
			return null;
		return $this->xmlValues[$valueType]['propertyName'];
	}


	public function getPropertyTypeForXmlElement($xmlElementName) {
		if(!$this->hasPropertyForXmlElement($xmlElementName))
			return null;
		return $this->xmlElements[$xmlElementName]['type'];
	}


	public function getPropertyTypeForXmlAttribute($xmlAttributeName) {
		if(!$this->hasPropertyForXmlAttribute($xmlAttributeName))
			return null;
		return $this->xmlAttributes[$xmlAttributeName]['type'];
	}


	public function getPropertyTypeForXmlValue($xmlElementName) {
		if(!$this->hasPropertyForXmlElement($xmlElementName))
			return null;
		return $this->xmlValues[$xmlElementName]['type'];
	}


	public function getPropertyDescriptionForXmlElement($xmlElementName) {
		if(!$this->hasPropertyForXmlElement($xmlElementName))
			return null;
		return $this->xmlElements[$xmlElementName]['description'];
	}


	public function getPropertyDescriptionForXmlAttribute($xmlAttributeName) {
		if(!$this->hasPropertyForXmlAttribute($xmlAttributeName))
			return null;
		return $this->xmlAttributes[$xmlAttributeName]['description'];
	}


	public function getPropertyDescriptionForXmlValue($xmlElementName) {
		if(!$this->hasPropertyForElement($xmlElementName))
			return null;
		return $this->xmlValues[$xmlElementName]['description'];
	}


	private function hasXmlNameForProperty($propertyName, $valueType) {
		return array_key_exists($propertyName, $this->properties) && array_key_exists($valueType, $this->properties[$propertyName]['xml']);
	}


	private function hasElementForProperty($propertyName, $valueType) {
		if(!$this->hasXmlNameForProperty($propertyName, $valueType))
			return false;
		return $this->properties[$propertyName][$valueType][1];
	}


	private function hasXmlAttributeForProperty($propertyName, $valueType) {
		if(!$this->hasXmlNameForProperty($propertyName, $valueType))
			return false;
		return !$this->properties[$propertyName][$valueType][1] && !$this->properties[$propertyName][$valueType][2];
	}


	private function hasXmlValueForProperty($propertyName, $valueType) {
		if(!$this->hasXmlNameForProperty($propertyName, $valueType))
			return false;
		return $this->properties[$propertyName][$valueType][2];
	}


	/**
	 * @param unknown_type $xmlElementName
	 * @return boolean
	 */
	private function hasPropertyForXmlElement($xmlElementName) {
		return array_key_exists($xmlElementName, $this->xmlElements);
	}


	/**
	 * @param unknown_type $xmlAttributeName
	 * @return boolean
	 */
	private function hasPropertyForXmlAttribute($xmlAttributeName) {
		return array_key_exists($xmlAttributeName, $this->xmlAttributes);
	}


}
?>