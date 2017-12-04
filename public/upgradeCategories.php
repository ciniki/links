<?php
//
// Description
// -----------
// This function will clean up the history for links.
//
// Arguments
// ---------
//
// Returns
// -------
//
function ciniki_links_upgradeCategories($ciniki) {
    if( ($ciniki['session']['user']['perms'] & 0x01) != 0x01 ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.links.6', 'msg'=>'Unable to upgrade'));
    }

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryIDTree');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'makePermalink');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectAdd');

    //
    // Get all the existing tags, so we don't duplicate
    //
    $strsql = "SELECT tnid, link_id, tag_name "
        . "FROM ciniki_link_tags "
        . "";
    $rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.links', array(
        array('container'=>'tags', 'fname'=>'tnid', 'fields'=>array()),
        array('container'=>'links', 'fname'=>'link_id', 'fields'=>array()),
        array('container'=>'tags', 'fname'=>'tag_name', 'fields'=>array()),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['tags']) ) {
        $tags = $rc['tags'];
    } else {
        $tags = array();
    }

    //
    // Get the links
    //
    $strsql = "SELECT id, tnid, category "
        . "FROM ciniki_links "
        . "WHERE ciniki_links.category <> '' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.links', 'item');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $links = $rc['rows'];
    $count = 0;
    foreach($links as $row) {
        if( !isset($tags[$row['tnid']]['links'][$row['id']]['tags'][$row['category']]) ) {
            $rc = ciniki_core_objectAdd($ciniki, $row['tnid'], 'ciniki.links.tag', array(
                'link_id'=>$row['id'],
                'tag_type'=>10,
                'tag_name'=>$row['category'],
                'permalink'=>ciniki_core_makePermalink($ciniki, $row['category']),
                ), 0x07);
            $count++;
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
        }
    }

    return array('stat'=>'ok', 'added'=>$count);
}
?>
