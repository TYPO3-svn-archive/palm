<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 Jochen Rau <jochen.rau@typoplanet.de>
*  All rights reserved
*
*  This class is a backport of the corresponding class of FLOW3.
*  All credits go to the v5 team.
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
 * Performs a join between two node-tuple sources.
 *
 * @package Extbase
 * @subpackage Persistence\QOM
 * @version $Id: Join.php 1729 2009-11-25 21:37:20Z stucki $
 * @scope prototype
 */
class Tx_Palm_Merger_FileLocation implements Tx_Extbase_Persistence_QOM_SourceInterface {

	/**
	 * @var string
	 */
	protected $location;

	/**
	 * @param string $location
	 */
	public function __construct($location = null) {
		$this->location = $location;
	}

	/**
	 * @return string
	 */
	public function getAbsoluteLocation() {
		return t3lib_div::getFileAbsFileName($this->location);
	}

	/**
	 * @param $location
	 */
	public function setLocation($location) {
		$this->location = $location;
	}

	/**
	 * @return string
	 */
	public function getLocation() {
		return $this->location;
	}

}

?>