<?php
//
// Description
// ===========
// This function will update an link in the database.
//
// Arguments
// ---------
// 
// Returns
// -------
// <rsp stat='ok' id='34' />
//
function ciniki_links_linkUpdate(&$ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
        'link_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Link'), 
		'name'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Name'), 
		'category'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Category'), 
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
    // Make sure this module is activated, and
    // check permission to run this function for this business
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'links', 'private', 'checkAccess');
    $rc = ciniki_links_checkAccess($ciniki, $args['business_id'], 'ciniki.links.linkUpdate'); 
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
	// Update the object
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectUpdate');
	$rc = ciniki_core_objectUpdate($ciniki, $args['business_id'], 'ciniki.links.link', $args['link_id'], $args, 0x07);
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}

	//
	// Add the categories if enabled and specified
	//
	if( ($modules['ciniki.links']['flags']&0x01) > 0 && isset($args['categories']) ) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'tagsUpdate');
		$rc = ciniki_core_tagsUpdate($ciniki, 'ciniki.links', 'tag', $args['business_id'],
			'ciniki_link_tags', 'ciniki_link_history',
			'link_id', $args['link_id'], 10, $args['categories']);
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
			'link_id', $args['link_id'], 40, $args['tags']);
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
		'args'=>array('id'=>$args['link_id']));

	return array('stat'=>'ok');
}
?>
