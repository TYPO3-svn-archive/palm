<?php

/***************************************************************
*  Copyright notice
*
*  (c) 2010 Thomas Maroschik <tmaroschik@dfau.de>
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
 * The Demo Controller
 * @version $Id:$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class Tx_Palm_Controller_DemoController extends Tx_Extbase_MVC_Controller_ActionController {

	public function indexAction() {
		$xmlSerializer = $this->objectManager->get('Tx_Palm_Xml_Serializer');
//		$xmlSchemaGenerator = $this->objectManager->get('Tx_Palm_Xml_SchemaGenerator');
//		$tourInformationItemRepository = $this->objectManager->get('Tx_Traveldb_Domain_Repository_TourInformationItemRepository');
		//$tourInformationItem = $tourInformationItemRepository->findByUid(10);
//		$this->response->addAdditionalHeaderData('Content-type: application/xml');

		$toursDoc = $this->objectManager->create('Tx_Palm_DOM_Document');
		$toursDoc->load('/Users/tmaroschik/Sites/renatour-web/typo3conf/ext/traveldbtour32/testout.xml');
		$xpath = new DOMXPath($toursDoc);
		$entries = $xpath->query("//TourInfomationItem[@ID='104']");

		foreach ($entries as $entry) {
			$tourDoc = $this->objectManager->create('Tx_Palm_DOM_Document');
			$tourDoc->appendChild($tourDoc->importNode($entry, true));
		}

		$accDoc = $xmlSerializer->unserialize($tourDoc, 'Tx_Traveldb_Domain_Model_TourInformationItem');
		var_dump($accDoc);
die();
//		$accDoc = $xmlSchemaGenerator->generateSchema('Tx_Traveldb_Domain_Model_TourInformationItem');
		return $accDoc->saveXML();
	}
}