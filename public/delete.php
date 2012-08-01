<?php
//
// Description
// -----------
// This method will delete a link from the business.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:			The ID of the business the link is attached to.
// link_id:			The ID of the link to be removed.
//
// Returns
// -------
// <rsp stat="ok">
//
function ciniki_links_delete($ciniki) {
	//
	// Find all the required and optional arguments
	//
	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/prepareArgs.php');
	$rc = ciniki_core_prepareArgs($ciniki, 'no', array(
		'business_id'=>array('required'=>'yes', 'blank'=>'no', 'errmsg'=>'No business specified'), 
		'link_id'=>array('required'=>'yes', 'default'=>'', 'blank'=>'yes', 'errmsg'=>'No link specified'), 
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$args = $rc['args'];
	
	//
	// Check access to business_id as owner
	//
	require_once($ciniki['config']['core']['modules_dir'] . '/links/private/checkAccess.php');
	$ac = ciniki_links_checkAccess($ciniki, $args['business_id'], 'ciniki.links.delete');
	if( $ac['stat'] != 'ok' ) {
		return $ac;
	}

	//
	// Start transaction
	//
	require($ciniki['config']['core']['modules_dir'] . '/core/private/dbTransactionStart.php');
	require($ciniki['config']['core']['modules_dir'] . '/core/private/dbTransactionRollback.php');
	require($ciniki['config']['core']['modules_dir'] . '/core/private/dbTransactionCommit.php');
	require($ciniki['config']['core']['modules_dir'] . '/core/private/dbDelete.php');
	require($ciniki['config']['core']['modules_dir'] . '/core/private/dbAddModuleHistory.php');
	$rc = ciniki_core_dbTransactionStart($ciniki, 'ciniki.links');
	if( $rc['stat'] != 'ok' ) { 
		return $rc;
	}   

	//
	// remove the link
	//
	$strsql = "DELETE FROM ciniki_links "
		. "WHERE id = '" . ciniki_core_dbQuote($ciniki, $args['link_id']) . "' "
		. "AND business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' ";
	$rc = ciniki_core_dbDelete($ciniki, $strsql, 'ciniki.links');
	if( $rc['stat'] != 'ok' ) {
		ciniki_core_dbTransactionRollback($ciniki, 'ciniki.links');
		return $rc;
	}

	if( $rc['num_affected_rows'] == 0 ) {
		ciniki_core_dbTransactionRollback($ciniki, 'ciniki.links');
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'634', 'msg'=>'Unable to remove link'));
	}

	// FIXME: Add code to track deletions
	ciniki_core_dbAddModuleHistory($ciniki, 'ciniki.links', 'ciniki_link_history', $args['business_id'], 
		3, 'ciniki_links', $args['link_id'], '*', '');

	//
	// Commit the transaction
	//
	$rc = ciniki_core_dbTransactionCommit($ciniki, 'ciniki.links');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}

	//
	// Update the last_change date in the business modules
	// Ignore the result, as we don't want to stop user updates if this fails.
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'businesses', 'private', 'updateModuleChangeDate');
	ciniki_businesses_updateModuleChangeDate($ciniki, $args['business_id'], 'ciniki', 'links');

	return array('stat'=>'ok');
}
?>
