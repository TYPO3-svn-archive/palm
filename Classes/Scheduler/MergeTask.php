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
class tx_Palm_Scheduler_MergeTask  extends tx_Palm_Scheduler_AbstractTask {

	/**
	 * Contains action
	 *
	 * @var string
	 */
	protected $action = 'mergeAllRecords';

	/**
	 * Contains fileName
	 *
	 * @var string
	 */
	protected $fileName;

	/**
	 * Contains queue
	 *
	 * @var string
	 */
	protected $queue;

	/**
	 * Sets $fileName
	 *
	 * @param string $fileName
	 */
	public function setFileName($fileName) {
		$this->fileName = $fileName;
	}

	/**
	 * Returns $fileName
	 *
	 * @return string
	 */
	public function getFileName() {
		return $this->fileName;
	}

	/**
	 * Sets $queue
	 *
	 * @param string $queue
	 */
	public function setQueue($queue) {
		$this->queue = $queue;
	}

	/**
	 * Returns $queue
	 *
	 * @return string
	 */
	public function getQueue() {
		return $this->queue;
	}

	/**
	 *
	 */
	protected function prepareGetArguments() {
		$_GET['id'] = $this->pid;
		$_GET['tx_' . $this->extensionName . '_' . $this->pluginName] = array(
			'action' => $this->action,
			'fileLocation' => $this->fileName,
			'queue' => $this->queue,
		);
	}

}