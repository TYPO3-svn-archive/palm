<?php
if (!defined ('TYPO3_MODE'))    die ('Access denied.');

	// Register caches if not already done in localconf.php or a previously loaded extension.
	// We do not set frontend and backend: The cache manager uses t3lib_cache_frontend_VariableFrontend
	// and t3lib_cache_backend_DbBackend by default if not set otherwise.
	// This default is perfectly fine for our reflection and object cache.
if (!is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['palm_reflection'])) {
	$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['palm_reflection'] = array(
		'backend' => 't3lib_cache_backend_DbBackend',
		'options' => array(
			'cacheTable' => 'tx_palm_cache_reflection',
			'tagsTable' => 'tx_palm_cache_reflection_tags',
		),
	);
}

$TYPO3_CONF_VARS['FE']['eID_include']['SchemaGenerator'] = 'EXT:palm/Classes/Utility/SchemaGenerator.php';
$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_tceforms.php']['getMainFieldsClass'][]='EXT:palm/Classes/Hook/TCEForms.php:tx_Palm_Hook_TCEForms';

if (t3lib_extMgm::isLoaded('scheduler')) {
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['tx_Palm_Scheduler_ImportTask'] = array(
		'extension'			=> $_EXTKEY,
		'title'				=> 'Run palm import task',
		'description'		=> 'Run palm import task to import a configured xml.',
		'additionalFields'	=> 'tx_Palm_Scheduler_Fields_ImportFields'
	);
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['tx_Palm_Scheduler_MergeTask'] = array(
		'extension'			=> $_EXTKEY,
		'title'				=> 'Run palm merge task',
		'description'		=> 'Run palm merge task to merge a configured xml.',
		'additionalFields'	=> 'tx_Palm_Scheduler_Fields_MergeFields'
	);
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['tx_Palm_Scheduler_WorkerTask'] = array(
		'extension'			=> $_EXTKEY,
		'title'				=> 'Run palm worker task',
		'description'		=> 'Run palm worker task to update all queued records.',
		'additionalFields'	=> 'tx_Palm_Scheduler_Fields_AbstractFields'
	);
}

?>
