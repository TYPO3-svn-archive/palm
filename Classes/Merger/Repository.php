<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Thomas Maroschik <tmaroschik@dfau.de>
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
 *  A copy is found in the textfile GPL.txt and important notices to the license
 *  from the author is found in LICENSE.txt distributed with these scripts.
 *
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Enter descriptions here
 *
 * @package $PACKAGE$
 * @subpackage $SUBPACKAGE$
 * @scope prototype
 * @entity
 * @api
 */
class Tx_Palm_Merger_Repository implements Tx_Extbase_Persistence_RepositoryInterface {

	/**
	 * Contains rule
	 *
	 * @var Tx_Palm_Merger_RootRule
	 */
	protected $rule;

	/**
	 * @var string
	 */
	protected $objectType;

	/**
	 * @var array
	 */
	protected $defaultOrderings = array();

	/**
	 * @var Tx_Extbase_Persistence_QuerySettingsInterface
	 */
	protected $defaultQuerySettings = NULL;

	/**
	 * @var Tx_Extbase_Object_ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * Constructs a new Repository
	 *
	 * @param Tx_Palm_Merger_RootRule $rootRule
	 */
	public function __construct(Tx_Palm_Merger_RootRule $rootRule) {
		$this->rule = $rootRule;
	}

	/**
	 * Injector method for a Tx_Extbase_Object_ObjectManagerInterface
	 *
	 * @param Tx_Extbase_Object_ObjectManagerInterface
	 */
	public function injectObjectManager(Tx_Extbase_Object_ObjectManagerInterface $objectManager) {
		$this->objectManager = $objectManager;
	}

	/**
	 * Adds an object to this repository
	 *
	 * @param object $object The object to add
	 * @return void
	 * @api
	 */
	public function add($object) {

	}

	/**
	 * Removes an object from this repository.
	 *
	 * @param object $object The object to remove
	 * @return void
	 * @api
	 */
	public function remove($object) {

	}

	/**
	 * Replaces an object by another.
	 *
	 * @param object $existingObject The existing object
	 * @param object $newObject The new object
	 * @return void
	 * @api
	 */
	public function replace($existingObject, $newObject) {

	}

	/**
	 * Replaces an existing object with the same identifier by the given object
	 *
	 * @param object $modifiedObject The modified object
	 * @api
	 */
	public function update($modifiedObject) {

	}

	/**
	 * Returns all addedObjects that have been added to this repository with add().
	 *
	 * This is a service method for the persistence manager to get all addedObjects
	 * added to the repository. Those are only objects *added*, not objects
	 * fetched from the underlying storage.
	 *
	 * @return Tx_Extbase_Persistence_ObjectStorage the objects
	 */
	public function getAddedObjects() {

	}

	/**
	 * Returns an Tx_Extbase_Persistence_ObjectStorage with objects remove()d from the repository
	 * that had been persisted to the storage layer before.
	 *
	 * @return Tx_Extbase_Persistence_ObjectStorage the objects
	 */
	public function getRemovedObjects() {

	}

	/**
	 * Returns all objects of this repository
	 *
	 * @return array An array of objects, empty if no objects found
	 * @api
	 */
	public function findAll() {
		return $this->createQuery()->statement($this->rule->getSingleEntityInCollection())->execute();
	}

	/**
	 * Returns the total number objects of this repository.
	 *
	 * @return integer The object count
	 * @api
	 */
	public function countAll() {
		return $this->createQuery()->statement($this->rule->getSingleEntityInCollection())->execute()->count();
	}

	/**
	 * Removes all objects of this repository as if remove() was called for
	 * all of them.
	 *
	 * @return void
	 * @api
	 */
	public function removeAll() {

	}

	/**
	 * Finds an object matching the given identifier.
	 *
	 * @param int $uid The identifier of the object to find
	 * @return object The matching object if found, otherwise NULL
	 * @api
	 */
	public function findByUid($uid) {

	}

	/**
	 * Sets the property names to order the result by per default.
	 * Expected like this:
	 * array(
	 *  'foo' => Tx_Extbase_Persistence_QueryInterface::ORDER_ASCENDING,
	 *  'bar' => Tx_Extbase_Persistence_QueryInterface::ORDER_DESCENDING
	 * )
	 *
	 * @param array $defaultOrderings The property names to order by
	 * @return void
	 * @api
	 */
	public function setDefaultOrderings(array $defaultOrderings) {
		$this->defaultOrderings = $defaultOrderings;
	}

	/**
	 * Sets the default query settings to be used in this repository
	 *
	 * @param Tx_Extbase_Persistence_QuerySettingsInterface $defaultQuerySettings The query settings to be used by default
	 * @return void
	 * @api
	 */
	public function setDefaultQuerySettings(Tx_Extbase_Persistence_QuerySettingsInterface $defaultQuerySettings) {
		$this->defaultQuerySettings = $defaultQuerySettings;
	}

	/**
	 * Returns a query for objects of this repository
	 *
	 * @return Tx_Extbase_Persistence_QueryInterface
	 * @api
	 */
	public function createQuery() {
		/** @var $query Tx_Palm_Merger_Query */
		$query = $this->objectManager->create('Tx_Palm_Merger_Query', $this->rule->getEntityName());
		$query->setSource($this->objectManager->create('Tx_Palm_Merger_FileLocation', $this->rule->getFileLocation()));
		if ($this->defaultOrderings !== array()) {
			$query->setOrderings($this->defaultOrderings);
		}
		if ($this->defaultQuerySettings !== NULL) {
			$query->setQuerySettings($this->defaultQuerySettings);
		} else {
			$query->setQuerySettings($this->objectManager->create('Tx_Extbase_Persistence_Typo3QuerySettings'));
		}
		return $query;
	}

	/**
	 * Returns the class name of this class.
	 *
	 * @return string Class name of the repository.
	 */
	protected function getRepositoryClassName() {
		return get_class($this);
	}

}

?>