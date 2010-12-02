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

	public function build(array $ruleConfiguration) {
		if (!isset($ruleConfiguration[Tx_Palm_Merger_ServiceInterface::FILE_LOCATION]) && !isset($ruleConfiguration[Tx_Palm_Merger_ServiceInterface::ENTITY_NAME])) {
			return;
		}

		$fileLocation = $ruleConfiguration[Tx_Palm_Merger_ServiceInterface::FILE_LOCATION];
		$entityName = $ruleConfiguration[Tx_Palm_Merger_ServiceInterface::ENTITY_NAME];

		$possibleRepositoryClassName = str_replace('_Model_', '_Repository_', $entityName) . 'Repository';
		if (!class_exists($possibleRepositoryClassName)) {
			return;
		}

		foreach ($ruleConfiguration as $ruleDirective) {
			if (is_array($ruleDirective) && !empty($ruleDirective)) {
				$rule = $this->buildRule($ruleDirective);
				$rule->setEntityName($entityName);
				$rule->setFileLocation($fileLocation);
				return $rule;
			}
		}
	}


	/**
	 * Build a rule
	 *
	 * @param array $ruleDirective
	 * @return Tx_Palm_Merger_Rule
	 */
	protected function buildRule(array $ruleDirective) {
		$rule = $this->objectManager->create('Tx_Palm_Merger_Rule');
		if(isset($ruleDirective[Tx_Palm_Merger_RuleInterface::MATCH_INTERNAL_PATH])) {
			$rule->setInternalPath($ruleDirective[Tx_Palm_Merger_RuleInterface::MATCH_INTERNAL_PATH]);
			unset($ruleDirective[Tx_Palm_Merger_RuleInterface::MATCH_INTERNAL_PATH]);
		}
		if(isset($ruleDirective[Tx_Palm_Merger_RuleInterface::MATCH_EXTERNAL_PATH])) {
			$rule->setExternalPath($ruleDirective[Tx_Palm_Merger_RuleInterface::MATCH_EXTERNAL_PATH]);
			unset($ruleDirective[Tx_Palm_Merger_RuleInterface::MATCH_EXTERNAL_PATH]);
		}
		if(isset($ruleDirective[Tx_Palm_Merger_RuleInterface::ON_NOT_FOUND_IN_INTERNAL])) {
			$rule->setOnNotFoundInInternal($ruleDirective[Tx_Palm_Merger_RuleInterface::ON_NOT_FOUND_IN_INTERNAL]);
			unset($ruleDirective[Tx_Palm_Merger_RuleInterface::ON_NOT_FOUND_IN_INTERNAL]);
		}
		if(isset($ruleDirective[Tx_Palm_Merger_RuleInterface::ON_NOT_FOUND_IN_EXTERNAL])) {
			$rule->setOnNotFoundInExternal($ruleDirective[Tx_Palm_Merger_RuleInterface::ON_NOT_FOUND_IN_EXTERNAL]);
			unset($ruleDirective[Tx_Palm_Merger_RuleInterface::ON_NOT_FOUND_IN_EXTERNAL]);
		}
		if(isset($ruleDirective[Tx_Palm_Merger_RuleInterface::ON_MATCH])) {
			$rule->setOnMatch($ruleDirective[Tx_Palm_Merger_RuleInterface::ON_MATCH]);
			unset($ruleDirective[Tx_Palm_Merger_RuleInterface::ON_MATCH]);
		}
		if(!empty($ruleDirective)) {
			foreach ($ruleDirective as $directive) {
				if (is_array($directive) && !empty($directive)) {
					$nestedRule = $this->buildRule($directive);
					$rule->addNestedRule($nestedRule);
				}
			}
		}
		return $rule;
	}

}

?>