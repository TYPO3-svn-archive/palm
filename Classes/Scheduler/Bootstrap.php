<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 Jochen Rau <jochen.rau@typoplanet.de>
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
 * Creates a request an dispatches it to the controller which was specified
 * by TS Setup, flexForm and returns the content to the v4 framework.
 *
 * This class is the main entry point for extbase extensions.
 *
 * @package Extbase
 * @version $ID:$
 */
class Tx_Palm_Scheduler_Bootstrap extends Tx_Extbase_Core_Bootstrap {

	/**
	 * Runs the the Extbase Framework by resolving an appropriate Request Handler and passing control to it.
	 * If the Framework is not initialized yet, it will be initialized.
	 *
	 * @param string $content The content. Not used
	 * @param array $configuration The TS configuration array
	 * @return string $content The processed content
	 * @api
	 */
	public function run($content, $configuration) {
		$this->initialize($configuration);
		$this->handleSchedulerRequest();
	}

	/**
	 * @return string
	 */
	protected function handleSchedulerRequest() {
		/** @var $requestHandler Tx_Extbase_MVC_Web_BackendRequestHandler */
		$requestHandler = $this->objectManager->get('Tx_Extbase_MVC_Web_BackendRequestHandler');
		$requestHandler->handleRequest();
		$this->resetSingletons();
	}

}