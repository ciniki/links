<?php
//
// Description
// -----------
//
// Arguments
// ---------
//
// Returns
// -------
//
function ciniki_links_objects($ciniki) {
	$objects = array();
	$objects['link'] = array(
		'name'=>'Links',
		'table'=>'ciniki_links',
		'fields'=>array(
			'name'=>array(),
			'category'=>array(),
			'url'=>array(),
			'description'=>array(),
			),
		'history_table'=>'ciniki_link_history',
		);
	
	return array('stat'=>'ok', 'objects'=>$objects);
}
?>
