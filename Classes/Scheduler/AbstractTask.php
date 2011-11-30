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
class tx_Palm_Scheduler_AbstractTask  extends tx_scheduler_Task {

	/**
	 * Contains extensionName
	 *
	 * @var string
	 */
	protected $extensionName = 'palm';

	/**
	 * Contains pluginName
	 *
	 * @var string
	 */
	protected $pluginName = 'web_palmtxpalmm1';

	/**
	 * Contains action
	 *
	 * @var string
	 */
	protected $action = 'index';

	/**
	 * Contains pid
	 *
	 * @var int
	 */
	protected $pid;

	/**
	 * Contains pulldataController
	 *
	 * @var Tx_Palm_Controller_PullDataController
	 */
	protected $pulldataController;

	/**
	 * Sets $pid
	 *
	 * @param int $pid
	 */
	public function setPid($pid) {
		$this->pid = $pid;
	}

	/**
	 * Returns $pid
	 *
	 * @return int
	 */
	public function getPid() {
		return $this->pid;
	}

	/**
	 * @return bool
	 */
	public function execute() {
		try {
			$this->dispatch();
		} catch (Exception $e) {
			throw new tx_DfauSbt_Solr_SyncTaskException($e->getMessage(), $e->getCode());
		}
		return true;
	}


	protected function prepareGetArguments() {
		$_GET['id'] = $this->pid;
		$_GET['tx_' . $this->extensionName . '_' . $this->pluginName] = array(
			'action' => $this->action
		);
	}

	/**
	 * Called by ajax.php / eID.php
	 * Builds an extbase context and returns the response
	 */
	protected function dispatch() {
		$GLOBALS['_SERVER']['REQUEST_METHOD'] = 'GET';
		$this->prepareGetArguments();
		/** @var $dispatcher Tx_Extbase_Core_Bootstrap */
		$dispatcher = t3lib_div::makeInstance('Tx_Palm_Scheduler_Bootstrap');
		$dispatcher->callModule('web_PalmTxPalmM1');
	}

}