<?php

/**
 * Enter description here ...
 * @author tmaroschik
 *
 */
class Tx_Palm_Merger_Rule implements Tx_Palm_Merger_RuleInterface {

	/**
	 * Contains the path to the internal object
	 * @var string
	 */
	protected $internalPath;

	/**
	 * Contains the path to the external object
	 * @var string
	 */
	protected $externalPath;

	/**
	 * Contains the location of the external file
	 * @var string
	 */
	protected $fileLocation;

	/**
	 * Contains the name of the entity this rule should be applied to
	 * @var string
	 */
	protected $entityName;

	/**
	 * Contains the action to happen when external object is not found in internal object
	 * @var int
	 */
	protected $onNotFoundInInternal;

	/**
	 * Contains the action to happen when internal object is not found in external object
	 * @var int
	 */
	protected $onNotFoundInExternal;

	/**
	 * Contains the action to happen when internal object matches external object by reference
	 * @var int
	 */
	protected $onMatch;

	/**
	 * Contains nested rules
	 * @var Tx_Extbase_Persistence_ObjectStorage<Tx_Palm_Merger_Rule>
	 */
	protected $nestedRules;

	/**
	 * The Rule constructor
	 */
	public function __construct() {
		$this->nestedRules = new Tx_Extbase_Persistence_ObjectStorage();
	}

	/**
	 * Sets internalPath
	 *
	 * @param string $internalPath
	 * @return void
	 */
	public function setInternalPath($internalPath) {
		$this->internalPath = $internalPath;
	}

	/**
	 * Returns internalPath
	 *
	 * @return string
	 */
	public function getInternalPath() {
		return $this->internalPath;
	}

	/**
	 * Sets externalPath
	 *
	 * @param string $externalPath
	 * @return void
	 */
	public function setExternalPath($externalPath) {
		$this->externalPath = $externalPath;
	}

	/**
	 * Returns externalPath
	 *
	 * @return string
	 */
	public function getExternalPath() {
		return $this->externalPath;
	}

	/**
	 * Sets fileLocation
	 *
	 * @param string $fileLocation
	 * @return void
	 */
	public function setFileLocation($fileLocation) {
		$this->fileLocation = $fileLocation;
	}

	/**
	 * Returns fileLocation
	 *
	 * @return string
	 */
	public function getFileLocation() {
		return $this->fileLocation;
	}

	/**
	 * Sets entityName
	 *
	 * @param string $entityName
	 * @return void
	 */
	public function setEntityName($entityName) {
		$this->entityName = $entityName;
	}

	/**
	 * Returns entityName
	 *
	 * @return string
	 */
	public function getEntityName() {
		return $this->entityName;
	}

	/**
	 * Sets onNotFoundInInternal
	 *
	 * @param int $onNotFoundInInternal
	 * @return void
	 */
	public function setOnNotFoundInInternal($onNotFoundInInternal) {
		$this->onNotFoundInInternal = (int) $onNotFoundInInternal;
	}

	/**
	 * Returns onNotFoundInInternal
	 *
	 * @return int
	 */
	public function getOnNotFoundInInternal() {
		return $this->onNotFoundInInternal;
	}

	/**
	 * Sets onNotFoundInExternal
	 *
	 * @param int $onNotFoundInExternal
	 * @return void
	 */
	public function setOnNotFoundInExternal($onNotFoundInExternal) {
		$this->onNotFoundInExternal = (int) $onNotFoundInExternal;
	}

	/**
	 * Returns onNotFoundInExternal
	 *
	 * @return int
	 */
	public function getOnNotFoundInExternal() {
		return $this->onNotFoundInExternal;
	}

	/**
	 * Sets onMatch
	 *
	 * @param int $onMatch
	 * @return void
	 */
	public function setOnMatch($onMatch) {
		$this->onMatch = (int) $onMatch;
	}

	/**
	 * Returns onMatch
	 *
	 * @return int
	 */
	public function getOnMatch() {
		return $this->onMatch;
	}

	/**
	 * Sets nestedRules
	 * @param Tx_Extbase_Persistence_ObjectStorage<Tx_Palm_Merger_Rule> $nestedRules An object storage containing the nestedRuless to add
	 * @return void
	 */
	public function setNestedRules(Tx_Extbase_Persistence_ObjectStorage $nestedRules) {
		$this->nestedRules = $nestedRules;
	}

	/**
	 * Adds a nestedRule
	 *
	 * @param Tx_Palm_Merger_Rule $nestedRule
	 * @return void
	 */
	public function addNestedRule(Tx_Palm_Merger_Rule $nestedRule) {
		$this->nestedRules->attach($nestedRule);
	}

	/**
	 * Removes a nestedRule
	 *
	 * @param Tx_Palm_Merger_Rule $nestedRule
	 * @return void
	 */
	public function removeNestedRule(Tx_Palm_Merger_Rule $nestedRule) {
		$this->nestedRules->detach($nestedRule);
	}

	/**
	 * Returns the nestedRules
	 *
	 * @return Tx_Extbase_Persistence_ObjectStorage An object storage containing the nestedRule
	 */
	public function getNestedRules() {
		return $this->nestedRules;
	}


}

?>