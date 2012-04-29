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
function ciniki_links_web_list($ciniki, $business_id) {

	$strsql = "SELECT ciniki_links.id, category AS cname, name, url, description "
		. "FROM ciniki_links "
		. "WHERE ciniki_links.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
		. "ORDER BY category, name ASC "
		. "";

	
    require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbHashQueryTree.php');
	return ciniki_core_dbHashQueryTree($ciniki, $strsql, 'links', array(
		array('container'=>'categories', 'fname'=>'cname', 'name'=>'category',
			'fields'=>array('cname')),
		array('container'=>'links', 'fname'=>'name', 'name'=>'link',
			'fields'=>array('id', 'name', 'url', 'description')),
		));
}
?>
