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
function ciniki_links_linkGet($ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
        'link_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Link'), 
        'tags'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Tags'), 
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
    $rc = ciniki_links_checkAccess($ciniki, $args['business_id'], 'ciniki.links.linkGet'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }
	$modules = $rc['modules'];

	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQuote');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'dateFormat');
	$date_format = ciniki_users_dateFormat($ciniki);

	//
	// Check for new link
	//
	if( $args['link_id'] == '0' ) {
		$rsp = array('stat'=>'ok', 'link'=>array('id'=>'0', 'name'=>'', 'url'=>'', 'description'=>'', 'notes'=>''));
	} 
	
	//
	// Lookup existing list
	//
	else {
		$strsql = "SELECT ciniki_links.id, name, url, description, notes "
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
		$rsp = array('stat'=>'ok', 'link'=>$rc['link']);
	}

	//
	// Load tags if enabled
	//
	if( ($modules['ciniki.links']['flags']&0x03) > 0 ) {
		//
		// Get tags for the link
		//
		$strsql = "SELECT tag_type, tag_name AS lists "
			. "FROM ciniki_link_tags "
			. "WHERE link_id = '" . ciniki_core_dbQuote($ciniki, $args['link_id']) . "' "
			. "AND business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
			. "ORDER BY tag_type, tag_name "
			. "";
		ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryTree');
		$rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.links', array(
			array('container'=>'tags', 'fname'=>'tag_type', 'name'=>'tags',
				'fields'=>array('tag_type', 'lists'), 'dlists'=>array('lists'=>'::')),
			));
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		if( isset($rc['tags']) ) {
			foreach($rc['tags'] as $tags) {
				if( $tags['tags']['tag_type'] == 10 ) {
					$rsp['link']['categories'] = $tags['tags']['lists'];
				} elseif( $tags['tags']['tag_type'] == 40 ) {
					$rsp['link']['tags'] = $tags['tags']['lists'];
				}
			}
		}
		
		if( isset($args['tags']) && $args['tags'] == 'yes' ) {
			//
			// Get all tags
			//
			$strsql = "SELECT DISTINCT tag_type, tag_name "
				. "FROM ciniki_link_tags "
				. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
				. "ORDER BY tag_type, tag_name "
				. "";
			ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryTree');
			$rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.links', array(
				array('container'=>'types', 'fname'=>'tag_type', 'name'=>'type',
					'fields'=>array('type'=>'tag_type')),
				array('container'=>'tags', 'fname'=>'tag_name', 'name'=>'tag',
					'fields'=>array('type'=>'tag_type', 'name'=>'tag_name')),
				));
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			if( isset($rc['types']) ) {
				foreach($rc['types'] as $tid => $type) {
					if( $type['type']['type'] == 10 ) {
						$rsp['categories'] = $type['type']['tags'];
					} elseif( $type['type']['type'] == 40 ) {
						$rsp['tags'] = $type['type']['tags'];
					}
				}
			}
		}
	}

	return $rsp;
}
?>
