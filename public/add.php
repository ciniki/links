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
function ciniki_links_add($ciniki) {
	//
	// Find all the required and optional arguments
	//
	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/prepareArgs.php');
	$rc = ciniki_core_prepareArgs($ciniki, 'no', array(
		'business_id'=>array('required'=>'yes', 'blank'=>'no', 'errmsg'=>'No business specified'), 
		'name'=>array('required'=>'yes', 'blank'=>'no', 'errmsg'=>'No name specified'), 
		'category'=>array('required'=>'no', 'blank'=>'yes', 'errmsg'=>'No name specified'), 
		'url'=>array('required'=>'no', 'blank'=>'yes', 'errmsg'=>'No url specified'), 
		'description'=>array('required'=>'no', 'blank'=>'yes', 'errmsg'=>'No description specified'), 
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$args = $rc['args'];
	
	//
	// Check access to business_id as owner
	//
	require_once($ciniki['config']['core']['modules_dir'] . '/links/private/checkAccess.php');
	$ac = ciniki_links_checkAccess($ciniki, $args['business_id'], 'ciniki.links.add');
	if( $ac['stat'] != 'ok' ) {
		return $ac;
	}

	//
	// Start transaction
	//
	require($ciniki['config']['core']['modules_dir'] . '/core/private/dbTransactionStart.php');
	require($ciniki['config']['core']['modules_dir'] . '/core/private/dbTransactionRollback.php');
	require($ciniki['config']['core']['modules_dir'] . '/core/private/dbTransactionCommit.php');
	require($ciniki['config']['core']['modules_dir'] . '/core/private/dbInsert.php');
	require($ciniki['config']['core']['modules_dir'] . '/core/private/dbAddModuleHistory.php');
	$rc = ciniki_core_dbTransactionStart($ciniki, 'links');
	if( $rc['stat'] != 'ok' ) { 
		return $rc;
	}   

	//
	// Add site to web_sites table
	// FIXME: Add ability to set modules when site is added, right now default to most apps on
	//
	$strsql = "INSERT INTO ciniki_links (uuid, business_id, "
		. "name, category, url, description, "
		. "date_added, last_updated ) VALUES ( "
		. "UUID(), "
		. "'" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "', "
		. "'" . ciniki_core_dbQuote($ciniki, $args['name']) . "', "
		. "'" . ciniki_core_dbQuote($ciniki, $args['category']) . "', "
		. "'" . ciniki_core_dbQuote($ciniki, $args['url']) . "', "
		. "'" . ciniki_core_dbQuote($ciniki, $args['description']) . "', "
		. "UTC_TIMESTAMP(), UTC_TIMESTAMP())";
	$rc = ciniki_core_dbInsert($ciniki, $strsql, 'links');
	if( $rc['stat'] != 'ok' ) {
		ciniki_core_dbTransactionRollback($ciniki, 'links');
		return $rc;
	}
	if( !isset($rc['insert_id']) || $rc['insert_id'] < 1 ) {
		ciniki_core_dbTransactionRollback($ciniki, 'links');
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'631', 'msg'=>'Unable to add link'));
	}
	$link_id = $rc['insert_id'];

	//
	// Add all the fields to the change log
	//
	$changelog_fields = array(
		'name',
		'category',
		'url',
		'description',
		);
	foreach($changelog_fields as $field) {
		if( isset($args[$field]) && $args[$field] != '' ) {
			$rc = ciniki_core_dbAddModuleHistory($ciniki, 'links', 'ciniki_link_history', $args['business_id'], 
				1, 'ciniki_links', $link_id, $field, $args[$field]);
		}
	}

	//
	// Commit the transaction
	//
	$rc = ciniki_core_dbTransactionCommit($ciniki, 'links');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}

	return array('stat'=>'ok', 'id'=>$link_id);
}
?>
