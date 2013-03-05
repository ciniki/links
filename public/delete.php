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
function ciniki_links_delete(&$ciniki) {
	//
	// Find all the required and optional arguments
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
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
	ciniki_core_loadMethod($ciniki, 'ciniki', 'links', 'private', 'checkAccess');
	$ac = ciniki_links_checkAccess($ciniki, $args['business_id'], 'ciniki.links.delete');
	if( $ac['stat'] != 'ok' ) {
		return $ac;
	}

	//
	// Get the link uuid
	//
	$strsql = "SELECT uuid FROM ciniki_links "
		. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "AND id = '" . ciniki_core_dbQuote($ciniki, $args['link_id']) . "' " 
		. "";
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQuery');
	$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.links', 'link');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( !isset($rc['link']) ) {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'889', 'msg'=>'The link does not exist'));
	}
	$link_uuid = $rc['link']['uuid'];

	//
	// Start transaction
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionStart');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionRollback');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionCommit');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDelete');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbAddModuleHistory');
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

	$ciniki['syncqueue'][] = array('push'=>'ciniki.links.link',
		'args'=>array('delete_uuid'=>$link_uuid, 'delete_id'=>$args['link_id']));

	return array('stat'=>'ok');
}
?>
