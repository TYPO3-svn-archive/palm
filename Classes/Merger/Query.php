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
 * The Query class used to run queries against the database
 *
 * @package Extbase
 * @subpackage Persistence
 * @version $Id$
 * @scope prototype
 * @api
 */
class Tx_Palm_Merger_Query extends Tx_Extbase_Persistence_Query {

	/**
	 * Executes the query against the database and returns the result
	 *
	 * @return Tx_Extbase_Persistence_QueryResultInterface|array The query result object or an array if $this->getQuerySettings()->getReturnRawQueryResult() is TRUE
	 * @api
	 */
	public function execute() {
//		if ($this->getQuerySettings()->getReturnRawQueryResult() === TRUE) {
//			return $this->persistenceManager->getObjectDataByQuery($this);
//		} else {
			return $this->objectManager->create('Tx_Palm_Merger_QueryResult', $this);
//		}
	}

	/**
	 * Executes the number of matching objects for the query
	 *
	 * @return integer The number of matching objects
	 * @deprecated since Extbase 1.3.0; was removed in FLOW3; will be removed in Extbase 1.4.0; use Query::execute()::count() instead
	 * @api
	 */
	public function count() {
		t3lib_div::logDeprecatedFunction();
		return $this->execute()->count();
	}


}
?>