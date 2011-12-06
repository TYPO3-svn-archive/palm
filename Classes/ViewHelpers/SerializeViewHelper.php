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
class Tx_Palm_ViewHelpers_SerializeViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractViewHelper {

	/**
	 * @var Tx_Palm_Xml_Serializer
	 */
	protected $xmlSerializer;

	/**
	 * @param Tx_Palm_Xml_Serializer $xmlSerializer
	 * @return void
	 */
	public function injectXmlSerializer(Tx_Palm_Xml_Serializer $xmlSerializer) {
		$this->xmlSerializer = $xmlSerializer;
	}

	/**
	 * @param mixed $target
	 * @return void
	 */
	public function render($target) {
		if ($target instanceof Tx_Extbase_Persistence_LoadingStrategyInterface) {
			$target = $target->_loadRealInstance();
		}
		$document = $this->xmlSerializer->serialize($target);
		if ($document) {
			$result = '';
			foreach ($document->childNodes as $childNode) {
				$result .= $document->saveXML($childNode);
			}
			return $result;
		}
	}

}