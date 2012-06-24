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
function ciniki_links_getHistory($ciniki) {
	//
	// Find all the required and optional arguments
	//
	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/prepareArgs.php');
	$rc = ciniki_core_prepareArgs($ciniki, 'no', array(
		'business_id'=>array('required'=>'yes', 'blank'=>'no', 'errmsg'=>'No business specified'), 
		'link_id'=>array('required'=>'yes', 'blank'=>'no', 'errmsg'=>'No item specified'), 
		'field'=>array('required'=>'yes', 'blank'=>'no', 'errmsg'=>'No field specified'), 
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$args = $rc['args'];
	
	//
	// Check access to business_id as owner, or sys admin
	//
	require_once($ciniki['config']['core']['modules_dir'] . '/links/private/checkAccess.php');
	$rc = ciniki_links_checkAccess($ciniki, $args['business_id'], 'ciniki.links.getHistory');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}

	if( $args['field'] == 'start_date' ) {
		require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbGetModuleHistoryReformat.php');
		return ciniki_core_dbGetModuleHistoryReformat($ciniki, 'links', 'ciniki_link_history', $args['business_id'], 'ciniki_links', $args['link_id'], $args['field'], 'links','date');
	}
	if( $args['field'] == 'end_date' ) {
		require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbGetModuleHistoryReformat.php');
		return ciniki_core_dbGetModuleHistoryReformat($ciniki, 'links', 'ciniki_link_history', $args['business_id'], 'ciniki_links', $args['link_id'], $args['field'], 'links','date');
	}

	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbGetModuleHistory.php');
	return ciniki_core_dbGetModuleHistory($ciniki, 'links', 'ciniki_link_history', $args['business_id'], 'ciniki_links', $args['link_id'], $args['field']);
}
?>
