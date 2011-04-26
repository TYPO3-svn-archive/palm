<?php
if (TYPO3_MODE === 'BE'){
	Tx_Extbase_Utility_Extension::registerModule(
		$_EXTKEY,
		'web',
		'tx_palm_m1',
		'',
		Array ('PullData'	=> 'index,list,selectRecord,mergeRecord,mergeAllRecords,selectImportRecord,importRecord,importAllRecords'),
		Array ('access'		=> 'user, group',
				'icon'		=> 'EXT:'.$_EXTKEY.'/ext_icon.gif',
				'labels'	=> 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_mod.xml',
		)
	);
}

t3lib_extMgm::addTypoScript($extensionName, 'setup', '
config.tx_extbase {
	objects {
		Tx_Extbase_Reflection_Service {
			className = Tx_Palm_Reflection_Service
		}
		Tx_Extbase_Persistence_Mapper_DataMapper {
			className = Tx_Palm_Persistence_Mapper_DataMapper
		}
	}
}
');
?>