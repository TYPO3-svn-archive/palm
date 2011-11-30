<?php

class Tx_Palm_Merger_RuleBuilder implements t3lib_Singleton {

	/**
	 * An instance of a object manager
	 *
	 * @var Tx_Extbase_Object_ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * Injector method for an object manager
	 *
	 * @param Tx_Extbase_Object_ObjectManagerInterface $objectManager
	 */
	public function injectObjectManager(Tx_Extbase_Object_ObjectManagerInterface $objectManager) {
		$this->objectManager = $objectManager;
	}

	/**
	 * Enter description here ...
	 *
	 * @param array $ruleConfiguration
	 * @return null|Tx_Palm_Merger_Rule
	 */
	public function build(array $ruleConfiguration) {
		if (!isset($ruleConfiguration[Tx_Palm_Merger_ServiceInterface::FILE_LOCATION]) && !isset($ruleConfiguration[Tx_Palm_Merger_ServiceInterface::ENTITY_NAME])) {
			return;
		}

		$fileLocation = $ruleConfiguration[Tx_Palm_Merger_ServiceInterface::FILE_LOCATION];
		$entityName = $ruleConfiguration[Tx_Palm_Merger_ServiceInterface::ENTITY_NAME];
		$singlePathInCollection = $ruleConfiguration[Tx_Palm_Merger_ServiceInterface::SINGLE_PATH_IN_COLLECTION];
		$singleEntityInCollection = $ruleConfiguration[Tx_Palm_Merger_ServiceInterface::SINGLE_ENTITY_IN_COLLECTION];

		$classHierarchy = array_values(class_parents($entityName, true));
		array_unshift($classHierarchy, $entityName);

		for ($i = 0; $i < count($classHierarchy); $i++) {
			$possibleRepositoryClassName = str_replace('_Model_', '_Repository_', $classHierarchy[$i], $replacementCount) . 'Repository';
			if ($replacementCount > 0 && class_exists($possibleRepositoryClassName)) {
				$repositoryName = $possibleRepositoryClassName;
				break;
			} elseif ($replacementCount == 0) {
				return null;
			}
		}

		foreach ($ruleConfiguration as $workOn=>$directive) {
			if (is_array($directive) && !empty($directive)) {
				$rule = $this->buildRule($workOn, $directive);
				$rule->setEntityName($entityName)
						->setRepositoryName($repositoryName)
						->setFileLocation($fileLocation)
						->setSinglePathInCollection($singlePathInCollection)
						->setSingleEntityInCollection($singleEntityInCollection);
				return $rule;
			}
		}
	}


	/**
	 * Build a rule
	 *
	 * @param string $workOn
	 * @param array $ruleDirective
	 * @param array $parentRuleSet
	 * @return Tx_Palm_Merger_Rule
	 */
	protected function buildRule($workOn, array $ruleDirective, array $parentRuleSet = array()) {
		if(empty($parentRuleSet)) {
			$rule = $this->objectManager->create('Tx_Palm_Merger_RootRule');
		} else {
			$rule = $this->objectManager->create('Tx_Palm_Merger_Rule');
		}
		$ruleSet = Array(
			'matchOn'					=> $ruleDirective[Tx_Palm_Merger_RuleInterface::MATCH_ON],
			'lookUpRepository'			=> $ruleDirective[Tx_Palm_Merger_RuleInterface::LOOKUP_REPOSITORY],
			'onExternalPropertyEmpty'	=> $ruleDirective[Tx_Palm_Merger_RuleInterface::ON_EXTERNAL_PROPERTY_EMPTY],
			'onInternalPropertyEmpty'	=> $ruleDirective[Tx_Palm_Merger_RuleInterface::ON_INTERNAL_PROPERTY_EMPTY],
			'onBothPropertyNotEmpty'	=> $ruleDirective[Tx_Palm_Merger_RuleInterface::ON_BOTH_PROPERTY_NOT_EMPTY],
			'onExternalObjectEmpty'		=> $ruleDirective[Tx_Palm_Merger_RuleInterface::ON_EXTERNAL_OBJECT_EMPTY],
			'onInternalObjectEmpty'		=> $ruleDirective[Tx_Palm_Merger_RuleInterface::ON_INTERNAL_OBJECT_EMPTY],
			'onBothObjectNotEmpty'		=> $ruleDirective[Tx_Palm_Merger_RuleInterface::ON_BOTH_OBJECT_NOT_EMPTY],
			'onExternalCollectionEmpty'	=> $ruleDirective[Tx_Palm_Merger_RuleInterface::ON_EXTERNAL_COLLECTION_EMPTY],
			'onInternalCollectionEmpty'	=> $ruleDirective[Tx_Palm_Merger_RuleInterface::ON_INTERNAL_COLLECTION_EMPTY],
			'onBothCollectionNotEmpty'	=> $ruleDirective[Tx_Palm_Merger_RuleInterface::ON_BOTH_COLLECTION_NOT_EMPTY],
		);
		$ruleSet = Tx_Palm_Utility_Arrays::arrayFilterRecursive($ruleSet);
		$ruleSet = t3lib_div::array_merge_recursive_overrule($parentRuleSet, $ruleSet);
		foreach ($ruleSet as $propertyName=>$property) {
			Tx_Extbase_Reflection_ObjectAccess::setProperty($rule, $propertyName, $property);
		}
		foreach ($ruleDirective as $key=>$directive) {
			if (is_array($directive)) {
				$nestedRule = $this->buildRule($key, $directive, $ruleSet);
				$rule->addNestedRule($nestedRule);
			}
		}
		$rule->setWorkOn($workOn);
		return $rule;
	}

}