<?php

interface Tx_Palm_Merger_RuleInterface {

	const MATCH_INTERNAL_PATH = '_matchInternalPath';
	const MATCH_EXTERNAL_PATH = '_matchExternalPath';
	const ON_NOT_FOUND_IN_INTERNAL = '_onNotFoundInInternal';
	const ON_MATCH = '_onMatch';
	const ON_NOT_FOUND_IN_EXTERNAL = '_onNotFoundInExternal';
	const ACTION_KEEP = '0';
	const ACTION_TAKE_EXTERNAL = '1';
	const ACTION_DELETE = '2';

}

?>