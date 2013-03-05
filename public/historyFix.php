<?php
//
// Description
// -----------
// This function will clean up the history for links.
//
// Arguments
// ---------
//
// Returns
// -------
//
function ciniki_links_historyFix($ciniki) {
	//
	// Find all the required and optional arguments
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
	$rc = ciniki_core_prepareArgs($ciniki, 'no', array(
		'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$args = $rc['args'];
	
	//
	// Check access to business_id as owner, or sys admin
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'links', 'private', 'checkAccess');
	$rc = ciniki_links_checkAccess($ciniki, $args['business_id'], 'ciniki.links.historyFix', 0);
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}

	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQuote');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbUpdate');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDelete');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbFixTableHistory');

	//
	// Update the history for ciniki_links
	//
	$rc = ciniki_core_dbFixTableHistory($ciniki, 'ciniki.links', $args['business_id'],
		'ciniki_links', 'ciniki_link_history', 
		array('uuid', 'name', 'category', 'url', 'description'));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}

	//
	// Check for items missing a UUID
	//
	$strsql = "UPDATE ciniki_link_history SET uuid = UUID() WHERE uuid = ''";
	$rc = ciniki_core_dbUpdate($ciniki, $strsql, 'ciniki.links');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}

	//
	// Remote any entries with blank table_key, they are useless we don't know what they were attached to
	//
	$strsql = "DELETE FROM ciniki_link_history WHERE table_key = ''";
	$rc = ciniki_core_dbDelete($ciniki, $strsql, 'ciniki.links');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}

	return array('stat'=>'ok');
}
?>
