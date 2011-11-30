<?php

interface Tx_Palm_Merger_RuleInterface {

	const MATCH_ON						= '#matchOn';
	const ON_EXTERNAL_PROPERTY_EMPTY	= '#onExternalPropertyEmpty';
	const ON_INTERNAL_PROPERTY_EMPTY	= '#onInternalPropertyEmpty';
	const ON_BOTH_PROPERTY_NOT_EMPTY	= '#onBothPropertyNotEmpty';
	const ON_EXTERNAL_OBJECT_EMPTY		= '#onExternalObjectEmpty';
	const ON_INTERNAL_OBJECT_EMPTY		= '#onInternalObjectEmpty';
	const ON_BOTH_OBJECT_NOT_EMPTY		= '#onBothObjectNotEmpty';
	const ON_EXTERNAL_COLLECTION_EMPTY	= '#onExternalCollectionEmpty';
	const ON_INTERNAL_COLLECTION_EMPTY	= '#onInternalCollectionEmpty';
	const ON_BOTH_COLLECTION_NOT_EMPTY	= '#onBothCollectionNotEmpty';
	const LOOKUP_REPOSITORY				= '#lookupRepository';
	const ACTION_KEEP					= 1;
	const ACTION_TAKE_EXTERNAL			= 2;
	const ACTION_DELETE					= 3;
	const ACTION_MATCH_INDIVIDUAL		= 4;
	const ACTION_LOOKUP					= 5;

}