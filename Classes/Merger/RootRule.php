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
	 * Sets $repositoryName
	 *
	 * @param string $repositoryName
	 */
	public function setRepositoryName($repositoryName) {
		$this->repositoryName = $repositoryName;
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
	 * @return void
	 */
	public function setSinglePathInCollection($singlePathInCollection) {
		$this->singlePathInCollection = $singlePathInCollection;
	}

	/**
	 * Returns singlePathInCollection
	 *
	 * @return string
	 */
	public function getSinglePathInCollection() {
		return $this->singlePathInCollection;
	}

}

?>