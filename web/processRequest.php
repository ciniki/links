<?php
//
// Description
// -----------
// This function will generate the links page for the website
//
// Arguments
// ---------
// ciniki:
// settings:        The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_links_web_processRequest(&$ciniki, $settings, $tnid, $args) {

    //
    // Check to make sure the module is enabled
    //
    if( !isset($ciniki['tenant']['modules']['ciniki.links']) ) {
        return array('stat'=>'404', 'err'=>array('code'=>'ciniki.links.10', 'msg'=>"I'm sorry, the page you requested does not exist."));
    }
    $page = array(
        'title'=>$args['page_title'],
        'breadcrumbs'=>$args['breadcrumbs'],
        'article-class'=>'ciniki-links',
        'blocks'=>array(),
        );
    if( !isset($args['breadcrumbs']) || count($args['breadcrumbs']) <= 1 ) {
        $page['submenu'] = array();
    }

    $tag_type = 0;
    $tag_permalink = '';
    $base_url = $args['base_url'];

    $show_intro = 'no';
    $show_tags = 'no';
    $show_list = 'yes';

    //
    // Get the list of links for a category
    //
    if( isset($args['uri_split'][0]) && $args['uri_split'][0] == 'category' 
        && isset($args['uri_split'][1]) && $args['uri_split'][1] != '' 
        ) {
        $tag_type = 10;
        $tag_permalink = $args['uri_split'][1];
        $article_title = "<a href='$base_url/categories'>$article_title</a>";
        $show_tags = 'no';
        $show_list = 'yes';
    }

    //
    // Get the list of links for a tag
    //
    elseif( isset($args['uri_split'][0]) && $args['uri_split'][0] == 'tag' 
        && isset($args['uri_split'][1]) && $args['uri_split'][1] != '' 
        ) {
        $tag_type = 40;
        $tag_permalink = $args['uri_split'][1];
        $article_title = "<a href='$base_url/tags'>$article_title</a>";
        $show_tags = 'no';
        $show_list = 'yes';
    } 

    //
    // Get the stats for the number links, categories and tags
    //
    $stats = array('links'=>0, 'categories'=>0, 'tags'=>0);
    if( $tag_type == 0 ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'links', 'web', 'count');
        $rc = ciniki_links_web_count($ciniki, $ciniki['request']['tnid']);
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $stats = $rc;
    }

    //
    // Check if categories list was requested
    //
    if( $tag_type == 0 && isset($args['uri_split'][0]) && $args['uri_split'][0] == 'categories' ) {
        $tag_type = 10;
        $tag_permalink = '';
        if( $stats['categories'] > 1
            && (($stats['links'] > 20 && $stats['categories'] > 5)
                || ($stats['links'] > 30 && $stats['categories'] > 4)
                || ($stats['links'] > 40 && $stats['categories'] > 3)
                || ($stats['links'] > 50 && $stats['categories'] > 2)
                )
            ) {
            $show_tags = 'yes';
            $show_list = 'no';
        } else {
            $show_tags = 'no';
            $show_list = 'yes';
        }
    } 

    //
    // Check if tags list was requested
    //
    if( $tag_type == 0 && isset($args['uri_split'][0]) && $args['uri_split'][0] == 'tags' ) {
        $tag_type = 40;
        $tag_permalink = '';
        if( $stats['tags'] > 1
            && (($stats['links'] > 20 && $stats['tags'] > 5)
                || ($stats['links'] > 30 && $stats['tags'] > 4)
                || ($stats['links'] > 40 && $stats['tags'] > 3)
                || ($stats['links'] > 50 && $stats['tags'] > 2)
                )
            ) {
            $show_tags = 'yes';
            $show_list = 'no';
        } else {
            $show_tags = 'no';
            $show_list = 'yes';
        }
    }
    //
    // If nothing requested, decide what should be displayed
    //
    if( $tag_type == 0 ) {
        if( isset($ciniki['tenant']['modules']['ciniki.links']['flags'])
            && ($ciniki['tenant']['modules']['ciniki.links']['flags']&0x01) > 0 
            && $stats['categories'] > 1
            && (($stats['links'] > 20 && $stats['categories'] > 5)
                || ($stats['links'] > 30 && $stats['categories'] > 4)
                || ($stats['links'] > 40 && $stats['categories'] > 3)
                || ($stats['links'] > 50 && $stats['categories'] > 2)
                )
            ) {
            $tag_type = 10;
            $tag_permalink = '';
            $show_tags = 'yes';
            $show_list = 'no';
        }
        elseif( isset($ciniki['tenant']['modules']['ciniki.links']['flags'])
            && ($ciniki['tenant']['modules']['ciniki.links']['flags']&0x02) > 0
            && $stats['tags'] > 1
            && (($stats['links'] > 20 && $stats['tags'] > 5)
                || ($stats['links'] > 30 && $stats['tags'] > 4)
                || ($stats['links'] > 40 && $stats['tags'] > 3)
                || ($stats['links'] > 50 && $stats['tags'] > 2)
                )
            ) {
            $tag_type = 40;
            $tag_permalink = '';
            $show_tags = 'yes';
            $show_list = 'no';
        } 
    }

    if( $tag_type == 0 ) {
        $show_tags = 'no';
        $show_list = 'yes';
        if( isset($ciniki['tenant']['modules']['ciniki.links']['flags'])
            && ($ciniki['tenant']['modules']['ciniki.links']['flags']&0x03) == 2 ) {
            // Get the links organized by tag
            $tag_type = '40';
            $tag_permalink = '';
        } else {
            // Default to category list
            $tag_type = '10';
            $tag_permalink = '';
            $base_url .= '/category';
        }
    }

    //
    // Display the introduction content to the links page
    // FIXME: Not yet enabled in UI
    //
    if( $show_intro == 'yes' ) {
        //
        // Generate the content of the page
        //
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDetailsQueryDash');
        $rc = ciniki_core_dbDetailsQueryDash($ciniki, 'ciniki_web_content', 'tnid', $ciniki['request']['tnid'], 'ciniki.web', 'content', 'page-links');
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }

        if( isset($rc['content']) && isset($rc['content']['page-links-content']) ) {
            $page['blocks'][] = array('type'=>'content', 'content'=>$rc['content']['page-links-content']);
        }
    }

    //
    // Display the tag cloud/list
    //
    if( $show_tags == 'yes' ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'links', 'web', 'tagCloud');
        $rc = ciniki_links_web_tagCloud($ciniki, $settings, $ciniki['request']['tnid'], array(
            'tag_type'=>$tag_type,
            'permalink'=>$tag_permalink,
            ));
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( $tag_type == 40 ) {
            $base_url .= '/tag';
        } else {
            $base_url .= '/category';
        }
        if( ($tag_type == 40 && isset($settings['page-links-tags-format']) && $settings['page-links-tags-format'] == 'wordlist') 
            || ($tag_type == 10 && isset($settings['page-links-categories-format']) && $settings['page-links-categories-format'] == 'wordlist')
            ) {
            $page['blocks'][] = array('type'=>'taglist', 'base_url'=>$base_url, 'tags'=>$rc['tags']);
        } else {
            $page['blocks'][] = array('type'=>'tagcloud', 'base_url'=>$base_url, 'tags'=>$rc['tags']);
        }
    }

    if( $show_list == 'yes' ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'links', 'web', 'list');
        $rc = ciniki_links_web_list($ciniki, $ciniki['request']['tnid'], array('tag_type'=>$tag_type, 'tag_permalink'=>$tag_permalink));
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $sections = isset($rc['categories'])?$rc['categories']:array();
        if( $tag_permalink != '' ) {
            $skeys = array_keys($sections);
            $section_name = $skeys[0];
            $sections[$section_name]['name'] = '';
            if( $section_name != '' ) {
                $article_title .= ' - ' . $section_name;
                $page_title .= ' - ' . $section_name;
            } 
        }
        if( count($sections) > 0 ) {
            $page['blocks'][] = array('type'=>'cilist', 'categories'=>$sections);
        } else {
            $page['blocks'][] = array('type'=>'content', 'section'=>'content', 'content'=>"I'm sorry, there are no links.");
        }
    }

    
    if( isset($page['submenu']) && ciniki_core_checkModuleFlags($ciniki, 'ciniki.links', 0x03) ) {
        $page['submenu'] = array();
        // Display the category/tags buttons
        $page['submenu']['categories'] = array('name'=>'Categories', 'url'=>$ciniki['request']['base_url'] . '/links/categories');
        $page['submenu']['tags'] = array('name'=>'Tags', 'url'=>$ciniki['request']['base_url'] . '/links/tags');
    } 

    return array('stat'=>'ok', 'page'=>$page);
}
?>
