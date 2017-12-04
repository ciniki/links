<?php
//
// Description
// -----------
// This method will delete a link from the tenant.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:         The ID of the tenant the link is attached to.
// link_id:         The ID of the link to be removed.
//
// Returns
// -------
// <rsp stat="ok">
//
function ciniki_links_linkDelete(&$ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'), 
        'link_id'=>array('required'=>'yes', 'default'=>'', 'blank'=>'yes', 'name'=>'Link'), 
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];
    
    //
    // Check access to tnid as owner
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'links', 'private', 'checkAccess');
    $ac = ciniki_links_checkAccess($ciniki, $args['tnid'], 'ciniki.links.linkDelete');
    if( $ac['stat'] != 'ok' ) {
        return $ac;
    }

    //
    // Get the link uuid
    //
    $strsql = "SELECT uuid FROM ciniki_links "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND id = '" . ciniki_core_dbQuote($ciniki, $args['link_id']) . "' " 
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQuery');
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.links', 'link');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['link']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.links.4', 'msg'=>'The link does not exist'));
    }
    $uuid = $rc['link']['uuid'];

    //
    // Start transaction
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionStart');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionRollback');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionCommit');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDelete');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectDelete');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbAddModuleHistory');
    $rc = ciniki_core_dbTransactionStart($ciniki, 'ciniki.links');
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

    //
    // Remove any tags
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'tagsDelete');
    $rc = ciniki_core_tagsDelete($ciniki, 'ciniki.links', 'tag', $args['tnid'],
        'ciniki_link_tags', 'ciniki_link_history', 'link_id', $args['link_id']);
    if( $rc['stat'] != 'ok' ) {
        ciniki_core_dbTransactionRollback($ciniki, 'ciniki.links');
        return $rc;
    }

    //
    // Delete the object
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectDelete');
    $rc = ciniki_core_objectDelete($ciniki, $args['tnid'], 'ciniki.links.link', $args['link_id'], $uuid, 0x04);
    if( $rc['stat'] != 'ok' ) {
        ciniki_core_dbTransactionRollback($ciniki, 'ciniki.links');
        return $rc;
    }

    //
    // Commit the transaction
    //
    $rc = ciniki_core_dbTransactionCommit($ciniki, 'ciniki.links');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Update the last_change date in the tenant modules
    // Ignore the result, as we don't want to stop user updates if this fails.
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'private', 'updateModuleChangeDate');
    ciniki_tenants_updateModuleChangeDate($ciniki, $args['tnid'], 'ciniki', 'links');

    return array('stat'=>'ok');
}
?>
