<?php
//
// Description
// -----------
// This function will return the list of links for a business.  It is restricted
// to business owners and sysadmins.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:		The ID of the business to get links for.
//
// Returns
// -------
// <links>
// 	<link id="" name="" url="" description=""/>
// </links>
//
function ciniki_links_linkList($ciniki) {
	//
	// Find all the required and optional arguments
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
	$rc = ciniki_core_prepareArgs($ciniki, 'no', array(
		'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
		'tag_type'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Tag Type'),
		'tag_name'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Tag Name'),
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$args = $rc['args'];
	
    //  
    // Check access to business_id as owner, or sys admin. 
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'links', 'private', 'checkAccess');
    $rc = ciniki_links_checkAccess($ciniki, $args['business_id'], 'ciniki.links.linkList');
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

	//
	// Check if Uncategorized specified
	//
	if( (isset($args['tag_type']) && $args['tag_type'] != '')
		&& (isset($args['tag_name']) && $args['tag_name'] == 'Uncategorized')
		) {
		$strsql = "SELECT ciniki_links.id, "
			. "ciniki_links.name, "
			. "ciniki_links.url, "
			. "ciniki_links.description, "
			. "ciniki_link_tags.tag_name "
			. "FROM ciniki_links "
			. "LEFT JOIN ciniki_link_tags ON ("
				. "ciniki_links.id = ciniki_link_tags.link_id "
				. "AND ciniki_link_tags.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
				. "AND ciniki_link_tags.tag_type = '" . ciniki_core_dbQuote($ciniki, $args['tag_type']) . "' "
				. ") "
			. "WHERE ciniki_links.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
			. "HAVING ISNULL(ciniki_link_tags.tag_name) "
			. "ORDER BY name "
			. "";
		ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryTree');
		$rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.links', array(
			array('container'=>'links', 'fname'=>'id', 'name'=>'link',
				'fields'=>array('id', 'name', 'url', 'description')),
			));
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		if( !isset($rc['links']) ) {
			return array('stat'=>'ok', 'links'=>array());
		}
		return array('stat'=>'ok', 'links'=>$rc['links']);
	}  

	if( (isset($args['tag_type']) && $args['tag_type'] != '')
		&& (isset($args['tag_name']) && $args['tag_name'] != '')
		) {
		$strsql = "SELECT ciniki_links.id, "
			. "ciniki_links.name, "
			. "ciniki_links.url, "
			. "ciniki_links.description "
			. "FROM ciniki_link_tags "
			. "LEFT JOIN ciniki_links ON ("
				. "ciniki_link_tags.link_id = ciniki_links.id "
				. "AND ciniki_links.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
				. ") "
			. "WHERE ciniki_link_tags.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
			. "AND ciniki_link_tags.tag_type = '" . ciniki_core_dbQuote($ciniki, $args['tag_type']) . "' "
			. "AND ciniki_link_tags.tag_name = '" . ciniki_core_dbQuote($ciniki, $args['tag_name']) . "' "
			. "ORDER BY name ";
		ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryTree');
		$rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.links', array(
			array('container'=>'links', 'fname'=>'id', 'name'=>'link',
				'fields'=>array('id', 'name', 'url', 'description')),
			));
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		if( !isset($rc['links']) ) {
			return array('stat'=>'ok', 'links'=>array());
		}
		return array('stat'=>'ok', 'links'=>$rc['links']);
	}  

	$strsql = "SELECT id, name, "
		. "IF(ciniki_links.category='', 'Uncategorized', ciniki_links.category) AS sname, "
		. "url, description FROM ciniki_links "
		. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "ORDER BY sname "
		. "";
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryTree');
	$rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.links', array(
		array('container'=>'sections', 'fname'=>'sname', 'name'=>'section',
			'fields'=>array('sname')),
		array('container'=>'links', 'fname'=>'id', 'name'=>'link',
			'fields'=>array('id', 'name', 'url', 'description')),
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( !isset($rc['sections']) ) {
		return array('stat'=>'ok', 'sections'=>array());
	}

	return array('stat'=>'ok', 'sections'=>$rc['sections']);
}
?>
