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
 * An extension of the extbase reflection service
 *
 * @package Palm
 * @subpackage Reflection
 */
class Tx_Palm_Reflection_Service {

	/**
	 * Whether this service has been initialized.
	 *
	 * @var boolean
	 */
	protected $initialized = FALSE;

	/**
	 * Indicates whether the Reflection cache needs to be updated.
	 *
	 * This flag needs to be set as soon as new Reflection information was
	 * created.
	 *
	 * @see reflectClass()
	 * @see getMethodReflection()
	 *
	 * @var boolean
	 */
	protected $dataCacheNeedsUpdate = FALSE;

	/**
	 * @var t3lib_cache_frontend_VariableFrontend
	 */
	protected $dataCache;

	/**
	 * Local cache for Class schemata
	 * @var array
	 */
	protected $classSchemata = array();

	/**
	 * @var Tx_Extbase_Configuration_ConfigurationManagerInterface
	 */
	protected $configurationManager;

	/**
	 * @var string
	 */
	protected $cacheIdentifier = 'Palm_';

	/**
	 * @var Tx_Palm_Reflection_ClassSchemaFactory
	 */
	protected $classSchemaFactory;

	/**
	 * Constructor method for a reflection service
	 */
	public function initializeObject() {
		try {
			$this->dataCache = $GLOBALS['typo3CacheManager']->getCache('palm_reflection');
		} catch(t3lib_cache_exception_NoSuchCache $e) {
			$GLOBALS['typo3CacheFactory']->create(
				'palm_reflection',
				't3lib_cache_frontend_VariableFrontend',
				$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['palm_reflection']['backend'],
				$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['palm_reflection']['options']
			);
			$this->dataCache = $GLOBALS['typo3CacheManager']->getCache('palm_reflection');
		}
		$frameworkConfiguration = $this->configurationManager->getConfiguration(Tx_Extbase_Configuration_ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
		$this->cacheIdentifier = 'PalmReflectionData_' . $frameworkConfiguration['extensionName'];
		$this->loadFromCache();
	}

	/**
	 * Returns whether the Reflection Service is initialized.
	 *
	 * @return boolean true if the Reflection Service is initialized, otherwise false
	 */
	public function isInitialized() {
		return $this->initialized;
	}

	/**
	 * Shuts the Reflection Service down.
	 *
	 * @return void
	 */
	public function __destruct() {
		if ($this->dataCacheNeedsUpdate) {
			$this->saveToCache();
		}
		$this->initialized = FALSE;
	}

	/**
	 * Injector method for $classSchemaFactory
	 * @param Tx_Palm_Reflection_ClassSchemaFactory $classSchemaFactory
	 */
	public function injectClassSchemaFactory(Tx_Palm_Reflection_ClassSchemaFactory $classSchemaFactory) {
		$this->classSchemaFactory = $classSchemaFactory;
	}

	/**
	 * @param Tx_Extbase_Configuration_ConfigurationManagerInterface $configurationManager
	 * @return void
	 */
	public function injectConfigurationManager(Tx_Extbase_Configuration_ConfigurationManagerInterface $configurationManager) {
		$this->configurationManager = $configurationManager;
	}

	/**
	 * Returns the class schema for the given class
	 *
	 * @param mixed $classNameOrObject The class name or an object
	 * @return Tx_Palm_Reflection_ClassSchema
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function getClassSchema($classNameOrObject) {
		$className = is_object($classNameOrObject) ? get_class($classNameOrObject) : $classNameOrObject;
		if (isset($this->classSchemata[$className])) {
			return $this->classSchemata[$className];
		} else {
			return $this->buildClassSchema($className);
		}
	}

	/**
	 * Builds class schemata from classes annotated as entities or value objects
	 *
	 * @param string $className
	 * @return Tx_Palm_Reflection_ClassSchema The class schema
	 */
	protected function buildClassSchema($className) {
		$classSchema = $this->classSchemaFactory->buildClassSchema($className);
		if($classSchema === NULL) {
			return NULL;
		}
		$this->classSchemata[$className] = $classSchema;
		$this->dataCacheNeedsUpdate = TRUE;
		return $classSchema;
	}


	/**
	 * Tries to load the reflection data from this service's cache.
	 *
	 * @return void
	 */
	protected function loadFromCache() {
		if ($this->dataCache->has($this->cacheIdentifier)) {
			$data = $this->dataCache->get($this->cacheIdentifier);
			foreach ($data as $propertyName => $propertyValue) {
				$this->$propertyName = $propertyValue;
			}
		}
	}

	/**
	 * Exports the internal reflection data into the ReflectionData cache.
	 *
	 * @return void
	 */
	protected function saveToCache() {
		if (!is_object($this->dataCache)) {
			throw new Tx_Extbase_Reflection_Exception(
				'A cache must be injected before initializing the Palm Reflection Service.',
				1322488862
			);
		}
		if (!isset($GLOBALS['TYPO3_DB'])) {
			return;
		}

		$data = array();
		$propertyNames = array(
			'classSchemata'
		);
		foreach ($propertyNames as $propertyName) {
			$data[$propertyName] = $this->$propertyName;
		}
		$this->dataCache->set($this->cacheIdentifier, $data);
	}

}
