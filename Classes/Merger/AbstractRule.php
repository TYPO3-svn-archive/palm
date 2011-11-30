<?php

abstract class Tx_Palm_Merger_AbstractRule implements Tx_Palm_Merger_RuleInterface {

	/**
	 * @var string
	 */
	protected $matchOn;

	/**
	 * @var string
	 */
	protected $workOn;

	/**
	 * @var string
	 */
	protected $lookUpRepository;

	/**
	 * @var int
	 */
	protected $onExternalPropertyEmpty;

	/**
	 * @var int
	 */
	protected $onInternalPropertyEmpty;

	/**
	 * @var int
	 */
	protected $onBothPropertyNotEmpty;

	/**
	 * @var int
	 */
	protected $onExternalObjectEmpty;

	/**
	 * @var int
	 */
	protected $onInternalObjectEmpty;

	/**
	 * @var int
	 */
	protected $onBothObjectNotEmpty;

	/**
	 * @var int
	 */
	protected $onExternalCollectionEmpty;

	/**
	 * @var int
	 */
	protected $onInternalCollectionEmpty;

	/**
	 * @var int
	 */
	protected $onBothCollectionNotEmpty;

	/**
	 * Contains nested rules
	 * @var Tx_Extbase_Persistence_ObjectStorage<Tx_Palm_Merger_AbstractRule>
	 */
	protected $nestedRules;

	/**
	 * Constructor method
	 */
	public function __construct() {
		$this->nestedRules = t3lib_div::makeInstance('Tx_Extbase_Persistence_ObjectStorage');
	}


	/**
	 * Sets matchOn
	 *
	 * @param string $matchOn
	 * @return Tx_Palm_Merger_AbstractRule
	 */
	public function setMatchOn($matchOn) {
		$this->matchOn = $matchOn;
		return $this;
	}


	/**
	 * Returns matchOn
	 *
	 * @return string
	 */
	public function getMatchOn() {
		return $this->matchOn;
	}


	/**
	 * Sets workOn
	 *
	 * @param string $workOn
	 * @return Tx_Palm_Merger_AbstractRule
	 */
	public function setWorkOn($workOn) {
		$this->workOn = $workOn;
		return $this;
	}


	/**
	 * Returns workOn
	 *
	 * @return string
	 */
	public function getWorkOn() {
		return $this->workOn;
	}

	/**
	 * Sets lookUpRepository
	 *
	 * @param string $lookUpRepository
	 */
	public function setLookUpRepository($lookUpRepository) {
		$this->lookUpRepository = $lookUpRepository;
	}

	/**
	 * Returns lookUpRepository
	 *
	 * @return string
	 */
	public function getLookUpRepository() {
		return $this->lookUpRepository;
	}


	/**
	 * Sets onExternalPropertyEmpty
	 *
	 * @param int $onExternalPropertyEmpty
	 * @return Tx_Palm_Merger_AbstractRule
	 */
	public function setOnExternalPropertyEmpty($onExternalPropertyEmpty) {
		$this->onExternalPropertyEmpty = $onExternalPropertyEmpty;
		return $this;
	}


	/**
	 * Returns onExternalPropertyEmpty
	 *
	 * @return int
	 */
	public function getOnExternalPropertyEmpty() {
		return $this->onExternalPropertyEmpty;
	}


	/**
	 * Sets onInternalPropertyEmpty
	 *
	 * @param int $onInternalPropertyEmpty
	 * @return Tx_Palm_Merger_AbstractRule
	 */
	public function setOnInternalPropertyEmpty($onInternalPropertyEmpty) {
		$this->onInternalPropertyEmpty = $onInternalPropertyEmpty;
		return $this;
	}


	/**
	 * Returns onInternalPropertyEmpty
	 *
	 * @return int
	 */
	public function getOnInternalPropertyEmpty() {
		return $this->onInternalPropertyEmpty;
	}


	/**
	 * Sets onBothPropertyNotEmpty
	 *
	 * @param int $onBothPropertyNotEmpty
	 * @return Tx_Palm_Merger_AbstractRule
	 */
	public function setOnBothPropertyNotEmpty($onBothPropertyNotEmpty) {
		$this->onBothPropertyNotEmpty = $onBothPropertyNotEmpty;
		return $this;
	}


	/**
	 * Returns onBothPropertyNotEmpty
	 *
	 * @return int
	 */
	public function getOnBothPropertyNotEmpty() {
		return $this->onBothPropertyNotEmpty;
	}


	/**
	 * Sets onExternalObjectEmpty
	 *
	 * @param int $onExternalObjectEmpty
	 * @return Tx_Palm_Merger_AbstractRule
	 */
	public function setOnExternalObjectEmpty($onExternalObjectEmpty) {
		$this->onExternalObjectEmpty = $onExternalObjectEmpty;
		return $this;
	}


	/**
	 * Returns onExternalObjectEmpty
	 *
	 * @return int
	 */
	public function getOnExternalObjectEmpty() {
		return $this->onExternalObjectEmpty;
	}


	/**
	 * Sets onInternalObjectEmpty
	 *
	 * @param int $onInternalObjectEmpty
	 * @return Tx_Palm_Merger_AbstractRule
	 */
	public function setOnInternalObjectEmpty($onInternalObjectEmpty) {
		$this->onInternalObjectEmpty = $onInternalObjectEmpty;
		return $this;
	}


	/**
	 * Returns onInternalObjectEmpty
	 *
	 * @return int
	 */
	public function getOnInternalObjectEmpty() {
		return $this->onInternalObjectEmpty;
	}


	/**
	 * Sets onBothObjectNotEmpty
	 *
	 * @param int $onBothObjectNotEmpty
	 * @return Tx_Palm_Merger_AbstractRule
	 */
	public function setOnBothObjectNotEmpty($onBothObjectNotEmpty) {
		$this->onBothObjectNotEmpty = $onBothObjectNotEmpty;
		return $this;
	}


	/**
	 * Returns onBothObjectNotEmpty
	 *
	 * @return int
	 */
	public function getOnBothObjectNotEmpty() {
		return $this->onBothObjectNotEmpty;
	}


	/**
	 * Sets onExternalCollectionEmpty
	 *
	 * @param int $onExternalCollectionEmpty
	 * @return Tx_Palm_Merger_AbstractRule
	 */
	public function setOnExternalCollectionEmpty($onExternalCollectionEmpty) {
		$this->onExternalCollectionEmpty = $onExternalCollectionEmpty;
		return $this;
	}


	/**
	 * Returns onExternalCollectionEmpty
	 *
	 * @return int
	 */
	public function getOnExternalCollectionEmpty() {
		return $this->onExternalCollectionEmpty;
	}


	/**
	 * Sets onInternalCollectionEmpty
	 *
	 * @param int $onInternalCollectionEmpty
	 * @return Tx_Palm_Merger_AbstractRule
	 */
	public function setOnInternalCollectionEmpty($onInternalCollectionEmpty) {
		$this->onInternalCollectionEmpty = $onInternalCollectionEmpty;
		return $this;
	}


	/**
	 * Returns onInternalCollectionEmpty
	 *
	 * @return int
	 */
	public function getOnInternalCollectionEmpty() {
		return $this->onInternalCollectionEmpty;
	}


	/**
	 * Sets onBothCollectionNotEmpty
	 *
	 * @param int $onBothCollectionNotEmpty
	 * @return Tx_Palm_Merger_AbstractRule
	 */
	public function setOnBothCollectionNotEmpty($onBothCollectionNotEmpty) {
		$this->onBothCollectionNotEmpty = $onBothCollectionNotEmpty;
		return $this;
	}


	/**
	 * Returns onBothCollectionNotEmpty
	 *
	 * @return int
	 */
	public function getOnBothCollectionNotEmpty() {
		return $this->onBothCollectionNotEmpty;
	}


	/**
	 * Sets nestedRules
	 * @param Tx_Extbase_Persistence_ObjectStorage<Tx_Palm_Merger_AbstractRule> $nestedRules An object storage containing the nestedRuless to add
	 * @return Tx_Palm_Merger_AbstractRule
	 */
	public function setNestedRules(Tx_Extbase_Persistence_ObjectStorage $nestedRules) {
		$this->nestedRules = $nestedRules;
		return $this;
	}


	/**
	 * Adds a nestedRule
	 *
	 * @param Tx_Palm_Merger_AbstractRule $nestedRule
	 * @return void
	 */
	public function addNestedRule(Tx_Palm_Merger_AbstractRule $nestedRule) {
		$this->nestedRules->attach($nestedRule);
	}


	/**
	 * Removes a nestedRule
	 *
	 * @param Tx_Palm_Merger_AbstractRule $nestedRule
	 * @return void
	 */
	public function removeNestedRule(Tx_Palm_Merger_AbstractRule $nestedRule) {
		$this->nestedRules->detach($nestedRule);
	}


	/**
	 * Returns the nestedRules
	 *
	 * @return Tx_Extbase_Persistence_ObjectStorage<Tx_Palm_Merger_AbstractRule> An object storage containing the nestedRule
	 */
	public function getNestedRules() {
		return $this->nestedRules;
	}


}