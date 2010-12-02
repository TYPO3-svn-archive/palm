<?php
if (!defined ('TYPO3_MODE'))    die ('Access denied.');

$TYPO3_CONF_VARS['FE']['eID_include']['SchemaGenerator'] = 'EXT:palm/Classes/Utility/SchemaGenerator.php';
$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_tceforms.php']['getMainFieldsClass'][]='EXT:palm/Classes/Hook/TCEForms.php:tx_Palm_Hook_TCEForms';

?>
