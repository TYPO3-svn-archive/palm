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
	 * @var Tx_Extbase_Reflection_Service
	 */
	private $reflectionService;

	/**
	 * @param Tx_Palm_Configuration_ConfigurationManager $configurationManager
	 * @return void
	 */
	public function injectConfigurationManager(Tx_Palm_Configuration_ConfigurationManager $configurationManager) {
		$this->configurationManager = $configurationManager;
		$config = $this->configurationManager->getConfiguration(Tx_Palm_Configuration_ConfigurationManager::CONFIGURATION_TYPE_FRAMEWORK);
		$this->configuration = $config['mapping']['xml'];
	}

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


		$mappingInformation = $this->buildMappingInformation($className);
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

		if (isset($mappingInformation['ignoreUnmappedProperties'])) {
			$classSchema->setIgnoreUnmappedProperties($mappingInformation['ignoreUnmappedProperties']);
		} elseif (isset($this->configuration['ignoreUnmappedProperties'])) {
			$classSchema->setIgnoreUnmappedProperties($this->configuration['ignoreUnmappedProperties']);
		}

		foreach ($this->reflectionService->getClassPropertyNames($className) as $propertyName) {
			if (isset($mappingInformation['properties'][$propertyName])) {
				$propertyConfiguration = $mappingInformation['properties'][$propertyName];
				if (isset($propertyConfiguration['wrapperName'])) {
					$classSchema->addXmlElementWrapper($propertyConfiguration['wrapperName'], $propertyName);
				}
				foreach ($propertyConfiguration as $valueType=>$mappingConfiguration) {
					if (isset($propertyConfiguration['removeMappingFor'])) {
						if (t3lib_div::inList($propertyConfiguration['removeMappingFor'], $valueType)) {
							unset($propertyConfiguration[$valueType]);
							$mappingConfiguration = array();
						}
					}
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
			} else {
				$propertyConfiguration = array();
			}
			if (!$this->reflectionService->isPropertyTaggedWith($className, $propertyName, 'transient') && $this->reflectionService->isPropertyTaggedWith($className, $propertyName, 'var')) {
				$cascadeTagValues = $this->reflectionService->getPropertyTagValues($className, $propertyName, 'cascade');
				$classSchema->addProperty(
					$propertyName,
					implode(' ', $this->reflectionService->getPropertyTagValues($className, $propertyName, 'var')),
					$this->reflectionService->isPropertyTaggedWith($className, $propertyName, 'lazy'),
					$cascadeTagValues[0],
					$propertyConfiguration);
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

	/**
	 * @throws Tx_Palm_Reflection_Exception_DuplicateXmlTypeBinding|Tx_Palm_Reflection_Exception_InvalidXmlNodeType
	 * @param  $className
	 * @param  $format
	 * @return array
	 */
	protected function buildMappingInformation($className, $format = 'xml') {
		$map = array();
		if($this->reflectionService->isClassTaggedWith($className, $format)) {
			$xmlTagValues	= $this->reflectionService->getClassTagValues($className, $format);
			$description	= $this->reflectionService->getClassDescription($className);
			foreach($xmlTagValues as $xmlTagValue) {
				list($nodeType, $nodeName) = preg_split('/\s*\(\s*|\s*\)\s*|\s*\,\s*/i', $xmlTagValue, 4, PREG_SPLIT_NO_EMPTY);
				if(!isset($nodeType) || !in_array($nodeType, array('Root'))) {
					throw new Tx_Palm_Reflection_Exception_InvalidXmlNodeType('Invalid ' . $format . ' node type "' . $nodeType . '" at ' . $className .  ' . Must be of Root.', 1289413094);
				}
				$map['rootName'] = $nodeName;
				$map['rootDescription'] = $description;
				break;
			}
		}
		foreach ($this->reflectionService->getClassPropertyNames($className) as $propertyName) {
			$propertyConfiguration = array();
			if($this->reflectionService->isPropertyTaggedWith($className, $propertyName, $format)) {
				$tagValues	= $this->reflectionService->getPropertyTagValues($className, $propertyName, $format);
				$description	= $this->reflectionService->getPropertyDescription($className, $propertyName);
				foreach($tagValues as $tagValue) {
					list($nodeType, $valueType, $nodeName) = preg_split('/\s*\(\s*|\s*\)\s*|\s*\,\s*/i', $tagValue, 4, PREG_SPLIT_NO_EMPTY);
					// Attention! Wrapper has no valueType and Value no Node Name
					if(!isset($nodeType) || !in_array($nodeType, array('Wrapper','Element', 'Attribute', 'Value'))) {
						throw new Tx_Palm_Reflection_Exception_InvalidXmlNodeType('Invalid xml node type "' . $nodeType . '" at ' . $className . '::' . $propertyName .  ' . Must be one of Wrapper/Element/Attribute/Value.', 1289409062);
					}
					if(isset($propertyConfiguration[$valueType])) {
						throw new Tx_Palm_Reflection_Exception_DuplicateXmlTypeBinding('The value type "' . $valueType . '" is already bound to ' . $className . '::' . $propertyName , 1289559710);
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
	//						$classSchema->addXmlElementWrapper($nodeName, $propertyName);
							break;
						case 'Element':
							$propertyConfiguration[$valueType] = Array(
								'elementName'	=> $nodeName,
								'description'	=> $description
							);
	//						$classSchema->addXmlElement($nodeName, $valueType, $propertyName, $description);
							break;
						case 'Attribute':
							$propertyConfiguration[$valueType] = Array(
								'attributeName'	=> $nodeName,
								'description'	=> $description
							);
	//						$classSchema->addXmlAttribute($nodeName, $valueType, $propertyName, $description);
							break;
						case 'Value':
							$propertyConfiguration[$valueType] = Array(
								'isValue'	=> true,
								'description'	=> $description
							);
	//						$classSchema->addXmlValue($valueType, $propertyName, $description);
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
}