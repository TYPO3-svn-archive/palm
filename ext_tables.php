<?php
if (TYPO3_MODE === 'BE'){
	Tx_Extbase_Utility_Extension::registerModule(
		$_EXTKEY,
		'web',
		'tx_palm_m1',
		'',
		Array ('PullData'	=> 'index,list,selectRecord,mergeRecord'),
		Array ('access'		=> 'user, group',
				'icon'		=> 'EXT:'.$_EXTKEY.'/ext_icon.gif',
				'labels'	=> 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_mod.xml',
		)
	);
		$container = Tx_Extbase_Object_Container_Container::getContainer();
}
?>