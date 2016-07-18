<?php
//
// Description
// -----------
// This function will return the number of links for a business.  This is used
// to determine how to display the initial list.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:     The ID of the business to get events for.
//
// Returns
// -------
//
function ciniki_links_web_count($ciniki, $business_id) {

    $links = 0;
    $rsp = array('stat'=>'ok', 'links'=>0, 'tags'=>0, 'categories'=>0);
    $strsql = "SELECT COUNT(ciniki_links.id) AS num_links "
        . "FROM ciniki_links "
        . "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
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
        . "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
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
