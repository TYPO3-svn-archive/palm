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
 * The array functions from the good old t3lib_div plus new code.
 *
 * @package Extbase
 * @subpackage Utility
 * @version $Id: Arrays.php 1992 2010-03-09 21:44:11Z jocrau $
 (robert) I'm not sure yet if we should use this library statically or as a singleton. The latter might be problematic if we use it from the Core classes.
 * @api
 */
class Tx_Palm_Utility_Arrays {

	/**
	 * Removes all elements recursively that equal (==) to false
	 *
	 * @param array $input
	 * @return array
	 */
	static public function arrayFilterRecursive(array $input) {
		foreach ($input as &$value) {
			if (is_array($value)) {
				$value = self::arrayFilterRecursive($value);
			}
		}
		return array_filter($input);
	}

}
?>