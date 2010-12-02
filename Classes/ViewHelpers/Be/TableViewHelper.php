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
 * View helper which allows you to create extbase based modules in the style of TYPO3 default modules.
 * Note: This feature is experimental!
 *
 * = Examples =
 *
 * <code title="Simple">
 * <f:be.container>your module content</f:be.container>
 * </code>
 *
 * Output:
 * "your module content" wrapped with propper head & body tags.
 * Default backend CSS styles and JavaScript will be included
 *
 * <code title="All options">
 * <f:be.container pageTitle="foo" enableJumpToUrl="false" enableClickMenu="false" loadPrototype="false" loadScriptaculous="false" scriptaculousModule="someModule,someOtherModule" loadExtJs="true" loadExtJsTheme="false" extJsAdapter="jQuery" enableExtJsDebug="true" addCssFile="{f:uri.resource(path:'styles/backend.css')}" addJsFile="{f:uri.resource('scripts/main.js')}">your module content</f:be.container>
 * </code>
 *
 * Output:
 * "your module content" wrapped with propper head & body tags.
 * Custom CSS file EXT:your_extension/Resources/Public/styles/backend.css and JavaScript file EXT:your_extension/Resources/Public/scripts/main.js will be loaded
 *
 * @author      Bastian Waidelich <bastian@typo3.org>
 * @license     http://www.gnu.org/copyleft/gpl.html
 * @version     SVN: $Id:
 *
 */
class Tx_Palm_ViewHelpers_Be_TableViewHelper extends Tx_Fluid_ViewHelpers_Be_AbstractBackendViewHelper implements Tx_Fluid_Core_ViewHelper_Facets_ChildNodeAccessInterface {

	/**
	 * An array of Tx_Fluid_Core_Parser_SyntaxTree_AbstractNode
	 * @var array
	 */
	private $childNodes = array();

	/**
	 * Setter for ChildNodes - as defined in ChildNodeAccessInterface
	 *
	 * @param array $childNodes Child nodes of this syntax tree node
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function setChildNodes(array $childNodes) {
		$this->childNodes = $childNodes;
	}

	/**
	 * Render start page with template.php and pageTitle
	 *
	 * @param array $tableData Table Data for tabe
	 * @param array $headerData Data for the table header
	 * @param array $tableLayout The table layout
	 * @param string $tableDataKey the table data key
	 * @return string
	 * @see template
	 */
	public function render(array $tableData, array $headerData = array(), $tableLayout = array(), $tableDataKey = 'PalmTableData') {
		$this->templateVariableContainer->add($tableDataKey, $tableData);
		if (!empty($this->childNodes)) {
			foreach ($this->childNodes as $childNode) {
				if ($childNode instanceof 	Tx_Fluid_Core_Parser_SyntaxTree_ViewHelperNode) {
					$tableData = $childNode->evaluate($this->getRenderingContext());
				}
			}
		}
		if (empty($tableLayout)) {
			$tableLayout = array(
				'table' => array(
					'<table border="0" cellspacing="0" cellpadding="0" class="typo3-dblist">', '</table>'
				),
				'0' => array(
					'tr'	=> array('<tr class="t3-row-header">', '</tr>'),
					'defCol'=> array('<td>', '</td>'),
				),
				'defRow' => array(
					'tr'	=> array('<tr class="db_list_normal">', '</tr>'),
					'defCol'=> array('<td>', '</td>'),
				)
			);
		}
		if(!empty($headerData)) {
			array_unshift($tableData, $headerData);
		}
		$doc = $this->getDocInstance();
		$this->templateVariableContainer->remove($tableDataKey);
		return $doc->table($tableData, $tableLayout);
	}
}
?>