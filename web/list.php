<?php
//
// Description
// -----------
// This function will return a list of links for the website.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:		The ID of the business to get events for.
//
// Returns
// -------
// <events>
// 	<event id="" name="" />
// </events>
//
function ciniki_links_web_list($ciniki, $business_id, $args) {

	if( !isset($args['tag_type']) 
		|| ($args['tag_type'] != '40' && $args['tag_type'] != '10')
		) {
		return array('stat'=>'404', 'err'=>array('pkg'=>'ciniki', 'code'=>'999', 'msg'=>'Category does not exist'));
	}
	$strsql = "SELECT ciniki_links.id, "
		. "ciniki_links.name, "
		. "ciniki_links.url, "
		. "ciniki_links.description, ";
	if( $args['tag_permalink'] == '' ) {
		$strsql .= "ciniki_link_tags.tag_name AS sname ";
	} else {
		$strsql .= "'' AS sname ";
	}
	$strsql .= "FROM ciniki_link_tags "
		. "LEFT JOIN ciniki_links ON ("
			. "ciniki_link_tags.link_id = ciniki_links.id "
			. "AND ciniki_links.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
			. ") "
		. "WHERE ciniki_link_tags.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
		. "AND ciniki_link_tags.tag_type = '" . ciniki_core_dbQuote($ciniki, $args['tag_type']) . "' "
		. "";
	if( $args['tag_permalink'] != '' ) {
		$strsql .= "AND ciniki_link_tags.permalink = '" . ciniki_core_dbQuote($ciniki, $args['tag_permalink']) . "' ";
	}
	$strsql .= "ORDER BY ciniki_link_tags.tag_name, name ASC ";

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryIDTree');
	return ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.links', array(
		array('container'=>'sections', 'fname'=>'sname',
			'fields'=>array('name'=>'sname')),
		array('container'=>'links', 'fname'=>'id', 'name'=>'link',
			'fields'=>array('id', 'name', 'url', 'description')),
		));
}
?>
