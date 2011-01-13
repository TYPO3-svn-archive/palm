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
class Tx_Palm_Reflection_Service extends Tx_Extbase_Reflection_Service {

	/**
	 * @var array
	 */
	protected $classDescriptions = array();

	/**
	 * @var array
	 */
	protected $propertyDescriptions = array();

	/**
	 * @var Tx_Palm_Reflection_ClassSchemaFactory
	 */
	protected $classSchemaFactory;

	/**
	 * Injector method for $classSchemaFactory
	 * @param Tx_Palm_Reflection_ClassSchemaFactory $classSchemaFactory
	 */
	public function injectClassSchemaFactory(Tx_Palm_Reflection_ClassSchemaFactory $classSchemaFactory) {
		$this->classSchemaFactory = $classSchemaFactory;
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
		$this->cacheNeedsUpdate = TRUE;
		return $classSchema;
	}


	/**
	 * Reflects the given class and stores the results in this service's properties.
	 *
	 * @param string $className Full qualified name of the class to reflect
	 * @return void
	 */
	protected function reflectClass($className) {
		$class = new Tx_Extbase_Reflection_ClassReflection($className);
		$this->reflectedClassNames[$className] = time();

		// Get class description without tags
		$lines = explode(chr(10), $class->getDocComment());
		$description = Array();
		foreach ($lines as $line) {
			$line = preg_replace('/\s*\\/?[\\\*\/]*\s*(.*)$/', '$1', $line);
			if (strlen($line) > 0 && substr($line,0,1) !== '@') {
				$description[] = $line;
			}
		}
		$this->classDescriptions[$className] = implode(' ', $description);
			
		foreach ($class->getInterfaces() as $interface) {
			if (!$class->isAbstract()) {
				$this->interfaceImplementations[$interface->getName()][] = $className;
			}
		}

		foreach ($class->getTagsValues() as $tag => $values) {
			if (array_search($tag, $this->ignoredTags) === FALSE) {
				$this->taggedClasses[$tag][] = $className;
				$this->classTagsValues[$className][$tag] = $values;
			}
		}

		foreach ($class->getProperties() as $property) {
			$propertyName = $property->getName();
			$this->classPropertyNames[$className][] = $propertyName;

			// Get property descriptions without tags
			$lines = explode(chr(10), $property->getDocComment());
			$description = Array();
			foreach ($lines as $line) {
				$line = preg_replace('/\s*\\/?[\\\*\/]*\s*(.*)$/', '$1', $line);
				if (strlen($line) > 0 && substr($line,0,1) !== '@') {
					$description[] = $line;
				}
			}
			$this->propertyDescriptions[$className][$propertyName] = implode(' ', $description);

			foreach ($property->getTagsValues() as $tag => $values) {
				if (array_search($tag, $this->ignoredTags) === FALSE) {
					$this->propertyTagsValues[$className][$propertyName][$tag] = $values;
				}
			}
		}

		foreach ($class->getMethods() as $method) {
			$methodName = $method->getName();
			foreach ($method->getTagsValues() as $tag => $values) {
				if (array_search($tag, $this->ignoredTags) === FALSE) {
					$this->methodTagsValues[$className][$methodName][$tag] = $values;
				}
			}

			foreach ($method->getParameters() as $parameterPosition => $parameter) {
				$this->methodParameters[$className][$methodName][$parameter->getName()] = $this->convertParameterReflectionToArray($parameter, $parameterPosition, $method);
			}
		}
		ksort($this->reflectedClassNames);

		$this->cacheNeedsUpdate = TRUE;
	}


	/**
	 * Returns all tags and their values the specified class is tagged with
	 *
	 * @param string $className Name of the class
	 * @return array An array of tags and their values or an empty array of no tags were found
	 */
	public function getClassTagsValues($className) {
		if (!isset($this->reflectedClassNames[$className])) $this->reflectClass($className);
		if (!isset($this->classTagsValues[$className])) return array();
		return (isset($this->classTagsValues[$className])) ? $this->classTagsValues[$className] : array();
	}


	/**
	 * Returns the values of the specified class tag
	 *
	 * @param string $className Name of the class
	 * @param string $tag Tag to return the values of
	 * @return array An array of values or an empty array if the tag was not found
	 */
	public function getClassTagValues($className, $tag) {
		if (!isset($this->reflectedClassNames[$className])) $this->reflectClass($className);
		if (!isset($this->classTagsValues[$className])) return array();
		return (isset($this->classTagsValues[$className][$tag])) ? $this->classTagsValues[$className][$tag] : array();
	}

	/**
	 * Returns the description of the specific class
	 *
	 * @param string $className Name of the class to return the description of
	 * @return string
	 */
	public function getClassDescription($className) {
		if (!isset($this->reflectedClassNames[$className])) $this->reflectClass($className);
		return (isset($this->classDescriptions[$className])) ? $this->classDescriptions[$className] : NULL;
	}


	/**
	 * Returns the description of the specific class property
	 *
	 * @param string $className Name of the property to return the description of
	 * @return string
	 */
	public function getPropertyDescription($className, $propertyName) {
		if (!isset($this->reflectedClassNames[$className])) $this->reflectClass($className);
		return (isset($this->propertyDescriptions[$className][$propertyName])) ? $this->propertyDescriptions[$className][$propertyName] : NULL;
	}


	/**
	 * Exports the internal reflection data into the ReflectionData cache.
	 *
	 * @return void
	 */
	protected function saveToCache() {
		if (!is_object($this->dataCache)) {
			throw new Tx_Palm_Reflection_Exception(
				'A cache must be injected before initializing the Reflection Service.',
				1289916950
			);
		}
		$data = array();
		$propertyNames = array(
			'reflectedClassNames',
			'classPropertyDocComments',
			'classPropertyNames',
			'classTagsValues',
			'interfaceImplementations',
			'methodTagsValues',
			'methodParameters',
			'propertyTagsValues',
			'taggedClasses',
			'classDocComments',
			'classSchemata'
		);
		foreach ($propertyNames as $propertyName) {
			$data[$propertyName] = $this->$propertyName;
		}
		$this->dataCache->set($this->cacheIdentifier, $data);
	}


}
?>