<?php
class tx_Palm_Hook_TCEForms {

	public function getMainFields_preProcess($table,$row,&$parent) {
		// TODO Check here if a record can get imported
		t3lib_FlashMessageQueue::addMessage(t3lib_div::makeInstance('t3lib_FlashMessage', 'There is some Data to Import. <a href="#">Click here to start the Import</a>', 'Importer', t3lib_FlashMessage::INFO));
	}
}
?>