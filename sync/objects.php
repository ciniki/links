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
function ciniki_links_sync_objects($ciniki, &$sync, $business_id, $args) {
    
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
