<?php

function d($text) {
	echo '<!-- ' . $text . ' -->' . chr(10);
}

require_once(t3lib_extMgm::extPath('extbase') . 'Classes/Core/Bootstrap.php');

$objectManager = t3lib_div::makeInstance('Tx_Extbase_Object_ObjectManager');
$objectManager->registerImplementation('Tx_Extbase_Reflection_Service', 'Tx_Palm_Reflection_Service');

$bootstrap = t3lib_div::makeInstance('Tx_Extbase_Core_Bootstrap');
$bootstrap->initialize(array());

$classSchemaFactory = $objectManager->get('Tx_Palm_Reflection_ClassSchemaFactory');

$schemaGenerator = $objectManager->get('Tx_Palm_Xml_SchemaGenerator');
$schema = $schemaGenerator->generateSchema('Tx_Traveldb_Domain_Model_Accommodation');
//header('Content-type: application/xml');
echo $schema->saveXml();

?>