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

		$testdoc = $this->objectManager->create('Tx_Palm_DOM_Document');
		$testdoc->loadXML('<?xml version="1.0" encoding="UTF-8"?>
<Accomodation uid="10"><Identity hotelName="The Westin Bund Center Shanghai**" uid="10"/></Accomodation>
		');

		$xmlSerializer = $this->objectManager->get('Tx_Palm_Xml_Serializer');
		$xmlSchemaGenerator = $this->objectManager->get('Tx_Palm_Xml_SchemaGenerator');


		$accommodation = $xmlSerializer->unserialize($testdoc, 'Tx_Traveldb_Domain_Model_Accommodation');

//		var_dump($accommodation);
//		$accommodation = 'O:38:"Tx_Traveldb_Domain_Model_Accommodation":16:{s:11:"' . "\0" . '*' . "\0" . 'identity";O:33:"Tx_Traveldb_Domain_Model_Identity":19:{s:12:"' . "\0" . '*' . "\0" . 'chainCode";s:0:"";s:12:"' . "\0" . '*' . "\0" . 'brandCode";s:0:"";s:12:"' . "\0" . '*' . "\0" . 'hotelCode";s:0:"";s:16:"' . "\0" . '*' . "\0" . 'hotelCityCode";s:0:"";s:12:"' . "\0" . '*' . "\0" . 'hotelName";s:33:"The Westin Bund Center Shanghai**";s:19:"' . "\0" . '*' . "\0" . 'hotelCodeContext";s:0:"";s:12:"' . "\0" . '*' . "\0" . 'chainName";s:0:"";s:12:"' . "\0" . '*' . "\0" . 'brandName";s:0:"";s:9:"' . "\0" . '*' . "\0" . 'areaID";N;s:20:"' . "\0" . '*' . "\0" . 'propertyClassType";s:0:"";s:13:"' . "\0" . '*' . "\0" . 'contentURL";N;s:11:"' . "\0" . '*' . "\0" . 'identity";N;s:6:"' . "\0" . '*' . "\0" . 'pid";N;s:6:"' . "\0" . '*' . "\0" . 'uid";i:10;s:7:"' . "\0" . '*' . "\0" . 'uuid";N;s:56:"' . "\0" . 'Tx_Extbase_DomainObject_AbstractEntity' . "\0" . '_cleanProperties";a:15:{s:9:"chainCode";s:0:"";s:9:"brandCode";s:0:"";s:9:"hotelCode";s:0:"";s:13:"hotelCityCode";s:0:"";s:9:"hotelName";s:33:"The Westin Bund Center Shanghai**";s:16:"hotelCodeContext";s:0:"";s:9:"chainName";s:0:"";s:9:"brandName";s:0:"";s:6:"areaID";N;s:17:"propertyClassType";s:0:"";s:10:"contentURL";N;s:8:"identity";N;s:3:"pid";N;s:3:"uid";i:10;s:4:"uuid";N;}s:16:"' . "\0" . '*' . "\0" . '_localizedUid";i:10;s:15:"' . "\0" . '*' . "\0" . '_languageUid";N;s:54:"' . "\0" . 'Tx_Extbase_DomainObject_AbstractDomainObject' . "\0" . '_isClone";b:0;}s:21:"' . "\0" . '*' . "\0" . 'accommodationClass";N;s:15:"' . "\0" . '*' . "\0" . 'roomProfiles";N;s:12:"' . "\0" . '*' . "\0" . 'mealPlans";N;s:13:"' . "\0" . '*' . "\0" . 'resortName";s:0:"";s:13:"' . "\0" . '*' . "\0" . 'resortCode";s:0:"";s:18:"' . "\0" . '*' . "\0" . 'destinationCode";s:0:"";s:19:"' . "\0" . '*' . "\0" . 'destinationLevel";N;s:18:"' . "\0" . '*' . "\0" . 'destinationName";N;s:6:"' . "\0" . '*' . "\0" . 'pid";N;s:6:"' . "\0" . '*' . "\0" . 'uid";i:10;s:7:"' . "\0" . '*' . "\0" . 'uuid";N;s:56:"' . "\0" . 'Tx_Extbase_DomainObject_AbstractEntity' . "\0" . '_cleanProperties";a:12:{s:8:"identity";O:33:"Tx_Traveldb_Domain_Model_Identity":19:{s:12:"' . "\0" . '*' . "\0" . 'chainCode";s:0:"";s:12:"' . "\0" . '*' . "\0" . 'brandCode";s:0:"";s:12:"' . "\0" . '*' . "\0" . 'hotelCode";s:0:"";s:16:"' . "\0" . '*' . "\0" . 'hotelCityCode";s:0:"";s:12:"' . "\0" . '*' . "\0" . 'hotelName";s:33:"The Westin Bund Center Shanghai**";s:19:"' . "\0" . '*' . "\0" . 'hotelCodeContext";s:0:"";s:12:"' . "\0" . '*' . "\0" . 'chainName";s:0:"";s:12:"' . "\0" . '*' . "\0" . 'brandName";s:0:"";s:9:"' . "\0" . '*' . "\0" . 'areaID";N;s:20:"' . "\0" . '*' . "\0" . 'propertyClassType";s:0:"";s:13:"' . "\0" . '*' . "\0" . 'contentURL";N;s:11:"' . "\0" . '*' . "\0" . 'identity";N;s:6:"' . "\0" . '*' . "\0" . 'pid";N;s:6:"' . "\0" . '*' . "\0" . 'uid";i:10;s:7:"' . "\0" . '*' . "\0" . 'uuid";N;s:56:"' . "\0" . 'Tx_Extbase_DomainObject_AbstractEntity' . "\0" . '_cleanProperties";a:15:{s:9:"chainCode";s:0:"";s:9:"brandCode";s:0:"";s:9:"hotelCode";s:0:"";s:13:"hotelCityCode";s:0:"";s:9:"hotelName";s:33:"The Westin Bund Center Shanghai**";s:16:"hotelCodeContext";s:0:"";s:9:"chainName";s:0:"";s:9:"brandName";s:0:"";s:6:"areaID";N;s:17:"propertyClassType";s:0:"";s:10:"contentURL";N;s:8:"identity";N;s:3:"pid";N;s:3:"uid";i:10;s:4:"uuid";N;}s:16:"' . "\0" . '*' . "\0" . '_localizedUid";i:10;s:15:"' . "\0" . '*' . "\0" . '_languageUid";N;s:54:"' . "\0" . 'Tx_Extbase_DomainObject_AbstractDomainObject' . "\0" . '_isClone";b:0;}s:18:"accommodationClass";N;s:12:"roomProfiles";N;s:9:"mealPlans";N;s:10:"resortName";s:0:"";s:10:"resortCode";s:0:"";s:15:"destinationCode";s:0:"";s:16:"destinationLevel";N;s:15:"destinationName";N;s:3:"pid";N;s:3:"uid";i:10;s:4:"uuid";N;}s:16:"' . "\0" . '*' . "\0" . '_localizedUid";i:10;s:15:"' . "\0" . '*' . "\0" . '_languageUid";N;s:54:"' . "\0" . 'Tx_Extbase_DomainObject_AbstractDomainObject' . "\0" . '_isClone";b:0;}';
//
//		$accommodation = unserialize($accommodation);

//		$accSchema = $xmlSchemaGenerator->generateSchema($accommodation);
//		$accDoc = $xmlSerializer->serialize($accommodation);

		return '';
	}
}
?>