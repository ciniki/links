<?php
//
// Description
// -----------
// This method will return a list of entries from a field.  Typically this is used for live search.
//
// Arguments
// ---------
// user_id: 		The user making the request
// search_str:		The search string provided by the user.
// 
// Returns
// -------
//
function ciniki_links_searchField($ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'errmsg'=>'No business specified'), 
		'field'=>array('required'=>'yes', 'blank'=>'no', 'errmsg'=>'No field specified'),
        'start_needle'=>array('required'=>'yes', 'blank'=>'yes', 'errmsg'=>'No search specified'), 
        'limit'=>array('required'=>'yes', 'blank'=>'no', 'errmsg'=>'No limit specified'), 
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
    $rc = ciniki_links_checkAccess($ciniki, $args['business_id'], 'ciniki.links.searchField', 0); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

	//
	// Reject if an unknown field
	//
	if( $args['field'] != 'category'
		) {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'632', 'msg'=>'Invalid search field'));
	}
	//
	// Get the number of faqs in each status for the business, 
	// if no rows found, then return empty array
	//
	$args['field'] = preg_replace('/^.*\./', '', $args['field']);
	$strsql = "SELECT " . $args['field'] . " AS name "
		. "FROM ciniki_links "
		. "WHERE ciniki_links.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "AND (" . $args['field']  . " LIKE '" . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
			. "AND " . $args['field'] . " <> '' "
			. ") "
		. "";
	$strsql .= "ORDER BY " . $args['field'] . " COLLATE latin1_general_cs "
		. "";
	if( isset($args['limit']) && $args['limit'] != '' && $args['limit'] > 0 ) {
		$strsql .= "LIMIT " . ciniki_core_dbQuote($ciniki, $args['limit']) . " ";
	} else {
		$strsql .= "LIMIT 25 ";
	}
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryTree');
	$rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.links', array(
		array('container'=>'results', 'fname'=>'name', 'name'=>'result', 'fields'=>array('name')),
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( !isset($rc['results']) || !is_array($rc['results']) ) {
		return array('stat'=>'ok', 'results'=>array());
	}
	return array('stat'=>'ok', 'results'=>$rc['results']);
}
?>
