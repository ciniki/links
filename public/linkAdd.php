<?php
//
// Description
// -----------
// This method will add a new link to a business.  
//
// Arguments
// ---------
// api_key:
// auth_token:
//
// Returns
// -------
// <rsp stat="ok">
//
function ciniki_links_linkAdd(&$ciniki) {
	//
	// Find all the required and optional arguments
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
	$rc = ciniki_core_prepareArgs($ciniki, 'no', array(
		'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
		'name'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Name'), 
		'category'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'', 'name'=>'Category'), 
		'url'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'URL'), 
		'description'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Description'), 
		'categories'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'list', 'delimiter'=>'::', 'name'=>'Categories'), 
		'tags'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'list', 'delimiter'=>'::', 'name'=>'Tags'), 
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$args = $rc['args'];
	
	//
	// Check access to business_id as owner
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'links', 'private', 'checkAccess');
	$rc = ciniki_links_checkAccess($ciniki, $args['business_id'], 'ciniki.links.linkAdd');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$modules = $rc['modules'];

	//  
	// Turn off autocommit
	//  
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionStart');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionRollback');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionCommit');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQuote');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbInsert');
	$rc = ciniki_core_dbTransactionStart($ciniki, 'ciniki.links');
	if( $rc['stat'] != 'ok' ) { 
		return $rc;
	}   

	//
	// Add the object
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectAdd');
	$rc = ciniki_core_objectAdd($ciniki, $args['business_id'], 'ciniki.links.link', $args, 0x04);
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$link_id = $rc['id'];

	//
	// Add the categories if enabled and specified
	//
	if( ($modules['ciniki.links']['flags']&0x01) > 0 && isset($args['categories']) ) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'tagsUpdate');
		$rc = ciniki_core_tagsUpdate($ciniki, 'ciniki.links', 'tag', $args['business_id'],
			'ciniki_link_tags', 'ciniki_link_history',
			'link_id', $link_id, 10, $args['categories']);
		if( $rc['stat'] != 'ok' ) {
			ciniki_core_dbTransactionRollback($ciniki, 'ciniki.links');
			return $rc;
		}
	}

	//
	// Add the tags if enabled and specified
	//
	if( ($modules['ciniki.links']['flags']&0x02) > 0 && isset($args['tags']) ) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'tagsUpdate');
		$rc = ciniki_core_tagsUpdate($ciniki, 'ciniki.links', 'tag', $args['business_id'],
			'ciniki_link_tags', 'ciniki_link_history',
			'link_id', $link_id, 40, $args['tags']);
		if( $rc['stat'] != 'ok' ) {
			ciniki_core_dbTransactionRollback($ciniki, 'ciniki.links');
			return $rc;
		}
	}

	//
	// Commit the database changes
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
		'args'=>array('id'=>$link_id));

	return array('stat'=>'ok', 'id'=>$link_id);
}
?>
