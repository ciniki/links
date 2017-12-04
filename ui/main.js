//
// This app will handle the listing, additions and deletions of links.
//
function ciniki_links_main() {
    //
    // Panels
    //
    this.toggleOptions = {'off':'Off', 'on':'On'};

    this.init = function() {
        //
        // links panel
        //
        this.menu = new M.panel('Links',
            'ciniki_links_main', 'menu',
            'mc', 'medium', 'sectioned', 'ciniki.links.main.menu');
        this.menu.sections = {
            '_':{'label':'', 'type':'simplegrid', 'num_cols':1,
                'headerValues':null,
                'cellClasses':[''],
                'addTxt':'Add Link',
                'addFn':'M.ciniki_links_main.showEdit(\'M.ciniki_links_main.showMenu();\',0);',
                'noData':'No links added',
                },
            };
        this.menu.sectionData = function(s) { return this.data[s]; }
        this.menu.cellValue = function(s, i, j, d) { return d.link.name; }
        this.menu.rowFn = function(s, i, d) { return 'M.ciniki_links_main.showEdit(\'M.ciniki_links_main.showMenu();\',' + d.link.id + ');' }
        this.menu.noData = function(s) { return this.sections[s].noData; }
        this.menu.addButton('add', 'Add', 'M.ciniki_links_main.showEdit(\'M.ciniki_links_main.showMenu();\',0);');
        this.menu.addClose('Back');

        //
        // tags or category list panel
        //
        this.tags = new M.panel('Links',
            'ciniki_links_main', 'tags',
            'mc', 'medium', 'sectioned', 'ciniki.links.main.tags');
        this.tags.tag_type = 40;
        this.tags.sections = {
            'types':{'label':'', 'visible':'no', 'selected':'categories', 'type':'paneltabs', 'tabs':{
                'categories':{'label':'Categories', 'fn':'M.ciniki_links_main.showTagsTab(10);'},
                'tags':{'label':'Tags', 'fn':'M.ciniki_links_main.showTagsTab(40);'},
                }},
            'categories':{'label':'', 'type':'simplegrid', 'num_cols':1,
                'headerValues':null,
                'cellClasses':[''],
                'addTxt':'Add Link',
                'addFn':'M.ciniki_links_main.showEdit(\'M.ciniki_links_main.showTags();\',0);',
                'noData':'No links added',
                },
            'tags':{'label':'', 'type':'simplegrid', 'num_cols':1,
                'headerValues':null,
                'cellClasses':[''],
                'addTxt':'Add Link',
                'addFn':'M.ciniki_links_main.showEdit(\'M.ciniki_links_main.showTags();\',0);',
                'noData':'No links added',
                },
            };
        this.tags.sectionData = function(s) { return this.data[s]; }
        this.tags.cellValue = function(s, i, j, d) { 
            return d.tag.name + (d.tag.count!=null?' <span class="count">'+d.tag.count+'</span>':''); 
        }
        this.tags.rowFn = function(s, i, d) { return 'M.ciniki_links_main.showList(\'M.ciniki_links_main.showTags();\',M.ciniki_links_main.tags.tag_type,\'' + escape(d.tag.name) + '\');' }
        this.tags.noData = function(s) { return this.sections[s].noData; }
        this.tags.addButton('add', 'Add', 'M.ciniki_links_main.showEdit(\'M.ciniki_links_main.showTags();\',0);');
        this.tags.addClose('Back');

        //
        // tags or category list panel
        //
        this.list = new M.panel('Links',
            'ciniki_links_main', 'list',
            'mc', 'medium', 'sectioned', 'ciniki.links.main.list');
        this.list.tag_type = 40;
        this.list.tag_name = '';
        this.list.sections = {
            'links':{'label':'', 'type':'simplegrid', 'num_cols':1,
                'headerValues':null,
                'cellClasses':['multiline'],
                'addTxt':'Add Link',
                'addFn':'M.ciniki_links_main.showEdit(\'M.ciniki_links_main.showList();\',0,M.ciniki_links_main.list.tag_type,escape(M.ciniki_links_main.list.tag_name));',
                'noData':'No links added',
                },
            };
        this.list.sectionData = function(s) { return this.data[s]; }
        this.list.cellValue = function(s, i, j, d) { 
            return '<span class="maintext">' + d.link.name + '</span><span class="subtext">' + d.link.url + '</span>'; 
        }
        this.list.rowFn = function(s, i, d) { return 'M.ciniki_links_main.showEdit(\'M.ciniki_links_main.showList();\',' + d.link.id + ');' }
        this.list.noData = function(s) { return this.sections[s].noData; }
        this.list.addButton('add', 'Add', 'M.ciniki_links_main.showEdit(\'M.ciniki_links_main.showList();\',0,M.ciniki_links_main.list.tag_type,M.ciniki_links_main.list.tag_name);');
        this.list.addClose('Back');

        //
        // The edit link panel 
        //
        this.edit = new M.panel('Link',
            'ciniki_links_main', 'edit',
            'mc', 'medium', 'sectioned', 'ciniki.links.main.edit');
        this.edit.data = null;
        this.edit.link_id = 0;
        this.edit.sections = { 
            'general':{'label':'General', 'fields':{
                'name':{'label':'Name', 'hint':'Company or links name', 'type':'text'},
//                'category':{'label':'Category', 'type':'text', 'livesearch':'yes', 'livesearchempty':'yes'},
                'url':{'label':'URL', 'hint':'Enter the http:// address for your links website', 'type':'text'},
                }}, 
            '_categories':{'label':'Categories', 'active':'no', 'fields':{
                'categories':{'label':'', 'hidelabel':'yes', 'type':'tags', 'tags':[], 'hint':'Enter a new category:'},
                }},
            '_tags':{'label':'Tags', 'active':'no', 'fields':{
                'tags':{'label':'', 'hidelabel':'yes', 'type':'tags', 'tags':[], 'hint':'Enter a new tag:'},
                }},
            '_description':{'label':'Additional Information', 'fields':{
                'description':{'label':'', 'hidelabel':'yes', 'hint':'Add additional information about your link', 'type':'textarea'},
                }},
            '_notes':{'label':'Notes', 'fields':{
                'notes':{'label':'', 'hidelabel':'yes', 'hint':'Other notes about this link', 'type':'textarea'},
                }},
            '_buttons':{'label':'', 'buttons':{
                'save':{'label':'Save', 'fn':'M.ciniki_links_main.saveLink();'},
                'delete':{'label':'Delete', 'visible':'no', 'fn':'M.ciniki_links_main.deleteLink();'},
                }},
            };  
        this.edit.fieldValue = function(s, i, d) { return this.data[i]; }
//      this.edit.liveSearchCb = function(s, i, value) {
//          if( i == 'category' ) {
//              var rsp = M.api.getJSONBgCb('ciniki.links.linkSearchField', {'tnid':M.curTenantID, 'field':i, 'start_needle':value, 'limit':15},
//                  function(rsp) {
//                      M.ciniki_links_main.edit.liveSearchShow(s, i, M.gE(M.ciniki_links_main.edit.panelUID + '_' + i), rsp.results);
//                  });
//          }
//      };
//      this.edit.liveSearchResultValue = function(s, f, i, j, d) {
//          if( (f == 'category' ) && d.result != null ) { return d.result.name; }
//          return '';
//      };
//      this.edit.liveSearchResultRowFn = function(s, f, i, j, d) { 
//          if( (f == 'category' )
//              && d.result != null ) {
//              return 'M.ciniki_links_main.edit.updateField(\'' + s + '\',\'' + f + '\',\'' + escape(d.result.name) + '\');';
//          }
//      };
//      this.edit.updateField = function(s, lid, result) {
//          M.gE(this.panelUID + '_' + lid).value = unescape(result);
//          this.removeLiveSearch(s, lid);
//      };
        this.edit.fieldHistoryArgs = function(s, i) {
            return {'method':'ciniki.links.linkHistory', 'args':{'tnid':M.curTenantID, 'link_id':this.link_id, 'field':i}};
        }
        this.edit.addButton('save', 'Save', 'M.ciniki_links_main.saveLink();');
        this.edit.addClose('Cancel');

    };

    //
    // Arguments:
    // aG - The arguments to be parsed into args
    //
    this.start = function(cb, appPrefix, aG) {
        args = {};
        if( aG != null ) {
            args = eval(aG);
        }

        //
        // Create the app container if it doesn't exist, and clear it out
        // if it does exist.
        //
        var appContainer = M.createContainer(appPrefix, 'ciniki_links_main', 'yes');
        if( appContainer == null ) {
            alert('App Error');
            return false;
        } 

        if( (M.curTenant.modules['ciniki.links'].flags&0x01) > 0 ) {
            this.edit.sections._categories.active = 'yes';
//          this.edit.sections.general.fields.category.active = 'no';
        } else {
            this.edit.sections._categories.active = 'no';
//          this.edit.sections.general.fields.category.active = 'yes';
        }
        if( (M.curTenant.modules['ciniki.links'].flags&0x02) > 0 ) {
            this.edit.sections._tags.active = 'yes';
        } else {
            this.edit.sections._tags.active = 'no';
        }
        if( (M.curTenant.modules['ciniki.links'].flags&0x10) > 0 ) {
            this.edit.sections._notes.active = 'yes';
        } else {
            this.edit.sections._notes.active = 'no';
        }
        
        if( (M.curTenant.modules['ciniki.links'].flags&0x03) == 0x03 ) {
            this.tags.sections.tags.label = '';
            this.tags.sections.categories.label = '';
            this.tags.sections.types.visible = 'yes';
            this.showTags(cb, 10);
        } else if( (M.curTenant.modules['ciniki.links'].flags&0x01) == 0x01 ) {
            this.tags.sections.categories.label = 'Categories';
            this.tags.sections.tags.label = 'Tags';
            this.tags.sections.types.visible = 'no';
            this.showTags(cb, 10);
        } else if( (M.curTenant.modules['ciniki.links'].flags&0x02) == 0x02 ) {
            this.tags.sections.categories.label = 'Categories';
            this.tags.sections.tags.label = 'Tags';
            this.tags.sections.types.visible = 'no';
            this.showTags(cb, 40);
        } else {
            this.showMenu(cb);
        }
    };

    this.showMenu = function(cb) {
        this.menu.data = [];
        //
        // Grab the list of sites
        //
        var rsp = M.api.getJSONCb('ciniki.links.linkList', {'tnid':M.curTenantID}, function(rsp) {
            if( rsp.stat != 'ok' ) {
                M.api.err(rsp);
                return false;
            }
            var p = M.ciniki_links_main.menu;
            p.data = rsp.links;

            p.sections = {};
            // 
            // Setup the menu to display the categories
            //
            p.data = {};
            if( rsp.sections.length > 0 ) {
                for(i in rsp.sections) {
                    p.data[rsp.sections[i].section.sname] = rsp.sections[i].section.links;
                    p.sections[rsp.sections[i].section.sname] = {'label':rsp.sections[i].section.sname,
                        'type':'simplegrid', 'num_cols':1,
                        'headerValues':null,
                        'cellClasses':[''],
                        'addTxt':'Add Link',
                        'addFn':'M.ciniki_links_main.showEdit(\'M.ciniki_links_main.showMenu();\',0,0,\'' + rsp.sections[i].section.sname + '\');',
                        'noData':'No links added',
                        };
                    if( (M.curTenant.modules['ciniki.links'].flags&0x01) == 0 ) {
                        p.sections[rsp.sections[i].section.sname].label = 'Links';
                    }
                }
            } else {
                p.data = {'_':{}};
                p.sections['_'] = {'label':'',
                    'type':'simplegrid', 'num_cols':1,
                    'headerValues':null,
                    'cellClasses':[''],
                    'addTxt':'Add Link',
                    'addFn':'M.ciniki_links_main.showEdit(\'M.ciniki_links_main.showMenu();\',0);',
                    'noData':'No links added',
                    };
            }

            p.refresh();
            p.show(cb);
        });
    };
    
    this.showTags = function(cb, t) {
        if( t != null ) { this.tags.tag_type = t; }
        if( this.tags.tag_type == 10 ) {
            this.tags.sections.categories.visible = 'yes';
            this.tags.sections.tags.visible = 'no';
            this.tags.sections.types.selected = 'categories';
        } else {
            this.tags.sections.categories.visible = 'no';
            this.tags.sections.tags.visible = 'yes';
            this.tags.sections.types.selected = 'tags';
        }
        M.api.getJSONCb('ciniki.links.linkTags', {'tnid':M.curTenantID}, function(rsp) {
            if( rsp.stat != 'ok' ) {
                M.api.err(rsp);
                return false;
            }
            var p = M.ciniki_links_main.tags;
            p.data = rsp;
            p.refresh();
            p.show(cb);
        });
    };

    this.showTagsTab = function(t) {
        if( t != null ) {
            this.tags.tag_type = t;
        }
        if( this.tags.tag_type == 10 ) {
            this.tags.sections.categories.visible = 'yes';
            this.tags.sections.tags.visible = 'no';
            this.tags.sections.types.selected = 'categories';
        } else {
            this.tags.sections.categories.visible = 'no';
            this.tags.sections.tags.visible = 'yes';
            this.tags.sections.types.selected = 'tags';
        }
        this.tags.refresh();
        this.tags.show();
    };

    //
    // t - tag type
    // n - tag name
    this.showList = function(cb,t,n) {
        if( t != null ) { this.list.tag_type = t; }
        if( n != null ) { this.list.tag_name = unescape(n); }
        this.list.sections.links.label = this.list.tag_name;
        M.api.getJSONCb('ciniki.links.linkList', 
            {'tnid':M.curTenantID, 
                'tag_type':this.list.tag_type, 'tag_name':encodeURIComponent(this.list.tag_name)}, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    }
                    var p = M.ciniki_links_main.list;
                    p.data = rsp;
                    p.refresh();
                    p.show(cb);
                });
    };

    this.showEdit = function(cb, lid, t, n) {
        this.edit.reset();
        this.edit.sections._buttons.buttons.delete.visible = 'no';
        if( lid != null ) { this.edit.link_id = lid; }
        this.edit.sections._buttons.buttons.delete.visible = (this.edit.link_id>0?'yes':'no');
        M.api.getJSONCb('ciniki.links.linkGet', 
            {'tnid':M.curTenantID, 'link_id':this.edit.link_id, 'tags':'yes'}, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                var p = M.ciniki_links_main.edit;
                p.data = rsp.link;
                if( rsp.link.id == 0 ) {
                    if( t != null && n != null ) {
                        if( t == 10 ) {
                            p.data.categories = unescape(n);
                        } else if( t == 40 ) {
                            p.data.tags = unescape(n);
                        }
                    }
                }
                if( (M.curTenant.modules['ciniki.links'].flags&0x01) > 0 && rsp.categories != null ) {
                    var tags = [];
                    for(i in rsp.categories) {
                        tags.push(rsp.categories[i].tag.name);
                    }
                    p.sections._categories.fields.categories.tags = tags;
                } else {
                    p.sections._categories.fields.categories.tags = [];
                }
                if( (M.curTenant.modules['ciniki.links'].flags&0x02) > 0 && rsp.tags != null ) {
                    var tags = [];
                    for(i in rsp.tags) {
                        tags.push(rsp.tags[i].tag.name);
                    }
                    p.sections._tags.fields.tags.tags = tags;
                } else {
                    p.sections._tags.fields.tags.tags = [];
                }
                p.refresh();
                p.show(cb);
            });
    };

    this.saveLink = function() {
        if( this.edit.link_id > 0 ) {
            var c = this.edit.serializeForm('no');
            if( c != '' ) {
                var rsp = M.api.postJSONCb('ciniki.links.linkUpdate', 
                    {'tnid':M.curTenantID, 'link_id':this.edit.link_id}, c, function(rsp) {
                        if( rsp.stat != 'ok' ) {
                            M.api.err(rsp);
                            return false;
                        } 
                        M.ciniki_links_main.edit.close();
                    });
            } else {
                M.ciniki_links_main.edit.close();
            }
        } else {
            var c = this.edit.serializeForm('yes');
            var rsp = M.api.postJSONCb('ciniki.links.linkAdd', 
                {'tnid':M.curTenantID}, c, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    } 
                    M.ciniki_links_main.edit.close();
                });
        }
    };

    this.deleteLink = function() {
        if( confirm("Are you sure you want to remove '" + this.edit.data.name + "' as an link ?") ) {
            var rsp = M.api.getJSONCb('ciniki.links.linkDelete', 
                {'tnid':M.curTenantID, 'link_id':M.ciniki_links_main.edit.link_id}, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    }
                    M.ciniki_links_main.edit.close();
                });
        }
    };
}
