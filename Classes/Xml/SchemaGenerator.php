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
 * A schema generator
 *
 * @version $Id$
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class Tx_Palm_Xml_SchemaGenerator implements t3lib_Singleton {

	/**
	 * @var Tx_Extbase_Object_ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * @var Tx_Palm_Reflection_Service
	 */
	protected $reflectionService;

	/**
	 * @var array
	 */
	protected $pendingComplexTypes;

	/**
	 * @var array
	 */
	protected $generatedComplexTypes;

	/**
	 * @var array
	 */
	protected $simpleTypes;

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
	public function injectReflectionService(Tx_Extbase_Reflection_Service $reflectionService) {
		$this->reflectionService = $reflectionService;
	}


	/**
	 * @param Object|string $rootClassName
	 * @return Tx_Palm_DOM_Document
	 */
	public function generateSchema($rootClassName) {
		if(is_object($rootClassName)) {
			$rootClassName = get_class($rootClassName);
		}

		$document = $this->objectManager->create('Tx_Palm_DOM_Document');
		$document->formatOutput = true;

		$root = $document->createElement("xs:schema");
		$root->setAttribute("xmlns:xs", "http://www.w3.org/2001/XMLSchema");

		$classSchema = $this->reflectionService->getClassSchema($rootClassName);

		$element = $document->createElement("xs:element");
		$element->setAttribute("name", $classSchema->getXmlRootName());
		$element->setAttribute("type", $this->getXsdType($classSchema->getClassName()));
		if($classSchema->getXmlRootDescription()) {
			$element->appendChild($this->createAnnotationNode($document, $classSchema->getXmlRootDescription()));
		}

		$root->appendChild($element);

		$this->pendingComplexTypes = Array($rootClassName => 1);
		$this->generatedComplexTypes = Array();
		$isRoot = true;

		while(count($this->pendingComplexTypes)) {
			reset($this->pendingComplexTypes);
			$type = key($this->pendingComplexTypes);
			$this->generatedComplexTypes[$type] = 1;
			unset($this->pendingComplexTypes[$type]);

			$root->appendChild($this->createSchemaNodeForObject($document, $type, $isRoot));
			$isRoot = false;
		}

		foreach($this->simpleTypes as $simpleType) {
			$root->appendChild($simpleType);
		}

		$document->appendChild($root);
		return $document;
	}


	/**
	 * @param Tx_Palm_DOM_Document $document
	 * @param string $obj
	 * @param boolean $isRoot
	 * @return DOMElement
	 */
	protected function createSchemaNodeForObject(Tx_Palm_DOM_Document $document, $type, $isRoot=false) {
		$classSchema = $this->reflectionService->getClassSchema($type);

		$sequence = $document->createElement("xs:sequence");
		$attributeBag = Array();
		$valueBag = Array();

		foreach($classSchema->getPropertyNames() as $propName) {
			$propertyConfig = $classSchema->getProperty($propName);

			// Process elements
			$elementNames = $classSchema->getXmlElementNamesForProperty($propName);
			if(count($elementNames) > 0) {

				// An collection an array of different type elements
				$isCollection =
					count($elementNames) > 1 && (
						$propertyConfig['type'] == 'Tx_Extbase_Persistence_ObjectStorage' ||
						is_subclass_of($propertyConfig['type'], 'Tx_Extbase_Persistence_ObjectStorage')
					);

				$propertyElement = count($elementNames) == 1
					? $this->createSchemaNodeForSingleElementProperty($document, $classSchema, $elementNames[0])
					: $this->createSchemaNodeForMultiElementProperty($document, $classSchema, $elementNames);

				if($isCollection) {
					$collectionChoice = $document->createElement("xs:choice");
					$collectionChoice->appendChild($propertyElement);
					$collectionChoice->setAttribute("minOccurs", 0);
					$collectionChoice->setAttribute("maxOccurs", "unbounded");
					$propertyElement = $collectionChoice;
				}

				$sequence->appendChild($propertyElement);
			}

			// Process attributes
			$attrNames = $classSchema->getXmlAttributeNamesForProperty($propName);
			foreach($attrNames as $attributeName) {
				$type = $classSchema->getPropertyTypeForXmlAttribute($attributeName);
				$this->mentionComplexType($type);
				$attribute = $document->createElement("xs:attribute");
				$attribute->setAttribute("name", $attributeName);
				$attribute->setAttribute("type", $this->getXsdType($type));
				if($classSchema->getPropertyDescriptionForXmlAttribute($attributeName)) {
					$attribute->appendChild($this->createAnnotationNode($document, $classSchema->getPropertyDescriptionForXmlAttribute($attributeName)));
				}
				$attributeBag[] = $attribute;
			}

			// Process values
			$values = $classSchema->getXmlValuesForProperty($propName);
			foreach($values as $valueType) {
				$valueBag[$valueType] = $propName;
			}
		}

		$complexType = $document->createElement("xs:complexType");
		$complexType->setAttribute("name", $this->getXsdType($classSchema->getClassName()));

		// If there are elements, then append the sequence
		if($sequence->hasChildNodes())
			$complexType->appendChild($sequence);

		if(!empty($valueBag)) {
			if(count($valueBag) > 1) {
				$valueType = $this->createSimpleType($document, array_keys($valueBag));
			} else {
				$valueType = current(array_keys($valueBag));
			}
			$extension = $document->createElement('xs:extension');
			$extension->setAttribute('base', $this->getXsdType($valueType));
			// If there are attributes, then append the attributes
			foreach($attributeBag as $attribute)
				$extension->appendChild($attribute);
			$simpleContent = $document->createElement('xs:simpleContent');
			$simpleContent->appendChild($extension);
			$complexType->appendChild($simpleContent);
		} else {
			// If there are attributes, then append the attributes
			foreach($attributeBag as $attribute)
				$complexType->appendChild($attribute);
		}

		return $complexType;
	}


	/**
	 * @param Tx_Palm_DOM_Document $document
	 * @param Tx_Palm_Reflection_ClassSchema $classSchema
	 * @param string $elementName
	 * @return DOMElement
	 */
	protected function createSchemaNodeForSingleElementProperty(Tx_Palm_DOM_Document $document, Tx_Palm_Reflection_ClassSchema $classSchema, $elementName) {
		$element = $document->createElement("xs:element");
		$element->setAttribute("name", $elementName);

		if($classSchema->getPropertyDescriptionForXmlElement($elementName)) {
			$element->appendChild($this->createAnnotationNode($document, $classSchema->getPropertyDescriptionForXmlElement($elementName)));
		}

		$type = $classSchema->getPropertyTypeForXmlElement($elementName);
		$typeClassSchema = $this->reflectionService->getClassSchema($type);
		if($typeClassSchema !== null && $typeClassSchema->getXmlRootName()) {
			$complexType = $document->createElement('xs:complexType');
			$sequence = $document->createElement('xs:sequence');
			$subElement = $document->createElement('xs:element');
			$subElement->setAttribute('name', $typeClassSchema->getXmlRootName());
			$this->mentionComplexType($type);
			$subElement->setAttribute("type", $this->getXsdType($type));
			$subElement->setAttribute("minOccurs", 0);
			if($classSchema->getXmlRootDescription()) {
				$subElement->appendChild($this->createAnnotationNode($document, $typeClassSchema->getXmlRootDescription()));
			}
			$sequence->appendChild($subElement);
			$complexType->appendChild($sequence);
			$element->appendChild($complexType);
			$element->setAttribute("minOccurs", 0);
		} else {
			// TODO Check here for occurence
			$this->mentionComplexType($type);
			$element->setAttribute("type", $this->getXsdType($type));
			$element->setAttribute("minOccurs", 0);
		}

		return $element;
	}


	/**
	 * @param Tx_Palm_DOM_Document $document
	 * @param Tx_Palm_Xml_ClassMeta $classSchema
	 * @param Array $names
	 * @return DOMElement
	 */
	protected function createSchemaNodeForMultiElementProperty(Tx_Palm_DOM_Document $document, Tx_Palm_Reflection_ClassSchema $classSchema, Array $names) {
		$result = $document->createElement("xs:choice");
		foreach($names as $name)
			$result->appendChild($this->createSchemaNodeForSingleElementProperty($document, $classSchema, $name));
		$result->setAttribute("minOccurs", 0);
		return $result;
	}


	/**
	 * @param Tx_Palm_DOM_Document $document
	 * @param string $description
	 * @return DOMElement
	 */
	protected function createAnnotationNode(Tx_Palm_DOM_Document $document, $description) {
		$annotation = $document->createElement("xs:annotation");

		$documentation = $document->createElement("xs:documentation");
		$documentation->setAttribute('xml:lang', 'en');
		$documentation->appendChild($document->createTextNode($description));

		$annotation->appendChild($documentation);

		return $annotation;
	}


	/**
	 * @param string $type
	 * @return string|string|string
	 */
	protected function getXsdType($type) {
		if($type == "integer" || $type == "double" || $type == "boolean" || $type == "string")
			return "xs:$type";
		if($type == "DateTime")
			return "xs:string";
		if(array_key_exists($type, $this->simpleTypes)) {
			return $type;
		}
		return str_replace("\\", "-", $type) . "-Type";
	}


	/**
	 * @param Tx_Palm_DOM_Document $document
	 * @param array $types
	 * @return string
	 */
	protected function createSimpleType(Tx_Palm_DOM_Document $document, array $types) {
		sort($types, SORT_STRING);
		$simpleTypeName = implode('Or', array_map('ucfirst', $types));
		if(!isset($this->simpleTypes[$simpleType])) {
			$types = array_map(array($this, 'getXsdType'), $types);
			$union = $document->createElement('xs:union');
			$union->setAttribute('memberTypes', implode(' ', $types));
			$simpleType = $document->createElement('xs:simpleType');
			$simpleType->setAttribute('name', $simpleTypeName);
			$simpleType->appendChild($union);
			$this->simpleTypes[$simpleTypeName] = $simpleType;
		}
		return $simpleTypeName;
	}

	/**
	 * @param string $type
	 */
	protected function mentionComplexType($type) {
		if($type == "string" || $type == "integer" || $type == "boolean" || $type == "double" || $type == "DateTime")
			return;

		if(!array_key_exists($type, $this->generatedComplexTypes))
			$this->pendingComplexTypes[$type] = 1;
	}


}

?>