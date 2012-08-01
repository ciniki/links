<?php
//
// Description
// ===========
// This function will return all the details for a links.
//
// Arguments
// ---------
// user_id: 		The user making the request
// 
// Returns
// -------
//
function ciniki_links_get($ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    require_once($ciniki['config']['core']['modules_dir'] . '/core/private/prepareArgs.php');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'errmsg'=>'No business specified'), 
        'link_id'=>array('required'=>'yes', 'blank'=>'no', 'errmsg'=>'No links specified'), 
        )); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   
    $args = $rc['args'];
    
    //  
    // Make sure this module is activated, and
    // check permission to run this function for this business
    //  
    require_once($ciniki['config']['core']['modules_dir'] . '/links/private/checkAccess.php');
    $rc = ciniki_links_checkAccess($ciniki, $args['business_id'], 'ciniki.links.get'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbQuote.php');
	require_once($ciniki['config']['core']['modules_dir'] . '/users/private/dateFormat.php');
	$date_format = ciniki_users_dateFormat($ciniki);

	$strsql = "SELECT ciniki_links.id, name, category, url, description, "
		. "date_added, last_updated "
		. "FROM ciniki_links "
		. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "AND ciniki_links.id = '" . ciniki_core_dbQuote($ciniki, $args['link_id']) . "' "
		. "";
	
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQuery');
	$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.links', 'link');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( !isset($rc['link']) ) {
		return array('stat'=>'ok', 'err'=>array('pkg'=>'ciniki', 'code'=>'633', 'msg'=>'Unable to find link'));
	}

	return array('stat'=>'ok', 'link'=>$rc['link']);
}
?>
