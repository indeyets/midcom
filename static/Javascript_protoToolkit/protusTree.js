function protusTree(id, options) {
    this.id = id;
    
    if (options == null) {
        options = {};
    }
    
    this.dataUrl = options.dataUrl;
    this.cssClass = options.cssClass;
    this.cssStyle = options.cssStyle;
    this.serverUrl = options.serverUrl;
    this.imagePath = options.imagePath || "midcom-static/Javascript_protoToolkit/images/";
    this.iconPath = options.iconPath || "midcom-static/stock-icons/16x16/";
    this.expandRootItem = (options.expandRootItem == null ? true : options.expandRootItem);	
    this.hideRootItem = (options.hideRootItem == null ? false : options.hideRootItem);
    this.rootItemId = options.rootItemId;
    this.expandItemOnClick = (options.expandItemOnClick == null ? true : options.expandItemOnClick);
    this.initialData = options.initialData;
    this.scroll = (options.scroll == null ? true : options.scroll);
    this.preloadItems = (options.preloadItems == null ? true : options.preloadItems);
    
    this.collapsedItemIconHtml = options.collapsedItemIconHtml;
    this.expandedItemIconHtml = options.expandedItemIconHtml;
    this.leafIconHtml = options.leafIconHtml;
    this.loadingIconHtml = options.loadingIconHtml;
    this.loadingTreeHtml = options.loadingTreeHtml;
    this.searchingHtml = options.searchingHtml;
    this.loadingItemHtml = options.loadingItemHtml;

	this.enableDragAndDrop = options.enableDragAndDrop || false;

    this.onClickItem = options.onClickItem;
    this.allowClickBranch = (options.allowClickBranch == null ? true : options.allowClickBranch);
    this.allowClickLeaf = (options.allowClickLeaf == null ? true : options.allowClickLeaf);
    this.onExpandItem = options.onExpandItem;
    this.onCollapseItem = options.onCollapseItem;
    this.onLoadItem = options.onLoadItem;
    
	this.iconMapping = { group: 'stock_people.png',
	 					 person: 'stock_person.png',
	 					 folder: 'folder.png',
	 					 article: 'article.png' };
    
    this._root = {};
    this._itemsIndex = {};
    this._activeItemId = null;
    this._scrollToItemIdOnLoad = null;
    this._scrollToItemMustBeExpanded = false;
    this._searchCount = 0;
    this._preloadCount = 0;
    this._updateItemDisplay = null;
	this._imageUrl = this.serverUrl + this.imagePath;
	this._iconUrl = this.serverUrl + this.iconPath;
	
	this._debugLineNum = 1;
}

protusTree.DEV_DEBUG = true;
//protusTree.DEV_SHOW_PRELOADS = true;
//protusTree.DEV_SHOW_ITEM_IDS = true;

protusTree.prototype._logMessage = function(msg) {
	if(protusTree.DEV_DEBUG) {
		console.log(this._debugLineNum + ": " + msg);
	}
	this._debugLineNum += 1;
}

protusTree.prototype._markItemForUpdateDisplay = function (item) {
	this._logMessage("_markItemForUpdateDisplay item.id: "+item.id);
    var tree = this;
    // This is not very intelligent yet... basically if only one item needs to be updated, that's fine, otherwise the whole tree is updated.
    if (tree._updateItemDisplay == null) {
        tree._updateItemDisplay = item;
    } else if (tree._updateItemDisplay != item) {
        tree._updateItemDisplay = tree._root;
    }	
}

protusTree.prototype._getClass = function (suffix) {
	this._logMessage("_getClass");
    if (suffix != "") {
        suffix = "_" + suffix;
    }
    result = 'protus_tree' + suffix;
    if (this.cssClass != null) {
        result += ' ' + this.cssClass + suffix;
    }
    return result;
}

protusTree.prototype._escapeId = function (itemId) {
	this._logMessage("_escapeId");
    //XXX find out exactly what characters are allowed in HTML id
    return escape(itemId);
}

protusTree.prototype._getCollapsedItemIconHtml = function (item) {
	this._logMessage("_getCollapsedItemIconHtml");
    if (this.collapsedItemIconHtml != null) {
        return this.collapsedItemIconHtml;
    } else {
		var iconFile = this.iconMapping[item.type];
		//var icon = eval(this.iconMapping+"."+item.type);
		this._logMessage("iconFile: "+iconFile);
		
		iconFile = (iconFile != undefined || iconFile != null) ? this._iconUrl + iconFile : this._imageUrl + 'protusTree_transparent_pixel.gif';
        return '<img src="' + iconFile + '" alt="&gt;" id="' + this.id + '_item_icon_' + this._escapeId(item.id) + '" class="' + this._getClass("item_icon") + ' ' + this._getClass("branch_collapsed_icon") + '" />';
    }
}

protusTree.prototype._getExpandedItemIconHtml = function (item) {
	this._logMessage("_getExpandedItemIconHtml");
    if (this.expandedItemIconHtml != null) {
        return this.expandedItemIconHtml;
    } else {
		var iconFile = this.iconMapping[item.type];
		//var icon = eval(this.iconMapping+"."+item.type);
		this._logMessage("iconFile: "+iconFile);
		
		iconFile = (iconFile != undefined || iconFile != null) ? this._iconUrl + iconFile : this._imageUrl + 'protusTree_transparent_pixel.gif';
        return '<img src="' + iconFile + '" alt="v" id="' + this.id + '_item_icon_' + this._escapeId(item.id) + '" class="' + this._getClass("item_icon") + ' ' + this._getClass("branch_expanded_icon") + '" />';
    }
}

protusTree.prototype._getLeafIconHtml = function (item) {
	this._logMessage("_getLeafIconHtml");
    if (this.leafIconHtml != null) {
        return this.leafIconHtml;
    } else {
		var iconFile = this.iconMapping[item.type];
		//var icon = eval(this.iconMapping+"."+item.type);
		this._logMessage("iconFile: "+iconFile);
		
		iconFile = (iconFile != undefined || iconFile != null) ? this._iconUrl + iconFile : this._imageUrl + 'protusTree_transparent_pixel.gif';
        return '<img src="' + iconFile + '" alt=" " id="' + this.id + '_item_icon_' + this._escapeId(item.id) + '" class="' + this._getClass("item_icon") + ' ' + this._getClass("leaf_icon") + '" />';
    }
}

protusTree.prototype._getLoadingIconHtml = function () {
	this._logMessage("_getLoadingIconHtml");
    if (this.loadingIconHtml != null) {
        return this.loadingIconHtml;
    } else {
        return '<img src="' + this._imageUrl + 'protusTree_loading_spinner.gif" alt="[loading]" class="' + this._getClass("loading_icon") + '" />';
    }
}

protusTree.prototype._getLoadingTreeHtml = function () {
	this._logMessage("_getLoadingTreeHtml");
    if (this.loadingTreeHtml != null) {
        return this.loadingTreeHtml;
    } else {
        return '<span class="' + this._getClass("loading_tree") + '">' + this._getLoadingIconHtml() + 'Loading tree data&hellip;</span>';
    }
}

protusTree.prototype._getSearchingHtml = function () {
	this._logMessage("_getSearchingHtml");
    if (this.searchingHtml != null) {
        return this.searchingHtml;
    } else {
        return '<div class="' + this._getClass("searching") + '">' + this._getLoadingIconHtml() + 'Searching for item&hellip;</div>';
    }
}

protusTree.prototype._getLoadingItemHtml = function () {
	this._logMessage("_getLoadingItemHtml");
    if (this.loadingItemHtml != null) {
        return this.loadingItemHtml;
    } else {
        return this._getLoadingIconHtml() + 'Loading&hellip;';
    }
}

protusTree.prototype._startPreloads = function (item) {
	this._logMessage("_startPreloads");
    var tree = this;
    if (!tree.preloadItems || tree._preloadCount > 0) {
        return false;
    }
    if (item == null) {
        item = tree._root;
    }
    if (!item.isExpanded || item.isLoading) {
        return false;
    }
    var tailBranch = true;
    for (var i = 0; i < item.children.length; i++) {
        var child = item.children[i];
        if (!child.isLeaf && ( child.isLoaded || child.isLoading )) {
            tailBranch = false;
        }
    }
    var doLoad = false;
    if (tailBranch) {
        for (var i = 0; i < item.children.length; i++) {
            var child = item.children[i];
            if (!child.isLeaf) {
                if (!child.isLoaded && !child.isLoading) {
                    doLoad = true;
                    child.isLoading = true;
                    child.isLoadingBackground = true;
                }
            }
        }
    }
    var didLoad = false;
    if (doLoad) {
        this._logMessage("preloading children of " + item.id);
        tree._preloadCount++;
        if (item == tree._root) {
            tree._requestItem(tree._root.children[0].id, 2, tree._onPreloadItemReceived.bind(tree));	
        } else {
            tree._requestItem(item.id, 3, tree._onPreloadItemReceived.bind(tree));	
        }
        if (protusTree.DEV_SHOW_PRELOADS) {
            tree._markItemForUpdateDisplay(item);
        }
        didLoad = true;
    } else {
        for (var i = 0; i < item.children.length; i++) {
            var child = item.children[i];
            if (!child.isLeaf && child.isLoaded) {
                if (tree._startPreloads(child)) {
                    didLoad = true;
                }
            }
        }
    }

    return didLoad;
}

protusTree.prototype._stopLoading = function () {
	this._logMessage("_stopLoading");
    var tree = this;
    function recurse(item) {
        if (item.isLoading) {
            item.isLoading = false;
            item.isExpanded = false;
        }		
        if (item.children != null) {
            for (var i = 0; i < item.children.length; i++) {
                recurse(item.children[i]);
            }
        }
    }
    recurse(tree._root);
    tree._markItemForUpdateDisplay(tree._root);
    tree._searchCount = 0;
    tree._preloadCount = 0;
    tree._updateDisplay();
}

protusTree.prototype._onItemFailure = function (request) {
	this._logMessage("_onItemFailure request.responseText:"+request.responseText);
    alert("protusTree error: could not get data from server: HTTP error: " + request.status);
    this._stopLoading();
}

protusTree.prototype._requestItem = function (itemId, depth, onItemCallback, options) {
	this._logMessage("_requestItem");
    var tree = this;	
    if (options == null) {
        options = {};
    }
    var url = tree.dataUrl;
    var requestOptions = new Object();
    var delim = "?";
    if (itemId != null) {
        requestOptions.itemId = itemId;
        url += delim + "item_id=" + escape(itemId);		
        delim = "&";
    }
    if (depth != null) {
        requestOptions.depth = depth;
        url += delim + "depth=" + depth;
        delim = "&";
    }
    if (options.includeParents) {
        requestOptions.includeParents = true;
        requestOptions.rootItemId = tree.rootItemId;
        url += delim + "include_parents=1&root_item_id=" + escape(tree.rootItemId);
        tree._searchCount++;
    }
	this._logMessage("_requestItem url: "+url);
    if (options.initialRequest) {
        requestOptions.initialRequest = true;
    }
    new Ajax.Request(url, {onSuccess: function (request) { tree._onItemResponse(request, onItemCallback, requestOptions) }, onFailure: tree._onItemFailure.bind(tree), evalScripts:true, asynchronous:true, method:"get"});
    return true;
}

protusTree.prototype._onExpandItemReceived = function (item, requestOptions) {
	this._logMessage("_onExpandItemReceived");
    var tree = this;
    item.isLoading = false;
    tree._markItemForUpdateDisplay(item);
    tree._startPreloads();
    tree._updateDisplay();	
}

protusTree.prototype._onPreloadItemReceived = function (item, requestOptions) {
	this._logMessage("_onPreloadItemReceived");
    var tree = this;
    if (tree._preloadCount <= 0) {
        return;
    }
    tree._preloadCount--;
    item.isLoading = false;
    for (var i = 0; i < item.children.length; i++) {
        item.children[i].isLoading = false;		
    }
    tree._startPreloads();
    tree._markItemForUpdateDisplay(item);
    tree._updateDisplay();	
}

protusTree.prototype._onClickExpand = function (item) {
	this._logMessage("_onClickExpand");
    var tree = this;
    var expanded = tree._expandItem(item);
    tree._updateDisplay();	
    if (expanded) {
        tree.scrollToItem(item.id);
        if (item.isLoading) {
            tree._scrollToItemIdOnLoad = item.id;
            tree._scrollToItemMustBeExpanded = true;
        }
        if (tree.onExpandItem != null) {
            tree.onExpandItem(item);
        }
    }
}

protusTree.prototype._onClickCollapse = function (item) {
    var tree = this;
    if (!item.isExpanded) {
        return;
    }
    item.isExpanded = false;
    tree._markItemForUpdateDisplay(item);
    tree._updateDisplay();	
    if (tree.onCollapseItem != null) {
        tree.onCollapseItem(item);
    }
}

protusTree.prototype._onClickItem = function (item) {
    var tree = this;
    if (tree.expandItemOnClick && !item.isExpanded && !item.isLeaf) {
        tree._onClickExpand(item);		
    }
    if (tree.onClickItem != null && ((tree.allowClickLeaf && item.isLeaf) || (tree.allowClickBranch && !item.isLeaf))) {
        tree.onClickItem(item);
    }
    tree._updateDisplay();
}

protusTree.prototype._getItem = function (itemId) {
	this._logMessage("_getItem");
    return this._itemsIndex[itemId];
}

protusTree.prototype._getItemElementId = function (itemId) {
	this._logMessage("_getItemElementId");
    return this.id + "_item_" + this._escapeId(itemId);
}

protusTree.prototype._getItemElement = function (itemId) {
	this._logMessage("_getItemElement");
    return $(this._getItemElementId(itemId));
}

protusTree.prototype._isRootItem = function (item) {
	this._logMessage("_isRootItem");
    var tree = this;
    return item == tree._root || (tree.hideRootItem && item == tree._root.children[0]);
}

protusTree.prototype._renderItemHeading = function (item) {
	this._logMessage("_renderItemHeading");
    var tree = this;
    var html = '';
    if (!item.isLeaf) {
        html += '<a href="#" id="' + tree.id + '_branch_expand_collapse_link_' + tree._escapeId(item.id) + '" class="' + this._getClass("branch_expand_collapse_link") + '">';
        if (item.isExpanded) {
            html += tree._getExpandedItemIconHtml(item);
        } else {
            html += tree._getCollapsedItemIconHtml(item);
        }
        html += '</a>';
    } else {
        html += tree._getLeafIconHtml(item);
    }
    var itemLinkExists = false;
    var extraNameClass = "";
    if (item.id == tree._activeItemId) {
        extraNameClass = " " + this._getClass("active_item_name");
    }
    var name_html = '<span id="' + tree.id + '_item_name_' + tree._escapeId(item.id) + '" class="' + this._getClass("item_name") + extraNameClass + '">' + item.name + '</span>';
    if (((tree.onClickItem != null && ((tree.allowClickLeaf && item.isLeaf) || (tree.allowClickBranch && !item.isLeaf))) ||
            (tree.expandItemOnClick && !item.isLeaf && !item.isExpanded)) && !item.isLoadingDisplay) {
        name_html = '<a href="#" id="' + tree.id + '_item_link_' + tree._escapeId(item.id) + '" class="' + this._getClass("item_link") + '">' + name_html + '</a>';
        itemLinkExists = true;
    }
    if (protusTree.DEV_SHOW_ITEM_IDS) {
        name_html = "(" + item.id + ") " + name_html;
    }
    html += name_html;
    if (protusTree.DEV_SHOW_PRELOADS) {
        if (item.isLoading && item.isLoadingBackground) {
            html += " " + tree._getLoadingIconHtml();
        }
    }
    $(tree.id + "_item_heading_" + tree._escapeId(item.id)).innerHTML = html;
    if (!item.isLeaf) {
        if (item.isExpanded) {
            $(tree.id + '_branch_expand_collapse_link_' + tree._escapeId(item.id)).onclick = function () { tree._onClickCollapse(item); return false }		
        } else {
            $(tree.id + '_branch_expand_collapse_link_' + tree._escapeId(item.id)).onclick = function () { tree._onClickExpand(item); return false }
        }
    }
    if (itemLinkExists) {
        $(tree.id + '_item_link_' + tree._escapeId(item.id)).onclick = function() { tree._onClickItem(item); return false }
    }
}

protusTree.prototype._hideItem = function (child) {
	this._logMessage("_hideItem");
    var tree = this;
    var elem = tree._getItemElement(child.id);
    if (elem) {
        $(tree.id).removeChild(elem);
        if (child.isLoaded || (child.isLoading && !child.isLoadingBackground)) {
            tree._hideItemChildren(child);
        }
    }
}

protusTree.prototype._hideItemChildren = function (item) {
    var tree = this;
    tree._hideItem(tree._getLoadingDisplayChild(item));
    if (!item.isLoading) {
        for (var i = 0; i < item.children.length; i++) {
            tree._hideItem(item.children[i]);
        }
    }
    item.childrenVisible = false;
}

protusTree.prototype._updateItemChildren = function (item, afterElem, indentLevel, containerElem) {
	this._logMessage("_updateItemChildren");
    var tree = this;
    
    function doUpdateChild(child) {
        var elem = tree._getItemElement(child.id);
        if (elem == null) {
            var html = "";
            html += '<div id="' + tree.id + '_item_' + tree._escapeId(child.id) + '" class="' + tree._getClass("item") + '">'+"\n";
            for (var j = 0; j < indentLevel; j++) {
                html += '<div class="' + tree._getClass("item_indent") + '">'+"\n";
            }
            html += '<span id="' + tree.id + '_item_heading_' + tree._escapeId(child.id) + '" class="' + tree._getClass("item_heading") + '"></span>'+"\n";
            for (var j = 0; j < indentLevel; j++) {
                html += '</div>'+"\n";
            }
            html += '</div>'+"\n";
            new Insertion.After(afterElem, html);
            elem = tree._getItemElement(child.id);
        }
        tree._renderItemHeading(child);
        afterElem = elem;
        if (child.isLoaded || (child.isLoading && !child.isLoadingBackground)) {
            afterElem = tree._updateItemChildren(child, afterElem, indentLevel + 1, containerElem);
        }
    }
    
    if (!item.isExpanded) {
        tree._hideItemChildren(item);
    } else {
        if (item.isLoaded) {
            tree._hideItem(tree._getLoadingDisplayChild(item));
            for (var i = 0; i < item.children.length; i++) {	
                doUpdateChild(item.children[i]);
            }
        } else {
            doUpdateChild(tree._getLoadingDisplayChild(item));
        }
        item.childrenVisible = true;
    }
    return afterElem;
}

protusTree.prototype._getLoadingDisplayChild = function (item) {
	this._logMessage("_getLoadingDisplayChild");
    var tree = this;
    var loadingChild = {id: "___protus_tree_LOADING_" + item.id + "___", 
                         name: tree._getLoadingItemHtml(), 
                         children: [], 
                         isLoadingDisplay: true};
    tree._setItemDerivedAttributes(loadingChild);
    return loadingChild;
}

protusTree.prototype._updateDisplay = function () {
	this._logMessage("_updateDisplay");
    var tree = this;
    if (tree._searchCount > 0) {
        Element.show(tree.id + "_searching");
    } else {
        Element.hide(tree.id + "_searching");
    }
    var updateItem = tree._updateItemDisplay;	
    if (updateItem != null) {
        tree._updateItemDisplay = null;
        if (tree._isRootItem(updateItem)) {
            if (tree.hideRootItem) {
                updateItem = tree._root.children[0];
            }
            tree._updateItemChildren(updateItem, $(tree.id + "_root"), 0, $(tree.id));
        } else {
            tree._renderItemHeading(updateItem);
            
            var indentLevel = 0;
            var parentItem = updateItem;
            while (!tree._isRootItem(parentItem)) {
                indentLevel++;
                parentItem = parentItem.parent;
            }
            
            if (updateItem.isLoaded || (updateItem.isLoading && !updateItem.isLoadingBackground)) {
                tree._updateItemChildren(updateItem, tree._getItemElement(updateItem.id), indentLevel, $(tree.id));
            }
        }
    }
    tree._checkScrollOnLoad();
}

protusTree.prototype._checkScrollOnLoad = function () {
	this._logMessage("_checkScrollOnLoad");
    var tree = this;
    if (tree._scrollToItemIdOnLoad == null) {
        return;
    }
    var item = tree._itemsIndex[tree._scrollToItemIdOnLoad];
    if (item == null) {
        return;
    }
    if (tree._scrollToItemMustBeExpanded) {
        if (item.isLoaded) {
            // The user may have collapsed the item while it was loading, so only scroll to it if it's still expanded.
            if (item.isExpanded) {
                tree.scrollToItem(item.id);
            }
            tree._scrollToItemIdOnLoad = null;
        }
    } else {
        tree.scrollToItem(item.id);
        tree._scrollToItemIdOnLoad = null;		
    }
}

protusTree.prototype._getElementPosition = function (destinationLink) {
	this._logMessage("_getElementPosition");
    // borrowed from http://www.sitepoint.com/print/scroll-smoothly-javascript
    var destx = destinationLink.offsetLeft;  
    var desty = destinationLink.offsetTop;
    var thisNode = destinationLink;
    while (thisNode.offsetParent &&  
            (thisNode.offsetParent != document.body)) {
        thisNode = thisNode.offsetParent;
        destx += thisNode.offsetLeft;
        desty += thisNode.offsetTop;
    }
    return { x: destx, y: desty }
}

protusTree.prototype._scrollTo = function (top) {
    var tree = this;
    if (!tree.scroll) {
        return;
    }
    var containerElem = $(tree.id);
    containerElem.scrollTop = top;
}

protusTree.prototype.scrollToItem = function (itemId) {
    var tree = this;
    if (!tree.scroll) {
        return;
    }
    var itemElem = tree._getItemElement(itemId);
    if (itemElem == null) {
        return;
    }
    var containerElem = $(tree.id);
    var itemPos = tree._getElementPosition(itemElem);
    var containerPos = tree._getElementPosition(containerElem);
    var itemTop = itemPos.y - containerPos.y;
    var containerHeight = containerElem.offsetHeight - 35; //HACK: adjust for space used by scrollbars and other decoration
    if (itemTop + itemElem.offsetHeight > containerElem.scrollTop + containerHeight ||
            itemTop < containerElem.scrollTop) {
        // item is currently not entirely visible
        if (itemElem.offsetHeight > containerHeight) {
            // item is too big to fit, so scroll to the top
            tree._scrollTo(itemTop);
        } else {
            if (itemTop < containerElem.scrollTop + containerHeight) {
                // item is partially onscreen (the top is showing), so put whole item at bottom
                tree._scrollTo(itemTop + itemElem.offsetHeight - containerHeight);
            } else {
                // item is entirely offscreen, so center it
                tree._scrollTo(itemTop - containerHeight/2 + itemElem.offsetHeight/2);
            }
        }
    }
    tree._scrollToItemOnLoad = null;
}

protusTree.prototype._expandItem = function (item) {
	this._logMessage("_expandItem item.id: "+item.id);
    var tree = this;
    
    // Make sure all item's parents are expanded as well
    var didExpand = false;
    var parent = item.parent;
    while (parent != tree._root && parent != null) {
        if (!parent.isExpanded) {
            parent.isExpanded = true;
            tree._markItemForUpdateDisplay(parent);
            didExpand = true;
        }
        parent = parent.parent;
    }	

    // Expand the selected item
    var needToLoad = false;
    if (!item.isExpanded) {
        needToLoad = (item.children == null && !item.isLoading);
        if (needToLoad) {
            item.isLoading = true;
        }
        item.isLoadingBackground = false;
        item.isExpanded = true;
        tree._markItemForUpdateDisplay(item);
        didExpand = true;
    }
    
    // If the item has not loaded, load it now
    if (needToLoad) {
        tree._requestItem(item.id, 2, tree._onExpandItemReceived.bind(tree));	
    }	

    tree._startPreloads();	
    return didExpand;
}

protusTree.prototype._onExpandItemParentsReceived = function (item, requestOptions) {
	this._logMessage("_onExpandItemParentsReceived");
    var tree = this;
    var requestedItem = tree._getItem(requestOptions.itemId);
    this._expandItem(requestedItem);
    tree._startPreloads();
    tree._updateDisplay();	
}

protusTree.prototype.expandItem = function (itemId) {
	this._logMessage("expandItem itemId: "+itemId);
    var tree = this;
    var item = tree._getItem(itemId);
    var search = false;
    if (item == null) {
        tree._requestItem(itemId, 2, tree._onExpandItemParentsReceived.bind(tree), { includeParents: true });
        search = true;
    } else {
        this._expandItem(this._itemsIndex[itemId]);
    }
    tree._updateDisplay();
    if (search) {
        tree._scrollTo(0);
        tree._scrollToItemIdOnLoad = itemId;		
        tree._scrollToItemMustBeExpanded = false;
    } else {
        tree.scrollToItem(itemId);
    }
}

protusTree.prototype._onExpandParentsOfItemReceived = function (item, requestOptions) {
    var tree = this;
    //alert("XXX _onExpandParentsOfItemReceived item.id=" + item.id);
    var requestedItem = tree._getItem(requestOptions.itemId);
    tree._expandItem(requestedItem.parent);
    tree._startPreloads();
    tree._updateDisplay();	
}

protusTree.prototype.expandParentsOfItem = function (itemId) {
    var tree = this;
    var item = tree._getItem(itemId);
    var search = false;
    if (item == null) {
        tree._requestItem(itemId, 1, tree._onExpandParentsOfItemReceived.bind(tree), { includeParents: true });
        search = true;
    } else {
        tree._expandItem(item.parent);
    }
    tree._updateDisplay();
    if (search) {
        tree._scrollTo(0);
        tree._scrollToItemIdOnLoad = itemId;		
        tree._scrollToItemMustBeExpanded = false;
    } else {
        tree.scrollToItem(itemId);
    }
}

protusTree.prototype.activateItem = function (itemId) {
	this._logMessage("activateItem");
    var tree = this;
    // un-highlight the old active item
    var oldElem = $(tree.id + '_item_name_' + tree._escapeId(tree._activeItemId));
    if (oldElem != null) {
        oldElem.className = tree._getClass("item_name");
    }
    // highlight the new active item
    var elem = $(tree.id + '_item_name_' + tree._escapeId(itemId));
    if (elem != null) {
        elem.className = tree._getClass("item_name") + " " + tree._getClass("active_item_name");
    }
    tree._activeItemId = itemId;
    tree.scrollToItem(itemId);
}

protusTree.prototype.getHtml = function() {
	this._logMessage("getHtml");
    var tree = this;	
    var html = '';
    html += '<div id="' + tree.id + '" class="' + tree._getClass("") + '"';
    if (tree.cssStyle != null) {
        html += ' style="' + tree.cssStyle + '"';
    }
    html += '>'+"\n";
    html += ' <div id="' + tree.id + '_searching" style="display:none">' + tree._getSearchingHtml() + '</div>'+"\n";
    html += ' <div id="' + tree.id + '_loading">' + tree._getLoadingTreeHtml() + '</div>'+"\n";
    html += ' <div id="' + tree.id + '_root"></div>'+"\n";
    html += '</div>'+"\n";
    return html;
}

protusTree.prototype._setItemDerivedAttributes = function (child) {
    child.isLeaf = !(child.children == null || child.children.length > 0);
    child.isLoaded = child.children != null;
}

protusTree.prototype._setupNewItemChildren = function (item) {
	this._logMessage("_setupNewItemChildren item.id: "+item.id);
    var tree = this;
    if (item.children != null) {
        for (var i = 0; i < item.children.length; i++) {
            var child = item.children[i];
            tree._setItemDerivedAttributes(child);
            child.parent = item;
            tree._itemsIndex[child.id] = child;
            tree._setupNewItemChildren(child);
        }
    }
}

protusTree.prototype._addNewItems = function (newItem) {
	this._logMessage("_addNewItems newItem.id: "+newItem.id);
    var tree = this;
    var oldItem = tree._getItem(newItem.id);
    if (newItem.children != null && oldItem != null) {
        if (!oldItem.isLoaded) {		
            // Old item has been seen, but its children were not loaded.
            // New item does have children, so add the children to the old item and flag it as as loaded.
            oldItem.children = newItem.children;
            tree._setupNewItemChildren(oldItem);
            oldItem.isLoaded = true;
        } else {
            // Item is already in the tree and has loaded, so recurse to new item's children
            for (var i = 0; i < newItem.children.length; i++) {
                tree._addNewItems(newItem.children[i]);
            }
        }
    }
    return oldItem;
}

protusTree.prototype._onItemResponse = function (request, onItemCallback, requestOptions) {
	//alert("_onItemResponse: "+request.responseText);
    var tree = this;
    if (requestOptions.includeParents && tree._searchCount > 0) {
        tree._searchCount--;
    }
    var item;
    try {
        eval("item = " + request.responseText);
    } catch (e) {
        alert("protusTree error: cannot parse data from server: " + e);
        tree._stopLoading();
        return;
    }
    
    if (requestOptions.initialRequest) {
        tree._handleInitialItem(item);
    } else {	
        var oldItem = tree._addNewItems(item);
        if (oldItem == null) {
            alert("protusTree error: cannot add received item to tree");
            tree._stopLoading();
            return;
        }
    }
    onItemCallback(oldItem, requestOptions);
}

protusTree.prototype._onInitialItemReceived = function () {
	this._logMessage("_onInitialItemReceived");
    var tree = this;
    this.rootItemId = tree._root.children[0].id;
    Element.hide($(tree.id + "_loading"));
    if (tree.hideRootItem || tree.expandRootItem) {
        tree._expandItem(tree._root.children[0]);
    }
    tree._root.isExpanded = true;
    tree._markItemForUpdateDisplay(tree._root);
    tree._startPreloads();
    tree._updateDisplay();		
}

protusTree.prototype._handleInitialItem = function (item) {
	this._logMessage("_handleInitialItem");
    var tree = this;
    tree._root.children = [item];
    tree._root.isLoaded = true;
    tree._setupNewItemChildren(tree._root);
}

protusTree.prototype.start = function() {
	this._logMessage("start");
    var tree = this;	
    if (tree.initialData != null) {
        tree._handleInitialItem(tree.initialData);
        tree._onInitialItemReceived(tree.initialData);
    } else {
        tree._requestItem(tree.rootItemId, (tree.expandRootItem || tree.hideRootItem) ? 2 : 1, tree._onInitialItemReceived.bind(tree), { initialRequest: true });
    }
}

protusTree.prototype.render = function () {
	this._logMessage("render");
    var tree = this;	
    document.write(tree.getHtml());
    tree.start();
}
