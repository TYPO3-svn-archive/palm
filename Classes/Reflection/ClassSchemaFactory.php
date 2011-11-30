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
	 * @var array
	 */
	protected $configuration;

	/**
	 * @var Tx_Extbase_Configuration_ConfigurationManager
	 */
	protected $configurationManager;

	/**
	 * @var Tx_Extbase_Object_ObjectManager
	 */
	protected $objectManager;

	/**
	 * @var Tx_Extbase_Service_TypeHandlingService
	 */
	protected $typeHandlingService;

	/**
	 * Injector method for a configuration manager
	 *
	 * @param Tx_Palm_Configuration_ConfigurationManager $configurationManager
	 */
	public function injectConfigurationManager(Tx_Palm_Configuration_ConfigurationManager $configurationManager) {
		$this->configurationManager = $configurationManager;
		$config = $this->configurationManager->getConfiguration(Tx_Palm_Configuration_ConfigurationManager::CONFIGURATION_TYPE_FRAMEWORK);
		$this->configuration = $config['mapping']['xml'];
	}

	/**
	 * Injector method for a object manager
	 *
	 * @param Tx_Extbase_Object_ObjectManager
	 */
	public function injectObjectManager(Tx_Extbase_Object_ObjectManager $objectManager) {
		$this->objectManager = $objectManager;
	}

	/**
	 * Injector method for a type handling service
	 *
	 * @param Tx_Extbase_Service_TypeHandlingService $typeHandlingService
	 */
	// TODO renenable when TYPO3 4.5 is not a requirement anymore
	// public function injectTypeHandlingService(Tx_Extbase_Service_TypeHandlingService $typeHandlingService) {
	// 	$this->typeHandlingService = $typeHandlingService;
	// }


	/**
	 * Initializes the object
	 */
	public function initializeObject() {
		if (class_exists('Tx_Extbase_Service_TypeHandlingService')) {
			$this->typeHandlingService = $this->objectManager->get('Tx_Extbase_Service_TypeHandlingService');
		} else {
			$this->typeHandlingService = new Tx_Extbase_Utility_TypeHandling();
		}
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

		/** @var $classSchema Tx_Palm_Reflection_ClassSchema */
		$classSchema = $this->objectManager->create('Tx_Palm_Reflection_ClassSchema', $className);
		$reflectionClass = new Tx_Extbase_Reflection_ClassReflection($className);

		$mappingInformation = $this->buildMappingInformation($reflectionClass);
		if (isset($this->configuration['classes'][$className]) && is_array($this->configuration['classes'][$className])) {
			$mappingInformation = t3lib_div::array_merge_recursive_overrule($mappingInformation, $this->configuration['classes'][$className]);
		}

		$classParents = class_parents($className);
		$overriddenProperties = array();
		foreach ($classParents as $classParent) {
			if (isset($this->configuration['classes'][$classParent]['properties']) && is_array($this->configuration['classes'][$classParent]['properties'])) {
				foreach ($this->configuration['classes'][$classParent]['properties'] as $propertyName=>$propertyConfiguration) {
					if (!isset($overriddenProperties[$propertyName]) && isset($propertyConfiguration['removeMappingFor'])) {
						$overriddenProperties[$propertyName] = true;
						$mappingInformation['properties'][$propertyName]['removeMappingFor'] = $propertyConfiguration['removeMappingFor'];
						break;
					}
				}
			}
		}

		if (isset($mappingInformation['rootName'])) {
			$classSchema->setXmlRoot($mappingInformation['rootName'], (isset($mappingInformation['rootDescription'])) ? isset($mappingInformation['rootDescription']) : '');
		}

		// TODO See if we need this
		//if (isset($mappingInformation['ignoreUnmappedProperties'])) {
		//	$classSchema->setIgnoreUnmappedProperties($mappingInformation['ignoreUnmappedProperties']);
		//} elseif (isset($this->configuration['ignoreUnmappedProperties'])) {
		//	$classSchema->setIgnoreUnmappedProperties($this->configuration['ignoreUnmappedProperties']);
		//}

		foreach ($reflectionClass->getProperties() as $property) {
			/** @var $property Tx_Extbase_Reflection_PropertyReflection */
			$propertyName = $property->getName();
			if (isset($mappingInformation['properties'][$propertyName])) {
				$propertyConfiguration = $mappingInformation['properties'][$propertyName];
				if (isset($propertyConfiguration['wrapperName'])) {
					$classSchema->addXmlElementWrapper($propertyConfiguration['wrapperName'], $propertyName);
				}
				if (isset($propertyConfiguration['removeMappingFor'])) {
					$removeMappingFor = t3lib_div::trimExplode(',', $propertyConfiguration['removeMappingFor']);
					foreach ($removeMappingFor as $valueType) {
						$valueType = $this->typeHandlingService->normalizeType($valueType);
						if (isset($propertyConfiguration[$valueType])) {
							unset($propertyConfiguration[$valueType]);
						}
					}
				}
				foreach ($propertyConfiguration as $valueType=>$mappingConfiguration) {
					if (is_array($mappingConfiguration)) {
						$valueType = $this->typeHandlingService->normalizeType($valueType);
						switch ($mappingConfiguration) {
							case array_key_exists('elementName', $mappingConfiguration):
								$classSchema->addXmlElement($mappingConfiguration['elementName'], $valueType, $propertyName, $mappingConfiguration['description']);
								break;
							case array_key_exists('attributeName', $mappingConfiguration):
								$classSchema->addXmlAttribute($mappingConfiguration['attributeName'], $valueType, $propertyName, $mappingConfiguration['description']);
								break;
							case array_key_exists('isValue', $mappingConfiguration):
								$classSchema->addXmlValue($valueType, $propertyName, $mappingConfiguration['description']);
								break;
							case array_key_exists('isRawValue', $mappingConfiguration):
								$classSchema->addXmlRawValue($valueType, $propertyName, $mappingConfiguration['description']);
								break;
						}
					}
				}
			}
		}

		return $classSchema;
	}

	/**
	 * @throws Tx_Palm_Reflection_Exception_DuplicateXmlTypeBinding|Tx_Palm_Reflection_Exception_InvalidXmlNodeType
	 * @param  $className
	 * @param  $format
	 * @return array
	 */
	protected function buildMappingInformation(Tx_Extbase_Reflection_ClassReflection $reflectionClass, $format = 'xml') {
		$map = array();
		if($reflectionClass->isTaggedWith($format)) {
			$xmlTagValues = $reflectionClass->getTagValues($format);
			$description = $this->getClassDescription($reflectionClass);
			foreach($xmlTagValues as $xmlTagValue) {
				list($nodeType, $nodeName) = preg_split('/\s*\(\s*|\s*\)\s*|\s*\,\s*/i', $xmlTagValue, 4, PREG_SPLIT_NO_EMPTY);
				if(!isset($nodeType) || !in_array($nodeType, array('Root'))) {
					throw new Tx_Palm_Reflection_Exception_InvalidXmlNodeType('Invalid ' . $format . ' node type "' . $nodeType . '" at ' . $reflectionClass->name .  ' . Must be of Root.', 1289413094);
				}
				$map['rootName'] = $nodeName;
				$map['rootDescription'] = $description;
				break;
			}
		}
		foreach ($reflectionClass->getProperties() as $property) {
			/** @var $property Tx_Extbase_Reflection_PropertyReflection */
			$propertyName = $property->getName();
			$propertyConfiguration = array();
			if($property->isTaggedWith($format)) {
				$tagValues	= $property->getTagValues($format);
				$description	= $this->getPropertyDescription($property);
				foreach($tagValues as $tagValue) {
					list($nodeType, $valueType, $nodeName) = preg_split('/\s*\(\s*|\s*\)\s*|\s*\,\s*/i', $tagValue, 4, PREG_SPLIT_NO_EMPTY);
					$valueType = $this->typeHandlingService->normalizeType($valueType);
					// Attention! Wrapper has no valueType and Value no Node Name
					if(!isset($nodeType) || !in_array($nodeType, array('Wrapper','Element', 'Attribute', 'Value'))) {
						throw new Tx_Palm_Reflection_Exception_InvalidXmlNodeType('Invalid xml node type "' . $nodeType . '" at ' . $reflectionClass->name . '::' . $propertyName .  ' . Must be one of Wrapper/Element/Attribute/Value.', 1289409062);
					}
					if(isset($propertyConfiguration[$valueType])) {
						throw new Tx_Palm_Reflection_Exception_DuplicateXmlTypeBinding('The value type "' . $valueType . '" is already bound to ' . $reflectionClass->name . '::' . $propertyName , 1289559710);
					}
					if(empty($nodeName)) {
						$nodeName = $propertyName;
					}
					switch($nodeType) {
						case 'Wrapper':
							if(!empty($valueType)) {
								$nodeName = $valueType;
							}
							$propertyConfiguration['wrapperName'] = $nodeName;
							break;
						case 'Element':
							$propertyConfiguration[$valueType] = Array(
								'elementName'	=> $nodeName,
								'description'	=> $description
							);
							break;
						case 'Attribute':
							$propertyConfiguration[$valueType] = Array(
								'attributeName'	=> $nodeName,
								'description'	=> $description
							);
							break;
						case 'Value':
							$propertyConfiguration[$valueType] = Array(
								'isValue'		=> true,
								'description'	=> $description
							);
							break;
						case 'RawValue':
							$propertyConfiguration[$valueType] = Array(
								'isRawValue'	=> true,
								'description'	=> $description
							);
							break;
					}
				}
			}
			if (!empty($propertyConfiguration)) {
				$map['properties'][$propertyName] = $propertyConfiguration;
			}
		}
		return $map;
	}

	/**
	 * @param ReflectionClass $reflectionClass
	 * @return string
	 */
	protected function getClassDescription(ReflectionClass $reflectionClass) {
		// Get class description without tags
		$lines = explode(chr(10), $reflectionClass->getDocComment());
		$description = Array();
		foreach ($lines as $line) {
			$line = preg_replace('/\s*\\/?[\\\*\/]*\s*(.*)$/', '$1', $line);
			if (strlen($line) > 0 && substr($line,0,1) !== '@') {
				$description[] = $line;
			}
		}
		return implode(' ', $description);
	}

	/**
	 * @param ReflectionProperty $reflectionProperty
	 * @return string
	 */
	protected function getPropertyDescription(ReflectionProperty $reflectionProperty) {
		// Get property descriptions without tags
		$lines = explode(chr(10), $reflectionProperty->getDocComment());
		$description = Array();
		foreach ($lines as $line) {
			$line = preg_replace('/\s*\\/?[\\\*\/]*\s*(.*)$/', '$1', $line);
			if (strlen($line) > 0 && substr($line,0,1) !== '@') {
				$description[] = $line;
			}
		}
		return implode(' ', $description);
	}
}
