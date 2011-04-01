<?php
class tx_Palm_Hook_TCEForms {

	public function getMainFields_preProcess($table,$row,&$parent) {
		// TODO Check here if a record can get imported
		if(empty($parent->dynNestedStack)) {
			$moduleSignature = 'web_PalmTxPalmM1';
			if (!isset($GLOBALS['TBE_MODULES']['_configuration'][$moduleSignature])) {
				return;
			}
			$moduleConfiguration = $GLOBALS['TBE_MODULES']['_configuration'][$moduleSignature];
			try {
				$GLOBALS['BE_USER']->modAccess($moduleConfiguration, TRUE);
			} catch (RuntimeException $error) {
				return;
			}
			$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['extbase']['extensions']['Palm']['modules'][$moduleSignature]['controllers'] = array(
				'PullData' => array(
					'actions' => array(
						'testRecord'
					),
				),
			);
			$dispatcher = t3lib_div::makeInstance('Tx_Extbase_Core_Bootstrap');
			$dispatcher->callModule($moduleSignature);
//			t3lib_FlashMessageQueue::addMessage(t3lib_div::makeInstance('t3lib_FlashMessage', 'There is some Data to Import. <a href="#">Click here to start the Import</a>', 'Importer', t3lib_FlashMessage::INFO));
		}
	}
}
?>
