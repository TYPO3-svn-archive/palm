<?php

interface Tx_Palm_Merger_ServiceInterface extends t3lib_Singleton {

	const FILE_LOCATION				= '#fileLocation';
	const ENTITY_NAME				= '#entityName';
	const SINGLE_PATH_IN_COLLECTION	= '#singlePathInCollection';

	const GETTER_SCOPE_PROPERTY		= 'Property';
	const GETTER_SCOPE_OBJECT		= 'Object';
	const GETTER_SCOPE_COLLECTION	= 'Collection';

}
?>