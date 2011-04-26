<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 Christian Müller <christian@kitsunet.de>
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
 * PHP type handling functions
 *
 * @package Extbase
 * @subpackage Utility
 * @version $ID:$
 * @api
 */
class Tx_Palm_Utility_TypeHandling extends Tx_Extbase_Utility_TypeHandling {

	/**
	 * @param mixed $type
	 * @return boolean
	 */
	static public function isAtomicType($type) {
		$type = parent::normalizeType($type);
		return $type == "string"
			|| $type == "integer"
			|| $type == "boolean"
			|| $type == "float"
			|| $type == "double"
			|| $type == "DateTime";
	}

}
?>