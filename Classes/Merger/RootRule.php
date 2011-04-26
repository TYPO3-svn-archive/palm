<?php

/**
 * Enter description here ...
 * @author tmaroschik
 *
 */
class Tx_Palm_Merger_RootRule extends Tx_Palm_Merger_AbstractRule {

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
	 * Contains repositoryName
	 *
	 * @var string
	 */
	protected $repositoryName;

	/**
	 * Contains the single path of an element when in a list of xml objects
	 * @var string
	 */
	protected $singlePathInCollection;

	/**
	 * Contains the single distinct path of an element when in a list of xml objects
	 * @var string
	 */
	protected $singleEntityInCollection;

	/**
	 * Sets fileLocation
	 *
	 * @param string $fileLocation
	 * @return Tx_Palm_Merger_RootRule
	 */
	public function setFileLocation($fileLocation) {
		$this->fileLocation = $fileLocation;
		return $this;
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
	 * @return Tx_Palm_Merger_RootRule
	 */
	public function setEntityName($entityName) {
		$this->entityName = $entityName;
		return $this;
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
	 * Sets $repositoryName
	 *
	 * @param string $repositoryName
	 * @return Tx_Palm_Merger_RootRule
	 */
	public function setRepositoryName($repositoryName) {
		$this->repositoryName = $repositoryName;
		return $this;
	}

	/**
	 * Returns $repositoryName
	 *
	 * @return string
	 */
	public function getRepositoryName() {
		return $this->repositoryName;
	}

	/**
	 * Sets singlePathInCollection
	 *
	 * @param string $singlePathInCollection
	 * @return Tx_Palm_Merger_RootRule
	 */
	public function setSinglePathInCollection($singlePathInCollection) {
		$this->singlePathInCollection = $singlePathInCollection;
		return $this;
	}

	/**
	 * Returns singlePathInCollection
	 *
	 * @return string
	 */
	public function getSinglePathInCollection() {
		return $this->singlePathInCollection;
	}

	/**
	 * Sets distinctPathInCollection
	 *
	 * @param  $distinctPathInCollection
	 * @return Tx_Palm_Merger_RootRule
	 */
	public function setSingleEntityInCollection($distinctPathInCollection) {
		$this->singleEntityInCollection = $distinctPathInCollection;
		return $this;
	}

	/**
	 * Returns distinctPathInCollection
	 *
	 * @return string
	 */
	public function getSingleEntityInCollection() {
		return $this->singleEntityInCollection;
	}

}

?>