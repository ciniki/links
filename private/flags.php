<?php
//
// Description
// -----------
// The module flags
//
// Arguments
// ---------
//
// Returns
// -------
//
function ciniki_links_flags($ciniki, $modules) {
	$flags = array();
	$flags[] = array('flag'=>array('bit'=>'1', 'name'=>'Categories'));
	$flags[] = array('flag'=>array('bit'=>'2', 'name'=>'Tags'));

	return array('stat'=>'ok', 'flags'=>$flags);
}
?>
