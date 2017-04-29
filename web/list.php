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
// business_id:     The ID of the business to get events for.
//
// Returns
// -------
// <events>
//  <event id="" name="" />
// </events>
//
function ciniki_links_web_list($ciniki, $business_id, $args) {

    if( !isset($args['tag_type']) 
        || ($args['tag_type'] != '40' && $args['tag_type'] != '10')
        ) {
        return array('stat'=>'404', 'err'=>array('code'=>'ciniki.links.7', 'msg'=>'Category does not exist'));
    }
    
    if( isset($ciniki['business']['modules']['ciniki.links']['flags']) && ($ciniki['business']['modules']['ciniki.links']['flags']&0x01) > 0 ) {
        $strsql = "SELECT ciniki_links.id, "
            . "ciniki_links.name, "
            . "ciniki_links.url, "
            . "ciniki_links.description, "
            . "IFNULL(ciniki_link_tags.tag_name, 'Other') AS sname "
            . "FROM ciniki_link_tags "
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
    } else {
        $strsql = "SELECT ciniki_links.id, "
            . "ciniki_links.name, "
            . "ciniki_links.url, "
            . "ciniki_links.description, "
            . "'' AS sname "
            . "FROM ciniki_links "
            . "WHERE ciniki_links.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
            . "ORDER BY name ASC ";
    }

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryIDTree');
    $rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.links', array(
        array('container'=>'categories', 'fname'=>'sname', 'fields'=>array('name'=>'sname')),
        array('container'=>'list', 'fname'=>'id', 'name'=>'link', 'fields'=>array('id', 'name', 'title'=>'name', 'url', 'description')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $rsp = $rc;

    //
    // If categories are enabled, check for any uncategorized links
    //
    if( isset($ciniki['business']['modules']['ciniki.links']['flags']) && ($ciniki['business']['modules']['ciniki.links']['flags']&0x01) > 0 ) {
        $strsql = "SELECT ciniki_links.id, "
            . "ciniki_links.name, "
            . "ciniki_links.url, "
            . "ciniki_links.description, "
            . "ciniki_link_tags.tag_name AS sname "
            . "FROM ciniki_links "
            . "LEFT JOIN ciniki_link_tags ON ("
                . "ciniki_links.id = ciniki_link_tags.link_id "
                . "AND ciniki_link_tags.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
                . ") "
            . "WHERE ciniki_links.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
            . "HAVING ISNULL(ciniki_link_tags.tag_name) "
            . "ORDER BY ciniki_links.name "
            . "";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryIDTree');
        $rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.links', array(
            array('container'=>'links', 'fname'=>'id', 'name'=>'link', 'fields'=>array('id', 'name', 'url', 'description')),
            ));
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['links']) ) {
            $rsp['sections']['Other'] = array('name'=>'Other', 'links'=>$rc['links']);
        }
    }

    return $rsp;
}
?>
