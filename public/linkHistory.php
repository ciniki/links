<?php
//
// Description
// -----------
// This function will get the history of a field from the ciniki_core_change_logs table.
// This allows the user to view what has happened to a data element, and if they
// choose, revert to a previous version.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:			The ID of the business to get the details for.
// key:					The detail key to get the history for.
//
// Returns
// -------
//
function ciniki_links_linkHistory($ciniki) {
	//
	// Find all the required and optional arguments
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
	$rc = ciniki_core_prepareArgs($ciniki, 'no', array(
		'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
		'link_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'List'), 
		'field'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Field'), 
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$args = $rc['args'];
	
	//
	// Check access to business_id as owner, or sys admin
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'links', 'private', 'checkAccess');
	$rc = ciniki_links_checkAccess($ciniki, $args['business_id'], 'ciniki.links.getHistory');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}

	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbGetModuleHistory');
	return ciniki_core_dbGetModuleHistory($ciniki, 'ciniki.links', 'ciniki_link_history', $args['business_id'], 'ciniki_links', $args['link_id'], $args['field']);
}
?>