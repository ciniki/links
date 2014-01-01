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
                'category':{'label':'Category', 'type':'text', 'livesearch':'yes', 'livesearchempty':'yes'},
                'url':{'label':'URL', 'hint':'Enter the http:// address for your links website', 'type':'text'},
                }}, 
			'_description':{'label':'Additional Information', 'fields':{
				'description':{'label':'', 'hidelabel':'yes', 'hint':'Add additional information about your link', 'type':'textarea'},
				}},
			'_save':{'label':'', 'buttons':{
				'save':{'label':'Save', 'fn':'M.ciniki_links_main.saveLink();'},
				'delete':{'label':'Delete', 'fn':'M.ciniki_links_main.deleteLink();'},
				}},
            };  
		this.edit.fieldValue = function(s, i, d) { return this.data[i]; }
		this.edit.liveSearchCb = function(s, i, value) {
			if( i == 'category' ) {
				var rsp = M.api.getJSONBgCb('ciniki.links.linkSearchField', {'business_id':M.curBusinessID, 'field':i, 'start_needle':value, 'limit':15},
					function(rsp) {
						M.ciniki_links_main.edit.liveSearchShow(s, i, M.gE(M.ciniki_links_main.edit.panelUID + '_' + i), rsp.results);
					});
			}
		};
		this.edit.liveSearchResultValue = function(s, f, i, j, d) {
			if( (f == 'category' ) && d.result != null ) { return d.result.name; }
			return '';
		};
		this.edit.liveSearchResultRowFn = function(s, f, i, j, d) { 
			if( (f == 'category' )
				&& d.result != null ) {
				return 'M.ciniki_links_main.edit.updateField(\'' + s + '\',\'' + f + '\',\'' + escape(d.result.name) + '\');';
			}
		};
		this.edit.updateField = function(s, lid, result) {
			M.gE(this.panelUID + '_' + lid).value = unescape(result);
			this.removeLiveSearch(s, lid);
		};
		this.edit.fieldHistoryArgs = function(s, i) {
			return {'method':'ciniki.links.linkHistory', 'args':{'business_id':M.curBusinessID, 'link_id':this.link_id, 'field':i}};
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
		
		this.showMenu(cb);
	};

	this.showMenu = function(cb) {
		this.menu.data = [];
		//
		// Grab the list of sites
		//
		var rsp = M.api.getJSONCb('ciniki.links.linkList', {'business_id':M.curBusinessID}, function(rsp) {
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
						'addFn':'M.ciniki_links_main.showEdit(\'M.ciniki_links_main.showMenu();\',0,\'' + rsp.sections[i].section.sname + '\');',
						'noData':'No links added',
						};
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

	this.showEdit = function(cb, lid, category) {
		this.edit.reset();
		if( lid != null ) {
			this.edit.link_id = lid;
		}
		if( this.edit.link_id > 0 ) {
			var rsp = M.api.getJSONCb('ciniki.links.linkGet', 
				{'business_id':M.curBusinessID, 'link_id':this.edit.link_id}, function(rsp) {
					if( rsp.stat != 'ok' ) {
						M.api.err(rsp);
						return false;
					}
					M.ciniki_links_main.edit.data = rsp.link;
					M.ciniki_links_main.edit.refresh();
					M.ciniki_links_main.edit.show(cb);
				});
		} else {
			this.edit.data = {};
			if( category != null && category != '' ) {
				this.edit.data.category = category;
			}
			this.edit.refresh();
			this.edit.show(cb);
		}
	};

	this.saveLink = function() {
		if( this.edit.link_id > 0 ) {
			var c = this.edit.serializeForm('no');
			if( c != '' ) {
				var rsp = M.api.postJSONCb('ciniki.links.linkUpdate', 
					{'business_id':M.curBusinessID, 'link_id':this.edit.link_id}, c, function(rsp) {
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
				{'business_id':M.curBusinessID}, c, function(rsp) {
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
				{'business_id':M.curBusinessID, 'link_id':M.ciniki_links_main.edit.link_id}, function(rsp) {
					if( rsp.stat != 'ok' ) {
						M.api.err(rsp);
						return false;
					}
					M.ciniki_links_main.edit.close();
				});
		}
	};
}
