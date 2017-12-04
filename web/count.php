<?php
//
// Description
// -----------
// This function will return the number of links for a tenant.  This is used
// to determine how to display the initial list.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:     The ID of the tenant to get events for.
//
// Returns
// -------
//
function ciniki_links_web_count($ciniki, $tnid) {

    $links = 0;
    $rsp = array('stat'=>'ok', 'links'=>0, 'tags'=>0, 'categories'=>0);
    $strsql = "SELECT COUNT(ciniki_links.id) AS num_links "
        . "FROM ciniki_links "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "";
    
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQuery');
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.links', 'links');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['links']['num_links']) ) {
        $rsp['links'] = $rc['links']['num_links'];
    }

    //
    // Get the number of categories
    //
    $strsql = "SELECT tag_type, COUNT(DISTINCT ciniki_link_tags.tag_name) AS num_tags "
        . "FROM ciniki_link_tags "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "GROUP BY tag_type "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.links', 'tags');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['rows']) ) {
        foreach($rc['rows'] as $row) {
            if( $row['tag_type'] == '10' ) {
                $rsp['categories'] = $row['num_tags'];
            } elseif( $row['tag_type'] == '40' ) {
                $rsp['tags'] = $row['num_tags'];
            }
        }
    }

    return $rsp;
}
?>
