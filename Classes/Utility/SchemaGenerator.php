<?php

// This is a demo dispatcher

require_once(t3lib_extMgm::extPath('extbase') . 'Classes/Core/Bootstrap.php');

$TSFE = t3lib_div::makeInstance('tslib_fe',
	$TYPO3_CONF_VARS,
	2,
	t3lib_div::_GP('type'),
	t3lib_div::_GP('no_cache'),
	t3lib_div::_GP('cHash'),
	t3lib_div::_GP('jumpurl'),
	t3lib_div::_GP('MP'),
	t3lib_div::_GP('RDCT')
);

$TSFE->initFEuser();
$TSFE->checkAlternativeIdMethods();
$TSFE->clear_preview();
$TSFE->determineId();
$TSFE->makeCacheHash();
$TSFE->getCompressedTCarray();
$TSFE->initTemplate();

//$GLOBALS['TSFE']->tmpl->setup = Array(
//	'config.' => Array(
//		'tx_extbase.' => Array(
//			'objects.' => Array(
//				'Tx_Extbase_Persistence_Storage_BackendInterface.' => Array(
//					'className' => 'Tx_Extbase_Persistence_Storage_Typo3DbBackend'
//				),
//				'Tx_Extbase_Reflection_Service.' => Array(
//					'className' => 'Tx_Palm_Reflection_Service'
//				),
//			),
//			'mvc.' => Array(
//				'requestHandlers.' => Array(
//					'Tx_Extbase_MVC_Web_FrontendRequestHandler' => 'Tx_Extbase_MVC_Web_FrontendRequestHandler'
//				)
//			)
//		)
//	)
//);

var_dump($TSFE->tmpl->setup);

$bootstrap = t3lib_div::makeInstance('Tx_Extbase_Core_Bootstrap');
$content = $bootstrap->run(
	'', Array(
		'pluginName' => 'Pi1',
		'extensionName' => 'Palm',
		'controller' => 'Demo',
		'action' => 'index',
		'switchableControllerActions.' => Array(
			'Demo' => 'index'
		)
	)
);

foreach($GLOBALS['TSFE']->additionalHeaderData as $header) {
	header($header);
}

print($content);

?>