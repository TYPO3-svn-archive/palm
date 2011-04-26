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
class Tx_Palm_ViewHelpers_Be_EntityLabelViewHelper extends Tx_Fluid_ViewHelpers_Be_AbstractBackendViewHelper {

	/**
	 * @var Tx_Extbase_Persistence_Mapper_DataMapper
	 */
	protected $dataMapper;

	/**
	 * Injector method for a data mapper
	 *
	 * @param Tx_Extbase_Persistence_Mapper_DataMapper $dataMapper
	 */
	public function injectDataMapper(Tx_Extbase_Persistence_Mapper_DataMapper $dataMapper) {
		$this->dataMapper = $dataMapper;
	}


	/**
	 * Render the backend label of an entity
	 *
	 * @param Tx_Extbase_DomainObject_DomainObjectInterface $entity
	 * @return string
	 */
	public function render(Tx_Extbase_DomainObject_DomainObjectInterface $entity) {
		$tableName = $this->dataMapper->convertClassNameToTableName(get_class($entity));
		t3lib_div::loadTCA($tableName);
		$labels = Array();
		if (isset($GLOBALS['TCA'][$tableName]['ctrl']['label'])) {
			$labels[] = $GLOBALS['TCA'][$tableName]['ctrl']['label'];
		}
		if (isset($GLOBALS['TCA'][$tableName]['ctrl']['label_alt']) && isset($GLOBALS['TCA'][$tableName]['ctrl']['label_alt_force'])) {
			$labels = array_merge($labels, t3lib_div::trimExplode(',', $GLOBALS['TCA'][$tableName]['ctrl']['label_alt']));
		}
		$columnNamePropertyNameMap = $this->buildColumnNamePropertyNameMap($entity);
		$output = Array();
		foreach ($labels as $labelColumnName) {
			if (isset($columnNamePropertyNameMap[$labelColumnName])) {
				$property = $columnNamePropertyNameMap[$labelColumnName];
				$tempLabel = Tx_Extbase_Reflection_ObjectAccess::getProperty($entity, $property);
				if (is_object($tempLabel) && $tempLabel instanceof Tx_Extbase_DomainObject_DomainObjectInterface) {
					$output[] = $this->render($tempLabel);
				} elseif (is_object($tempLabel) && $tempLabel instanceof DateTime) {
					$output[] = $tempLabel->format('c');
				} elseif (!is_object($tempLabel) && $tempLabel != '') {
					$output[] = $tempLabel;
				}
			}
		}
		return implode(', ', $output);
	}

	/**
	 * @param Tx_Extbase_DomainObject_DomainObjectInterface $entity
	 * @return array
	 */
	protected function buildColumnNamePropertyNameMap(Tx_Extbase_DomainObject_DomainObjectInterface $entity) {
		$columnNamePropertyNameMap = array();
		$className = get_class($entity);
		$dataMap = $this->dataMapper->getDataMap($className);
		$properties = Tx_Extbase_Reflection_ObjectAccess::getGettablePropertyNames($entity);
		foreach ($properties as $propertyName) {
			if ($dataMap->isPersistableProperty($propertyName)) {
				$columnMap = $dataMap->getColumnMap($propertyName);
				$columnNamePropertyNameMap[$columnMap->getColumnName()] = $propertyName;
			}
		}
		return $columnNamePropertyNameMap;
	}
}
?>