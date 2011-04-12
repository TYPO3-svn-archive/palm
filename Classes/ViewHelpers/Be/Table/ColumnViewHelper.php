<?php

/*                                                                        *
 * This script belongs to the FLOW3 package "Fluid".                      *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License as published by the *
 * Free Software Foundation, either version 3 of the License, or (at your *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser       *
 * General Public License for more details.                               *
 *                                                                        *
 * You should have received a copy of the GNU Lesser General Public       *
 * License along with the script.                                         *
 * If not, see http://www.gnu.org/licenses/lgpl.html                      *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * "THEN" -> only has an effect inside of "IF". See If-ViewHelper for documentation.
 * @see Tx_Fluid_ViewHelpers_IfViewHelper
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @api
 * @scope prototype
 */
class Tx_Palm_ViewHelpers_Be_Table_ColumnViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractViewHelper {

	/**
	 * @var array
	 */
	protected $tableData;

	/**
	 * Sets tableData
	 *
	 * @param array $tableData
	 * @return void
	 */
	public function setTableData($tableData) {
		$this->tableData = $tableData;
	}

	/**
	 * Returns tableData
	 *
	 * @return array
	 */
	public function getTableData() {
		return $this->tableData;
	}


	/**
	 * Just render everything.
	 *
	 * @param int $index ab cd ef
	 * @param array $tableData the data from the table
	 * @param string $columnDataKey
	 * @return array the rendered table
	 */
	public function render($index, array $tableData = array(), $columnDataKey='PalmColumnData') {
		if (empty($tableData)) {
			$tableData = $this->templateVariableContainer->get('PalmTableData');
		}
		foreach ($tableData as $rowIndex=>$row) {
			foreach ($row as $columndIndex=>$columnData) {
				if ($index == $columndIndex) {
					$this->templateVariableContainer->add($columnDataKey, $columnData);
					$columnData = $this->renderChildren();
					$this->templateVariableContainer->remove($columnDataKey);
					$tableData[$rowIndex][$columndIndex] = $columnData;
				}
			}
		}
		return $tableData;
	}
}

?>