<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2011 Thomas Maroschik <tmaroschik@dfau.de>
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
class Tx_Palm_Persistence_QueryResult extends Tx_Extbase_Persistence_QueryResult {

	/**
	 * @var Tx_Palm_Merger_RootRule
	 */
	protected $applicableRule;

	/**
	 * @var Tx_Palm_Merger_Service
	 */
	protected $mergerService;


	/**
	 * Constructor
	 *
	 * @param Tx_Extbase_Persistence_QueryInterface $query
	 */
	public function __construct(Tx_Extbase_Persistence_QueryInterface $query) {
		$this->query = $query;
		if ($this->query->getQuerySettings() instanceof Tx_Palm_Persistence_MergerQuerySettings) {
			$this->applicableRule = $this->query->getQuerySettings()->getApplicableRule();
		}
	}

	/**
	 * Injector method for a merger service
	 *
	 * @param Tx_Palm_Merger_ServiceInterface $mergerService
	 */
	public function injectMergerService(Tx_Palm_Merger_ServiceInterface $mergerService) {
		$this->mergerService = $mergerService;
	}

	/**
	 * Loads the objects this QueryResult is supposed to hold
	 *
	 * @return void
	 */
	protected function initialize() {
		if ($this->applicableRule === NULL) {
			parent::initialize();
		} else {
			if (!is_array($this->queryResult)) {
				$dom = $this->mergerService->getDOMByRule($this->applicableRule);
				$offset = ($this->query->getOffset() !== NULL) ? $this->query->getOffset() : 0;
				$limit = ($this->query->getLimit() !== NULL) ? $this->query->getLimit() : NULL;
				$this->queryResult = $this->mergerService->getExternalEntitiesByExternalPath(
					$dom,
					$this->applicableRule->getSingleEntityInCollection(),
					$this->applicableRule->getEntityName(),
					$offset,
					$limit
				);
			}
		}
	}


	/**
	 * Returns the first object in the result set
	 *
	 * @return object
	 * @api
	 */
	public function getFirst() {
		if ($this->applicableRule === NULL) {
			return parent::getFirst();
		} else {
			$this->initialize();
			if (is_array($this->queryResult)) {
				$queryResult = $this->queryResult;
				reset($queryResult);
			} else {
				return NULL;
			}
			$firstResult = current($queryResult);
			if ($firstResult === FALSE) {
				$firstResult = NULL;
			}
			return $firstResult;
		}
	}

	/**
	 * Returns the number of objects in the result
	 *
	 * @return integer The number of matching objects
	 * @api
	 */
	public function count() {
		if ($this->applicableRule === NULL) {
			return parent::count();
		} else {
			$this->initialize();
			return count($this->queryResult);
		}
	}

}
