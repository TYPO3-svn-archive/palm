<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 Thomas Maroschik <tmaroschik@dfau.de>
*  All rights reserved
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
 * A class schema factory
 *
 * @version $Id$
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class Tx_Palm_Reflection_ClassSchemaFactory implements t3lib_Singleton {


	/**
	 * @var Tx_Extbase_Reflection_Service
	 */
	private $reflectionService;


	/**
	 * Injector Method for reflection service
	 * @param Tx_Extbase_Reflection_Service $reflectionService
	 */
	public function injectReflectionService(Tx_Extbase_Reflection_Service $reflectionService) {
		$this->reflectionService = $reflectionService;
	}


	/**
	 * Builds class schemata from classes
	 *
	 * @param string $className
	 * @return Tx_Palm_Reflection_ClassSchema The class schema
	 */
	public function buildClassSchema($className) {
		if (!class_exists($className)) {
			return NULL;
		}
		$classSchema = new Tx_Palm_Reflection_ClassSchema($className);
		if (is_subclass_of($className, 'Tx_Extbase_DomainObject_AbstractEntity')) {
			$classSchema->setModelType(Tx_Extbase_Reflection_ClassSchema::MODELTYPE_ENTITY);

			$possibleRepositoryClassName = str_replace('_Model_', '_Repository_', $className) . 'Repository';
			if (class_exists($possibleRepositoryClassName)) {
				$classSchema->setAggregateRoot(TRUE);
			}
		} elseif (is_subclass_of($className, 'Tx_Extbase_DomainObject_AbstractValueObject')) {
			$classSchema->setModelType(Tx_Extbase_Reflection_ClassSchema::MODELTYPE_VALUEOBJECT);
		} else {
			return NULL;
		}

		if($this->reflectionService->isClassTaggedWith($className, 'xml')) {
			$xmlTagValues	= $this->reflectionService->getClassTagValues($className, 'xml');
			$description	= $this->reflectionService->getClassDescription($className);
			foreach($xmlTagValues as $xmlTagValue) {
				list($nodeType, $nodeName) = preg_split('/\s*\(\s*|\s*\)\s*|\s*\,\s*/i', $xmlTagValue, 4, PREG_SPLIT_NO_EMPTY);
				if(!isset($nodeType) || !in_array($nodeType, array('Root'))) {
					throw new Tx_Palm_Reflection_Exception_InvalidXmlNodeType('Invalid xml node type "' . $nodeType . '" at ' . $className .  ' . Must be of Root.', 1289413094);
				}
				$classSchema->setXmlRoot($nodeName, $description);
				break;
			}
		}

		foreach ($this->reflectionService->getClassPropertyNames($className) as $propertyName) {
			$propertyXmlConfiguration = Array();
			if($this->reflectionService->isPropertyTaggedWith($className, $propertyName, 'xml')) {
				$xmlTagValues	= $this->reflectionService->getPropertyTagValues($className, $propertyName, 'xml');
				$description	= $this->reflectionService->getPropertyDescription($className, $propertyName);
				foreach($xmlTagValues as $xmlTagValue) {
					list($nodeType, $valueType, $nodeName) = preg_split('/\s*\(\s*|\s*\)\s*|\s*\,\s*/i', $xmlTagValue, 4, PREG_SPLIT_NO_EMPTY);
					if(!isset($nodeType) || !in_array($nodeType, array('Element', 'Attribute', 'Value'))) {
						throw new Tx_Palm_Reflection_Exception_InvalidXmlNodeType('Invalid xml node type "' . $nodeType . '" at ' . $className . '::' . $propertyName .  ' . Must be one of Element/Attribute/Value.', 1289409062);
					}
					if(isset($propertyXmlConfiguration[$valueType])) {
						throw new Tx_Palm_Reflection_Exception_DuplicateXmlTypeBinding('The value type "' . $valueType . '" is already bound to ' . $className . '::' . $propertyName , 1289559710);
					}
					if(empty($nodeName)) {
						$nodeName = $propertyName;
					}
					switch($nodeType) {
						case 'Element':
							$propertyXmlConfiguration[$valueType] = Array(
								'elementName'	=> $nodeName
							);
							$classSchema->addXmlElement($nodeName, $valueType, $propertyName, $description);
							break;
						case 'Attribute':
							$propertyXmlConfiguration[$valueType] = Array(
								'attributeName'	=> $nodeName
							);
							$classSchema->addXmlAttribute($nodeName, $valueType, $propertyName, $description);
							break;
						case 'Value':
							$propertyXmlConfiguration[$valueType] = Array(
								'isValue'	=> true
							);;
							$classSchema->addXmlValue($valueType, $propertyName, $description);
							break;
					}
				}
			}
			if (!$this->reflectionService->isPropertyTaggedWith($className, $propertyName, 'transient') && $this->reflectionService->isPropertyTaggedWith($className, $propertyName, 'var')) {
				$cascadeTagValues = $this->reflectionService->getPropertyTagValues($className, $propertyName, 'cascade');
				$xmlTagValues = $this->reflectionService->getPropertyTagValues($className, $propertyName, 'xml');
				$classSchema->addProperty(
					$propertyName,
					implode(' ', $this->reflectionService->getPropertyTagValues($className, $propertyName, 'var')),
					$this->reflectionService->isPropertyTaggedWith($className, $propertyName, 'lazy'),
					$cascadeTagValues[0],
					$propertyXmlConfiguration);
			}
			if ($this->reflectionService->isPropertyTaggedWith($className, $propertyName, 'uuid')) {
				$classSchema->setUUIDPropertyName($propertyName);
			}
			if ($this->reflectionService->isPropertyTaggedWith($className, $propertyName, 'identity')) {
				$classSchema->markAsIdentityProperty($propertyName);
			}
		}


		return $classSchema;
	}
}