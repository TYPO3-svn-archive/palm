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
	 * @var Tx_Extbase_Persistence_ObjectStorage
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
	 * Constructor method for the merger service
	 */
	public function __construct() {
		if(@file_exists(PATH_site . 'typo3conf/palmconf.php')) {
			require_once(PATH_site . 'typo3conf/palmconf.php');
			if(isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['palm']) && is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['palm'])) {
				$this->configuration = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['palm'];
			}
		}
		$this->pullRules = t3lib_div::makeInstance('Tx_Extbase_Persistence_ObjectStorage');
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

	protected function initializeDomForRule(Tx_Palm_Merger_Rule $rule) {
		$dom = $this->objectManager->create('Tx_Palm_DOM_Document');
		$dom->load(t3lib_div::getFileAbsFileName($rule->getFileLocation()));
	}


	public function getPullConfiguration() {
		return $this->configuration['pull'];
	}

	public function getPullableEntities() {
		$this->initializePullRules();
		$entities = Array();
		foreach ($this->pullRules as $rule) {
			if (!isset($entities[$rule->getEntityName()])) {
				$entities[$rule->getEntityName()] = 0;
			}
			$entities[$rule->getEntityName()]++;
		}
		return $entities;
	}

	public function getPullRulesByEntityName($entityName) {
		$this->initializePullRules();
		$rules = Array();
		foreach ($this->pullRules as $rule) {
			if($rule->getEntityName() == $entityName) {
				$rules[$rule->getFileLocation()] = $rule;
			}
		}
		return $rules;
	}

	public function getPullRuleByFileLocation($fileLocation) {
		$this->initializePullRules();
		foreach ($this->pullRules as $rule) {
			if($rule->getFileLocation() == $fileLocation) {
				return $rule;
			}
		}
	}

	public function getPullRules() {
		$this->initializePullRules();
		return $this->pullRules;
	}

	protected function getDOMByRule(Tx_Palm_Merger_Rule $rule) {
		$this->initializeDOMForRule($rule);
	}

	public function chefIfRuleIsApplicableOnEntity(Tx_Palm_Merger_Rule $rule, Tx_Extbase_DomainObject_AbstractDomainObject $entity) {
		$dom = $this->getDOMByRule($rule);
		$internalPropertyPath = $this->getPropertyPathFromRule($rule);
		$internalPropertyValue = Tx_Extbase_Reflection_ObjectAccess::getPropertyPath($entity, $internalPropertyPath);
		$externalPath = str_replace('{' . $internalPropertyPath . '}', $internalPropertyValue, $rule->getExternalPath());
		var_dump($externalPath);
		return true;
	}

	/**
	 * Enter description here ...
	 *
	 * @param Tx_Palm_Merger_Rule $rule
	 * @return string
	 */
	protected function getPropertyPathFromRule(Tx_Palm_Merger_Rule $rule) {
		preg_match('|(?<=\{)(.*)(?=\})|', $rule->getExternalPath(), $entityPropertyPath);
		$entityPropertyPath = current($entityPropertyPath);
		if(!$entityPropertyPath) {
			// TODO throw some exception
		}
		return $entityPropertyPath;
	}


}
?>