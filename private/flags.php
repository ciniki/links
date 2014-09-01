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
//	$flags[] = array('flag'=>array('bit'=>'3', 'name'=>'Sub-Categories'));
//	$flags[] = array('flag'=>array('bit'=>'4', 'name'=>''));
	$flags[] = array('flag'=>array('bit'=>'5', 'name'=>'Notes'));

	return array('stat'=>'ok', 'flags'=>$flags);
}
?>
