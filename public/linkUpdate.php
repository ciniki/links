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
    $rc = ciniki_links_checkAccess($ciniki, $args['business_id'], 'ciniki.links.update'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

	//
	// Update the object
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectUpdate');
	return ciniki_core_objectUpdate($ciniki, $args['business_id'], 'ciniki.links.link', $args['link_id'], $args, 0x07);
}
?>
