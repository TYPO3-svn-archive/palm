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
class tx_Palm_Scheduler_WorkerQueue implements t3lib_Singleton {

	const ACTION_MERGE = 0;
	const ACTION_IMPORT = 1;

	/**
	 * @var array
	 */
	protected $configuration = array();

	/**
	 * @var t3lib_DB
	 */
	protected $db;

	/**
	 * @var string
	 */
	protected $registryTableName = 'tx_palm_worker_queue';

	/**
	 * @return void
	 */
	public function __construct() {
		$this->db = $GLOBALS['TYPO3_DB'];
	}

	/**
	 * @param int $action
	 * @param string $fileLocation
	 * @param int $id
	 * @return void
	 */
	public function registerActionForRecord($action, $fileLocation, $id) {
		$existingRecord = $this->db->exec_SELECTgetSingleRow(
			'uid, action',
			$this->registryTableName,
			'file_location LIKE "' . $fileLocation . '" AND foreign_uid = ' . $id
		);
		if (!$existingRecord) {
			$this->db->exec_INSERTquery(
				$this->registryTableName,
				array(
					'tstamp'		=> time(),
					'action'		=> $action,
					'file_location'	=> $fileLocation,
					'foreign_uid'	=> $id,
				));
		} else {
			$this->db->exec_UPDATEquery(
				$this->registryTableName,
				'uid = ' . $existingRecord['uid'],
				array(
					'tstamp'	=> time(),
					'action'	=> $action
				)
			);
		}
	}

	/**
	 * @return array
	 */
	public function getRegisteredRecordActions($limit = '') {
		return $this->db->exec_SELECTgetRows('action, file_location,foreign_uid', $this->registryTableName, '', '', '', $limit);
	}

	/**
	 * @param string $fileLocation
	 * @param int $id
	 * @return void
	 */
	public function unregisterRecord($fileLocation, $id) {
		$this->db->exec_DELETEquery($this->registryTableName, 'file_location LIKE "' . $fileLocation . '" AND foreign_uid = ' . $id);
	}
}
