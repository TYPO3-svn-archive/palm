<?php

class Tx_Palm_Merger_Service implements Tx_Palm_Merger_ServiceInterface {

	/**
	 * Merger configuration
	 *
	 * @var array
	 */
	protected $configuration = Array();

	/**
	 * @var bool
	 */
	protected $pullRulesInitialized = false;

	/**
	 * Contains available pull rules
	 *
	 * @var Tx_Extbase_Persistence_ObjectStorage<Tx_Palm_Merger_RootRule>
	 */
	protected $pullRules;

	/**
	 * @var array
	 */
	protected $loadedDOMs;

	/**
	 * An instance of a object manager
	 *
	 * @var Tx_Extbase_Object_ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * @var Tx_Palm_Merger_RuleBuilder
	 */
	protected $ruleBuilder;

	/**
	 * @var Tx_Palm_Xml_Serializer
	 */
	protected $xmlSerializer;

	/**
	 * @var Tx_Extbase_Reflection_Service
	 */
	protected $reflectionService;

	/**
	 * Injector method for an object manager
	 *
	 * @param Tx_Extbase_Object_ObjectManagerInterface $objectManager
	 */
	public function injectObjectManager(Tx_Extbase_Object_ObjectManagerInterface $objectManager) {
		$this->objectManager = $objectManager;
	}


	/**
	 * Injector method for a rule builder
	 *
	 * @param Tx_Palm_Merger_RuleBuilder $ruleBuilder
	 */
	public function injectRuleBuilder(Tx_Palm_Merger_RuleBuilder $ruleBuilder) {
		$this->ruleBuilder = $ruleBuilder;
	}


	/**
	 * Injector method for a xml serializer
	 *
	 * @param Tx_Palm_Xml_Serializer $xmlSerializer
	 */
	public function injectXmlSerializer(Tx_Palm_Xml_Serializer $xmlSerializer) {
		$this->xmlSerializer = $xmlSerializer;
	}


	/**
	 * Injector method for a reflection service
	 *
	 * @param Tx_Extbase_Reflection_Service $reflectionService
	 */
	public function injectReflectionService(Tx_Extbase_Reflection_Service $reflectionService) {
		$this->reflectionService = $reflectionService;
	}

	/**
	 * Constructor method for the merger service
	 */
	public function __construct() {
		if(file_exists(PATH_site . 'typo3conf/palmconf.php')) {
			require_once(PATH_site . 'typo3conf/palmconf.php');
			if(isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['palm']) && is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['palm'])) {
				$this->configuration = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['palm'];
			}
		}
		$this->pullRules = t3lib_div::makeInstance('Tx_Extbase_Persistence_ObjectStorage');
		$this->loadedDOMs = t3lib_div::makeInstance('Tx_Extbase_Persistence_ObjectStorage');
	}


	/**
	 * Start building pull rules
	 */
	protected function initializePullRules() {
		if($this->pullRulesInitialized) return;
		$configuration = $this->getPullConfiguration();
		if(!empty($configuration)) {
			foreach($configuration as $ruleConfiguration) {
				$this->pullRules->attach($this->ruleBuilder->build($ruleConfiguration));
			}
			$this->pullRulesInitialized = true;
		}
	}

	/**
	 * Enter description here ...
	 *
	 * @param Tx_Palm_Merger_AbstractRule $rule
	 */
	protected function initializeDomForRule(Tx_Palm_Merger_RootRule $rule) {
		if ($this->pullRules->offsetGet($rule) instanceof Tx_Palm_Merger_RootRule) {
			return;
		}
		/** @var Tx_Palm_DOM_Document $dom */
		$dom = $this->objectManager->create('Tx_Palm_DOM_Document');
		$dom->load(t3lib_div::getFileAbsFileName($rule->getFileLocation()));
		$this->pullRules->offsetSet($rule, $dom);
	}

	/**
	 * Enter description here ...
	 *
	 * @return mixed
	 */
	public function getPullConfiguration() {
		return $this->configuration['pull'];
	}

	/**
	 * Enter description here ...
	 *
	 * @return mixed
	 */
	public function getPullableEntities() {
		$this->initializePullRules();
		$entities = Array();
		/** @var Tx_Palm_Merger_RootRule $rule */
		foreach ($this->pullRules as $rule) {
			if (!isset($entities[$rule->getEntityName()])) {
				$entities[$rule->getEntityName()] = 0;
			}
			$entities[$rule->getEntityName()]++;
		}
		return $entities;
	}

	/**
	 * Enter description here ...
	 * @param unknown_type $entityName
	 * @return mixed
	 */
	public function getPullRulesByEntityName($entityName) {
		$this->initializePullRules();
		$rules = Array();
		/** @var Tx_Palm_Merger_RootRule $rule */
		foreach ($this->pullRules as $rule) {
			if($rule->getEntityName() == $entityName) {
				$rules[$rule->getFileLocation()] = $rule;
			}
		}
		return $rules;
	}

	/**
	 * Enter description here ...
	 * @param string $fileLocation
	 * @return Tx_Palm_Merger_RootRule
	 */
	public function getPullRuleByFileLocation($fileLocation) {
		$this->initializePullRules();
		/** @var Tx_Palm_Merger_RootRule $rule */
		foreach ($this->pullRules as $rule) {
			if($rule->getFileLocation() == $fileLocation) {
				return $rule;
			}
		}
	}

	/**
	 * Enter description here ...
	 * @return Tx_Extbase_Persistence_ObjectStorage
	 */
	public function getPullRules() {
		$this->initializePullRules();
		return $this->pullRules;
	}

	/**
	 * Enter description here ...
	 * @param Tx_Palm_Merger_AbstractRule $rule
	 * @return DOMDocument
	 */
	public function getDOMByRule(Tx_Palm_Merger_AbstractRule $rule) {
		$this->initializeDOMForRule($rule);
		return $this->pullRules->offsetGet($rule);
	}

	/**
	 * @param Tx_Palm_Merger_RootRule $rule
	 * @return Tx_Extbase_Persistence_Repository
	 */
	public function getRepositoryByRule(Tx_Palm_Merger_RootRule $rule) {
		if (!class_exists($rule->getRepositoryName())) {
			die('PullDataController: This should not happen. The check occurs already in rule builder');
		}
		$repository = $this->objectManager->get($rule->getRepositoryName());
		return $repository;
	}

	/**
	 * @param Tx_Palm_Merger_RootRule $rule
	 * @return Tx_Extbase_Persistence_Repository
	 */
	public function getXmlRepositoryByRule(Tx_Palm_Merger_RootRule $rule) {
		if (!class_exists($rule->getRepositoryName())) {
			die('PullDataController: This should not happen. The check occurs already in rule builder');
		}
		return $this->objectManager->create('Tx_Palm_Merger_Repository', $rule);
	}

	/**
	 * Enter description here ...
	 * @param Tx_Palm_Merger_AbstractRule $rule
	 * @param Tx_Extbase_DomainObject_DomainObjectInterface $entity
	 * @return mixed
	 */
	public function getResolvedSinglePathInCollection(Tx_Palm_Merger_RootRule $rule, Tx_Extbase_DomainObject_DomainObjectInterface $entity) {
		$internalPropertyPath = $this->getPropertyPathFromRule($rule);
		$internalPropertyValue = Tx_Palm_Reflection_ObjectAccess::getPropertyPath($entity, $internalPropertyPath);
		return str_replace('{' . $internalPropertyPath . '}', $internalPropertyValue, $rule->getSinglePathInCollection());
	}

	/**
	 * @param Tx_Palm_Merger_RootRule $rule
	 * @return array
	 */
	public function getMatchOnValuesByRule(Tx_Palm_Merger_RootRule $rule) {
		$singlePath = preg_replace('|\{[A-Za-z\.\-\_]+\}|', '###TOKEN###', $rule->getSinglePathInCollection());
		$matchOnXpath = $this->createMatchOnXPath($singlePath);
		/** @var DOMXPath $xPath */
		$xPath = $this->objectManager->create('DOMXPath', $this->getDOMByRule($rule));
		$xPathQueryResult = $xPath->query($matchOnXpath);
		$matchOnValues = array();
		if ($xPathQueryResult->length) {
			foreach ($xPathQueryResult as $node) {
				$matchOnValues[] = $node->nodeValue;
			}
		}
		return $matchOnValues;
	}

	/**
	 * @param string $xPath
	 * @return string
	 */
	protected function createMatchOnXPath($xPath) {
		$parser = new Tx_Palm_XPath_Parser();
		$xPathArray = $parser->parse($xPath);
		list($cleanXPathArray, $tokenLocationXPathArray) = $this->traverseXPathArrayCleanTokenCondition($xPathArray);
		$path = array();
		if (isset($cleanXPathArray['location']) && isset($tokenLocationXPathArray['location'])) {
			$currentPathStackPosition = 0;
			foreach ($cleanXPathArray['location'] as $pathPart) {
				$path[] = $pathPart;
				if ($pathPart['axis'] == 'parent') {
					$currentPathStackPosition--;
				} else {
					$currentPathStackPosition++;
				}
			}
			$tokenPathStackPosition = count($tokenLocationXPathArray['location']) - 1;
			while ($currentPathStackPosition > $tokenPathStackPosition) {
				$lastPath = $path[$currentPathStackPosition - 2];
				$currentPathStackPosition--;
				$path[] = array(
					'axis'  => 'parent',
					'localName' => $lastPath['localName']
				);
			}
			$tokenPathParts = array_slice($tokenLocationXPathArray['location'], $currentPathStackPosition);
			foreach ($tokenPathParts as $tokenPathPart) {
				$path[] = $tokenPathPart;
			}
		}
		$prettyPrinter = new Tx_Palm_XPath_PrettyPrinter();
		return $prettyPrinter->prettyPrint(array('location' => $path));
	}

	protected function traverseXPathArrayCleanTokenCondition(array $array, array &$path = array(), &$tokenLocation = array('location')) {
		$return = array();
		foreach ($array as $key => $value) {
			if (is_array($value)) {
				if (isset($value['axis']) && isset($value['localName'])) {
					$path['location'][] = array(
						'axis' => $value['axis'],
						'localName' => $value['localName']
					);
				}
				$temp = $this->traverseXPathArrayCleanTokenCondition($value, $path, $tokenLocation);
				$return[$key] = $temp[0];
			} elseif ($key == 'literal' && $value == '###TOKEN###' && isset($array['location'])) {
				unset($array['operator']);
				unset($array['literal']);
				$tokenLocation = $path;
				$return = array('location' => $array['location']);
			} else {
				$return[$key] = $value;
			}
		}
		return array($return, $tokenLocation);
	}

	/**
	 * Enter description here ...
	 * @param Tx_Palm_Merger_AbstractRule $rule
	 * @param Tx_Extbase_DomainObject_DomainObjectInterface $entity
	 * @return boolean
	 */
	public function isRuleApplicableOnEntity(Tx_Palm_Merger_AbstractRule $rule, Tx_Extbase_DomainObject_DomainObjectInterface $entity) {
		$dom = $this->getDOMByRule($rule);
		/** @var DOMXPath $xpath */
		$xpath = $this->objectManager->create('DOMXPath', $dom);
		$externalPath = $this->getResolvedSinglePathInCollection($rule, $entity);
		return (bool) $xpath->query($externalPath)->length;
	}

	/**
	 * Enter description here ...
	 * @param Tx_Palm_Merger_AbstractRule $rule
	 * @param Tx_Extbase_DomainObject_DomainObjectInterface $entity
	 * @return boolean
	 */
	public function isEntityAlreadyPresent(Tx_Palm_Merger_AbstractRule $rule, Tx_Extbase_DomainObject_DomainObjectInterface $entity) {
		if ((bool) $entity->getUid()) {
			return TRUE;
		} else {
			$matchValue = Tx_Palm_Reflection_ObjectAccess::getPropertyPath($entity, $rule->getMatchOn());
			if ($matchValue === NULL) {
				throw new InvalidArgumentException('The given entity has a match value of NULL at the given match path "' . $rule->getMatchOn() . '"', 1303384484);
			}
			if ($matchValue instanceof Tx_Extbase_DomainObject_DomainObjectInterface) {
				throw new InvalidArgumentException('The given entity has a non scalar match value at the given match path "' . $rule->getMatchOn() . '"', 1303384930);
			}
			/** @var Tx_Extbase_Persistence_QueryFactoryInterface $queryFactory  */
			$queryFactory = $this->objectManager->get('Tx_Extbase_Persistence_QueryFactoryInterface');
			if (!strpos($rule->getMatchOn(), '.')) {
				$lastMatchOnPart = $rule->getMatchOn();
				$query = $queryFactory->create($rule->getEntityName());
				return (bool) $query->matching($query->like($lastMatchOnPart, $matchValue))->execute()->count();
			} else {
				$matchOnParts = t3lib_div::trimExplode('.', $rule->getMatchOn());
				$matchOnTypes = array();
				if (!empty($matchOnParts)) {
					$classSchema = $this->reflectionService->getClassSchema($entity);
					foreach ($matchOnParts as $propertyName) {
						$propertyMeta = $classSchema->getProperty($propertyName);
						if (!Tx_Palm_Utility_TypeHandling::isAtomicType($propertyMeta['type'])) {
							$matchOnTypes[] = $propertyMeta['type'];
							$classSchema = $this->reflectionService->getClassSchema($propertyMeta['type']);
						} else {
							break;
						}
					}
				}
				end($matchOnParts);
				$lastMatchOnPart = current($matchOnParts);
				end($matchOnTypes);
				$lastMatchOnType = current($matchOnTypes);

				$query = $queryFactory->create($lastMatchOnType);
				return (bool) $query->matching($query->like($lastMatchOnPart, $matchValue))->execute()->count();
			}
		}
	}

	/**
	 * Enter description here ...
	 *
	 * @param Tx_Palm_Merger_RootRule $rule
	 * @return string
	 */
	public function getPropertyPathFromRule(Tx_Palm_Merger_RootRule $rule) {
		preg_match('|(?<=\{)(.*)(?=\})|', $rule->getSinglePathInCollection(), $entityPropertyPath);
		$entityPropertyPath = current($entityPropertyPath);
		if(!$entityPropertyPath) {
			// TODO throw some exception
		}
		return $entityPropertyPath;
	}


	/**
	 * Enter description here ...
	 * @param Tx_Extbase_DomainObject_AbstractDomainObject $entity
	 * @param Tx_Palm_Merger_RootRule $rule
	 */
	public function mergeByRule(Tx_Extbase_DomainObject_AbstractDomainObject $entity, Tx_Palm_Merger_RootRule $rule) {
		if(!$this->isRuleApplicableOnEntity($rule, $entity)) {
			// TODO throw exception that this rule is not applicable
		}
		$externalEntity = $this->getExternalEntityByExternalPath(
			$this->getDOMByRule($rule),
			$this->getResolvedSinglePathInCollection($rule, $entity),
			$rule->getEntityName()
		);
		$this->mergeEntitiesByRule($externalEntity, $entity, $rule);
	}

	/**
	 * @param DOMDocument $dom
	 * @param string $externalPath
	 * @param string $entityName
	 * @return Object
	 */
	public function getExternalEntityByExternalPath(DOMDocument $dom, $externalPath, $entityName) {
		/** @var DOMXPath $xpath */
		$xpath = $this->objectManager->create('DOMXPath', $dom);
		$result = $xpath->query($externalPath);
		if($result->length > 1) {
			// TODO throw exception that result is not distinct
		}
		/** @var Tx_Palm_DOM_Document $doc */
		$doc = $this->objectManager->create('Tx_Palm_DOM_Document');
		$doc->appendChild($doc->importNode($result->item(0), true));
		return $this->xmlSerializer->unserialize($doc, $entityName);
	}

	/**
	 * @param DOMDocument $dom
	 * @param string $externalPath
	 * @param string $entityName
	 * @param int $offset
	 * @param int $limit
	 * @return array
	 */
	public function getExternalEntitiesByExternalPath(DOMDocument $dom, $externalPath, $entityName, $offset = 0, $limit = null) {
		/** @var DOMXPath $xpath */
		$xpath = $this->objectManager->create('DOMXPath', $dom);
		$result = $xpath->query($externalPath);
		$limit = ($limit == NULL || $result->length < $limit) ? $result->length : $offset + $limit;
		$resultStorage = Array();
		for ($i=$offset; $i < $limit; $i++) {
			if ($result->item($i) === NULL) {
				continue;
			}
			/** @var Tx_Palm_DOM_Document $doc */
			$doc = $this->objectManager->create('Tx_Palm_DOM_Document');
			$doc->appendChild($doc->importNode($result->item($i), true));
			$newEntity = $this->xmlSerializer->unserialize($doc, $entityName);
			if ($newEntity !== NULL) {
				$resultStorage[] = $newEntity;
			}
		}
		return $resultStorage;
	}

	/**
	 * Enter description here ...
	 * @param Tx_Extbase_DomainObject_AbstractDomainObject $externalEntity
	 * @param Tx_Extbase_DomainObject_AbstractDomainObject $internalEntity
	 * @param Tx_Palm_Merger_AbstractRule $rule
	 */
	protected function mergeEntitiesByRule(Tx_Extbase_DomainObject_AbstractDomainObject $externalEntity, Tx_Extbase_DomainObject_AbstractDomainObject $internalEntity, Tx_Palm_Merger_AbstractRule $rule) {
		$nestedRules = Array();
		foreach ($rule->getNestedRules() as $nestedRule) {
			$nestedRules[$nestedRule->getWorkOn()] = $nestedRule;
		}

		$classSchema = $this->reflectionService->getClassSchema($internalEntity);
		$properties = $classSchema->getProperties();
		$configuredProperties = array_keys($nestedRules);
		$propertyNames = array_intersect(Tx_Palm_Reflection_ObjectAccess::getGettablePropertyNames($internalEntity), array_keys($properties));
		foreach ($propertyNames as $propertyName) {
			if(in_array($propertyName, array('uid','pid','_localizedUid', '_languageUid', '_cleanProperties', '_isClone'))) {
				continue;
			}
			$externalProperty	= Tx_Palm_Reflection_ObjectAccess::getProperty($externalEntity, $propertyName);
			$internalProperty	= Tx_Palm_Reflection_ObjectAccess::getProperty($internalEntity, $propertyName);
			$scope				= $this->determineScope($classSchema, $propertyName);
			$specificRule		= (isset($nestedRules[$propertyName])) ? $nestedRules[$propertyName] : $rule;
			$action				= $this->determineAction($specificRule, $scope, $externalProperty, $internalProperty);
			if($action === null) continue;

			$this->executeAction($specificRule, $propertyName, $scope, $action, $externalEntity, $externalProperty, $internalEntity, $internalProperty);
		}
	}


	protected function determineScope(Tx_Extbase_Reflection_ClassSchema $classSchema, $propertyName) {
		$propertyMetaData = $classSchema->getProperty($propertyName);
		if (in_array($propertyMetaData['type'], array('array', 'ArrayObject', 'SplObjectStorage', 'Tx_Extbase_Persistence_ObjectStorage'))) {
			return self::GETTER_SCOPE_COLLECTION;
		} elseif(strpos($propertyMetaData['type'], '_') !== false && !$propertyMetaData['type'] instanceof DateTime) {
			return self::GETTER_SCOPE_OBJECT;
		} else {
			return self::GETTER_SCOPE_PROPERTY;
		}
	}


	protected function determineAction(Tx_Palm_Merger_AbstractRule $specificRule, $scope, $externalProperty, $internalProperty) {
		if($scope == self::GETTER_SCOPE_COLLECTION) {
			$existence  = ($externalProperty instanceof Tx_Extbase_Persistence_ObjectStorage && $externalProperty->count() > 0) ? 1 : 0;
			$existence .= ($internalProperty instanceof Tx_Extbase_Persistence_ObjectStorage && $internalProperty->count() > 0) ? 1 : 0;
		} else {
			$existence  = ($externalProperty !== null) ? 1 : 0;
			$existence .= ($internalProperty !== null) ? 1 : 0;
		}
		switch($existence) {
			case '00':
				// Nothing to merge. Continue with next property
				return;
				break;
			case '10':
				return call_user_func(array($specificRule, 'getOnInternal' . $scope . 'Empty'));
				break;
			case '01':
				return call_user_func(array($specificRule, 'getOnExternal' . $scope . 'Empty'));
				break;
			case '11':
				return call_user_func(array($specificRule, 'getOnBoth' . $scope . 'NotEmpty'));
				break;
		}
	}


	protected function executeAction(Tx_Palm_Merger_AbstractRule $specificRule, $propertyName, $scope, $action, $externalEntity, $externalProperty, $internalEntity, $internalProperty) {
		switch($action) {
			case Tx_Palm_Merger_RuleInterface::ACTION_KEEP:
				// Do nothing
				break;
			case Tx_Palm_Merger_RuleInterface::ACTION_TAKE_EXTERNAL:
				Tx_Palm_Reflection_ObjectAccess::setProperty($internalEntity, $propertyName, $externalProperty);
				break;
			case Tx_Palm_Merger_RuleInterface::ACTION_DELETE:
				if ($scope === self::GETTER_SCOPE_COLLECTION) {
					$storage = Tx_Palm_Reflection_ObjectAccess::getProperty($internalEntity, $propertyName);
					$storage->removeAll(clone $storage);
				} else {
					Tx_Palm_Reflection_ObjectAccess::setProperty($internalEntity, $propertyName, null);
				}
				break;
			case Tx_Palm_Merger_RuleInterface::ACTION_MATCH_INDIVIDUAL:
				if($scope === self::GETTER_SCOPE_OBJECT) {
					if (!is_object($internalProperty)) {
						throw new Exception('The scope is defined as Object, but other than null or object is given. This error is potentially related to Extbase issue #25708.' ,1304080647);
					}
					$this->mergeEntitiesByRule($externalProperty, $internalProperty, $specificRule);
				} elseif ($scope === self::GETTER_SCOPE_COLLECTION) {
					$matchOns = t3lib_div::trimExplode(',', $specificRule->getMatchOn());
					if(!empty($matchOns) && !in_array('.', $matchOns)) {
						$entityReference = $this->buildReferenceArray(Array(), $externalProperty, $matchOns, 'external');
						$entityReference = $this->buildReferenceArray($entityReference, $internalProperty, $matchOns, 'internal');
						// Determine action for each external-internal pair
						foreach($entityReference as $entityMap) {
							$entityAction = $this->determineAction($specificRule, self::GETTER_SCOPE_OBJECT, $entityMap['external'], $entityMap['internal']);
							switch ($entityAction) {
								case Tx_Palm_Merger_RuleInterface::ACTION_KEEP:
									// Do nothing
									break;
								case Tx_Palm_Merger_RuleInterface::ACTION_TAKE_EXTERNAL:
									$internalProperty->attach($entityMap['external']);
									break;
								case Tx_Palm_Merger_RuleInterface::ACTION_DELETE:
									$internalProperty->detach($entityMap['internal']);
									break;
								case Tx_Palm_Merger_RuleInterface::ACTION_MATCH_INDIVIDUAL:
									$this->mergeEntitiesByRule($entityMap['external'], $entityMap['internal'], $specificRule);
									break;
							}
						}
					}
				}
				break;
			case Tx_Palm_Merger_RuleInterface::ACTION_LOOKUP:
				$lookUpRepositoryName = $specificRule->getLookUpRepository();
				if (empty($lookUpRepositoryName)) {
					throw new InvalidArgumentException('If a lookup action is specified, a lookup repository has to be set in palm configuration at the property "' . $propertyName .'"', 1310637800);
				}
				/** @var Tx_Extbase_Persistence_Typo3QuerySettings $querySettings */
				$querySettings = $this->objectManager->create('Tx_Extbase_Persistence_Typo3QuerySettings');
				$querySettings->setRespectStoragePage(FALSE);
				/** @var Tx_Extbase_Persistence_RepositoryInterface $lookUpRepository */
				$lookUpRepository = $this->objectManager->get($lookUpRepositoryName);
				$lookUpRepository->setDefaultQuerySettings($querySettings);
				$lookUpProperty = $specificRule->getMatchOn();
				$lookUpMethod = 'findOneBy' . ucfirst($lookUpProperty);
				$externalValue = Tx_Palm_Reflection_ObjectAccess::getProperty($externalEntity, $propertyName);
				if ($scope === self::GETTER_SCOPE_COLLECTION) {
					$newInternalValue = new Tx_Extbase_Persistence_ObjectStorage();
					foreach ($externalValue as $externalChild) {
						$externalChildValue = Tx_Palm_Reflection_ObjectAccess::getProperty($externalChild, $lookUpProperty);
						$internalChild = $lookUpRepository->$lookUpMethod($externalChildValue);
						if ($internalChild !== NULL) {
							$newInternalValue->attach($internalChild);
						}
					}
					if ($newInternalValue->count() > 0) {
						Tx_Palm_Reflection_ObjectAccess::setProperty($internalEntity, $propertyName, $newInternalValue);
					}
				} else {
					$externalChildValue = Tx_Palm_Reflection_ObjectAccess::getProperty($externalValue, $lookUpProperty);
					$internalChild = $lookUpRepository->$lookUpMethod($externalChildValue);
					if ($internalChild !== NULL) {
						Tx_Palm_Reflection_ObjectAccess::setProperty($internalEntity, $propertyName, $internalChild);
					}
				}
				break;
			default:
				throw new InvalidArgumentException('The given action is not one of the ones defined in Tx_Palm_Merger_RuleInterface::ACTION_* .', 1310637155);
				break;
		}
	}

	protected function buildReferenceArray($entityReference, $objectStorage, $matchOns, $scope) {
		// Index external entities by matchons
		$position = 1;
		foreach($objectStorage as $entity) {
			$referenceValue = array();
			foreach($matchOns as $matchOn) {
				if ($matchOn === '_position') {
					$referenceValue[] = $position;
					continue;
				}
				$reference = Tx_Palm_Reflection_ObjectAccess::getPropertyPath($entity, $matchOn);
				if (is_object($reference) && $reference instanceof DateTime) {
					$referenceValue[] = $reference->format("o-m-d\TH:i:s\Z");
				} elseif(is_object($reference)) {
					// TODO Throw exception
				} else {
					$referenceValue[] = $reference;
				}
			}
			$referenceValue = implode('###', $referenceValue);
			if($referenceValue && isset($entityReference[$referenceValue][$scope])) {
				$referenceIncrementor = -1;
				do {
					$referenceIncrementor++;
					$referenceFound = isset($entityReference[$referenceValue . '###' .  $referenceIncrementor]);
				} while($referenceFound);
				if (!isset($entityReference[$referenceValue])) {
					$entityReference[$referenceValue . '###' . $referenceIncrementor] = Array();
				}
				$entityReference[$referenceValue . '###' . $referenceIncrementor][$scope] = $entity;
			} else if($referenceValue) {
				if (!isset($entityReference[$referenceValue])) {
					$entityReference[$referenceValue] = Array();
				}
				$entityReference[$referenceValue][$scope] = $entity;
			}
			$position++;
		}
		return $entityReference;
	}

}
