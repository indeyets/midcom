var PROTOTOOLBAR_TYPE_MENU = 'menu';
var PROTOTOOLBAR_TYPE_PALETTE = 'palette';
var PROTOTOOLBAR_DEFAULT_ID = 'protoToolbar';

var protoToolbar = Class.create();
protoToolbar.prototype = {

    initialize: function(parameters)
    {
        if(parseFloat(Prototype.Version.split(".")[0] + "." + Prototype.Version.split(".")[1]) < 1.5) {
            this.recursiveHack = true;
        } else {
            this.recursiveHack = false;
        }
        this.disabled = false;
        this.debugON = parameters.debug || false;

        this.debug("Initialization start");

        this.type = parameters.type || PROTOTOOLBAR_TYPE_MENU; // Types: menu, palette
        this.items = parameters.items;
        this.id = parameters.id || PROTOTOOLBAR_DEFAULT_ID + '-' + this.type;

        this.protoMemory = new protoMemory( 'protoToolbar' );

        this.enableMemory = parameters.enableMemory || this.protoMemory.hasSupport();

        if(this.enableMemory && this.type == PROTOTOOLBAR_TYPE_PALETTE) {
            //get data from cookie
            //var memcheck = this.getMemoryContent();
            //if(memcheck == false) {
                //#this.initMemory();
            //}
            var memPosition = this.protoMemory.read("position");
        }

        this.imagepath = parameters.imagePath || '';//'images/tango-icons/16x16/';

        var paramCreate = parameters.create=='no'?false:(parameters.create=='yes'?true:'NULL');
        if(this.type == PROTOTOOLBAR_TYPE_MENU) {
            var createToolbar = paramCreate=='NULL'?false:paramCreate;
        } else if(this.type == PROTOTOOLBAR_TYPE_PALETTE) {
            var createToolbar = paramCreate=='NULL'?false:paramCreate;
        }

        var enableDrag = parameters.enableDrag || true;

        this.padding = parameters.padding || [20,0,0,0];
        var defPosition = this.getDefaultPosition();

        if(memPosition != null) {
            this.debug("memory, position: "+memPosition.x);
            var posX = memPosition.x!=''&&memPosition.x!=undefined?memPosition.x:defPosition[0]+'px';
            var posY = memPosition.y!=''&&memPosition.y!=undefined?memPosition.y:defPosition[1]+'px';
            this.debug("posX, : "+posX);
        } else {
            var posX = defPosition[0]+'px';
            var posY = defPosition[1]+'px';
        }

        this.type_config = new Object();
        this.type_config[PROTOTOOLBAR_TYPE_MENU] = { height: 25,
                                                     width: 0,
                                                     create: createToolbar };
        this.type_config[PROTOTOOLBAR_TYPE_PALETTE] = { height: 20,
                                                        width: 300,
                                                        create: createToolbar,
                                                        dragEnabled: enableDrag,
                                                        posX: posX,
                                                        posY: posY };

        this.width = parameters.width || this.type_config[this.type].width;
        this.height = parameters.height || this.type_config[this.type].height;


        var default_logo = new Array(
                                     {
                                       href: 'http://www.midgard-project.org',
                                       classname: '',
                                       target: '_blank',
                                       src: 'images/midgard-logo.png',
                                       alt: 'Midgard',
                                       width: '16',
                                       height: '16'
                                     }
                                    );
        this.toolbar_logos = parameters.logos || default_logo;

        var default_items = new Array(
                                      {
                                       title: 'Help',
                                       href: '#help',
                                       target: '_self',
                                       classname: 'enabled',
                                       content: '',
                                       children: new Array(
                                                            {
                                                             title: 'About',
                                                             href: '#about',
                                                             target: '_self',
                                                             classname: 'enabled',
                                                             content: '',
                                                             image: 'demo/help-browser.png'
                                                            },
                                                            {
                                                             special: 'hr'
                                                            },
                                                            {
                                                             title: 'Topics',
                                                             href: '#topics',
                                                             target: '',
                                                             classname: 'enabled',
                                                             content: '<a href="#topics" alt="Topics">Topics</a>'
                                                            }
                                                          )
                                      },
                                      {
                                       title: 'Help2',
                                       href: '#help2',
                                       target: '_self',
                                       classname: 'enabled',
                                       content: '',
                                       children: new Array(
                                                            {
                                                             title: 'About2',
                                                             href: '#about',
                                                             target: '_self',
                                                             classname: 'enabled',
                                                             content: '',
                                                             image: 'demo/help-browser.png'
                                                            },
                                                            {
                                                             special: 'hr'
                                                            },
                                                            {
                                                             title: 'Topics2',
                                                             href: '#topics',
                                                             target: '',
                                                             classname: 'enabled',
                                                             content: '<a href="#topics" alt="Topics">Topics</a>'
                                                            }
                                                          )
                                      }
                                     );
        this.menu_items = parameters.items || default_items;

        this.parent = parameters.parent || document.getElementsByTagName("body")[0];

        this.create( this.type );

        this.debug("Initialization end");
    },

    create: function( p_type )
    {
        if(p_type == PROTOTOOLBAR_TYPE_MENU) {
            if(this.type_config[PROTOTOOLBAR_TYPE_MENU].create) {
                this.toolbar = this.create_menu();
            } else {
                this.toolbar = this.collect( PROTOTOOLBAR_TYPE_MENU );
            }
        } else if(p_type == PROTOTOOLBAR_TYPE_PALETTE) {
            if(this.type_config[PROTOTOOLBAR_TYPE_PALETTE].create) {
                this.toolbar = this.create_palette();
            } else {
                this.toolbar = this.collect( PROTOTOOLBAR_TYPE_PALETTE );

                if(!this.disabled) {
                    if(this.type_config[PROTOTOOLBAR_TYPE_PALETTE].dragEnabled) {
                        var ondragend = function(element){this.onDragEnd(element);}.bind(this);
                        new Draggable(this.toolbar,{snap:false,revert:ondragend});
                    }
                }
            }
        }

    },

    collect: function( p_type )
    {
        var element = this.bodyreader( this.id );
        //this.id = this.id + '-' + this.type;
        //element.setAttribute('id',this.id);
        this.debug("element: "+element);
        
        if(!element || element == undefined) {
/*             this.debug("Couldn't find proper element from body. falling back to default action");
            if(p_type == PROTOTOOLBAR_TYPE_MENU) {
                return this.create_menu();
            } else if(p_type == PROTOTOOLBAR_TYPE_PALETTE) {
                return this.create_palette();
            } */
            this.disabled = true;
        }

        if(!this.disabled) {
            if(p_type == PROTOTOOLBAR_TYPE_MENU) {
                this.enable_menu_items( this.id );
                return element;
            } else if(p_type == PROTOTOOLBAR_TYPE_PALETTE) {
                
                /*workaround for Safari/Konqueror*/
                if (navigator.userAgent.indexOf('KHTML') != -1)
                {
                        element.style.position = "fixed";
                }
                
                if(this.type_config[PROTOTOOLBAR_TYPE_PALETTE].posX != '') {
                    element.style.left = this.type_config[p_type].posX;
                }
                if(this.type_config[PROTOTOOLBAR_TYPE_PALETTE].posY != '') {
                    element.style.top = this.type_config[p_type].posY;
                }
                new Effect.Appear( this.id );
    			
                this.enable_menu_items( this.id );
                return element;
            }
        }
    },

    enable_menu_items: function( p_id )
    {
        var menudiv = this.bodyreader( 'div#'+this.id, true );//document.getElementById(this.id);
        var contentdiv =  document.getElementById(this.id+'-content');
        this.debug(contentdiv);
        var listdiv = contentdiv.getElementsByTagName('div');
        var listdivCnt = listdiv.length;
        for(var i=0;i<listdivCnt;i++) {
            var item = listdiv[i];

            item.onmouseover = function () {set_item_display(this);}
            item.onmouseout = function () {set_item_display(this, 'none');}

            //Event.observe(item, 'mouseover', function(item){set_item_display(item)}, false);
            //Event.observe(item, 'mouseout', function(item){set_item_display(item, 'none')}, false);
        }
    },

    create_menu: function()
    {
        var menu = document.createElement("div");
        menu.setAttribute('id', this.id);

        var menu_logos = document.createElement("div");
        menu_logos.setAttribute('id', 'logos');
        menu_logos.setAttribute('class', 'logos');

        for(var i=0;i<this.toolbar_logos.length;i++) {
            var logo = this.create_logo_element( this.toolbar_logos[i] );
            menu_logos.appendChild(logo);
        }
        menu.appendChild(menu_logos);

        var menu_content = document.createElement("div");
        menu_content.setAttribute('class', 'content')

        for(var i=0;i<this.menu_items.length;i++) {
            var item = this.create_item_element( this.menu_items[i] );
            menu_content.appendChild(item);
        }
        menu.appendChild(menu_content);

        this.parent.insertBefore(menu, this.parent.firstChild);
        //alert(menu.innerHTML);
    },

    create_palette: function()
    {
        var menu = document.createElement("div");
        menu.setAttribute('id', this.id);

        var posX = this.type_config[PROTOTOOLBAR_TYPE_PALETTE].posX;
        var posY = this.type_config[PROTOTOOLBAR_TYPE_PALETTE].posY;

        var menu_logos = document.createElement("div");
        menu_logos.setAttribute('id', 'protoToolbar-palette-logos');

        for(var i=0;i<this.toolbar_logos.length;i++) {
            var logo = this.create_logo_element( this.toolbar_logos[i] );
            menu_logos.appendChild(logo);
        }
        menu.appendChild(menu_logos);

        var menu_content = document.createElement("div");
        menu_content.setAttribute('id', 'protoToolbar-palette-content')

        for(var i=0;i<this.menu_items.length;i++) {
            var item = this.create_item_element( this.menu_items[i] );
            menu_content.appendChild(item);
        }
        menu.appendChild(menu_content);

        var dragger = document.createElement("div");
        dragger.setAttribute('class','dragbar');
        menu.appendChild(dragger);

        if(this.type_config[PROTOTOOLBAR_TYPE_PALETTE].posX != '') {
            menu.style.left = this.type_config[PROTOTOOLBAR_TYPE_PALETTE].posX;
        }
        if(this.type_config[PROTOTOOLBAR_TYPE_PALETTE].posY != '') {
            menu.style.top = this.type_config[PROTOTOOLBAR_TYPE_PALETTE].posY;
        }

        this.parent.insertBefore(menu, this.parent.firstChild);

        if(this.type_config[PROTOTOOLBAR_TYPE_PALETTE].dragEnabled) {
            var ondragend = function(element){this.onDragEnd(element);}.bind(this);
            new Draggable(menu,{snap:false,revert:ondragend});
        }

        if(this.enableMemory) {
            //save position into cookie
        }
    },

    onDragEnd: function(element)
    {
        if(this.enableMemory && this.type == PROTOTOOLBAR_TYPE_PALETTE) {
            //save position into cookie
            var posArr = { x:element.style.left,
                           y:element.style.top };

            this.protoMemory.write("position",protoToolkit.toJSON(posArr));
        }
    },

    getDefaultPosition: function()
    {
        var x = 10;
        var y = 20;

        var eW = 300;
        var topPadding = this.padding[0];
        var middle = getViewportSize()[0]/2 + getScrollXY()[0] - eW/2;

//        var ow = this.toolbar.offsetWidth;
//        var oh = this.toolbar.offsetHeight;

        y = getScrollXY()[1] + topPadding;
        x = middle;

        return [x,y];
    },

    create_logo_element: function( p_data )
    {
        var link_element = document.createElement("a");
        link_element.setAttribute('href', p_data.href);
        if(p_data.classname) {
            link_element.setAttribute('class', p_data.classname);
        }
        if(p_data.target) {
            link_element.setAttribute('target', p_data.target);
        }

        var image_element = document.createElement("img");
        image_element.setAttribute('src', p_data.src);
        image_element.setAttribute('alt', p_data.alt);
        image_element.setAttribute('width', p_data.width);
        image_element.setAttribute('height', p_data.height);

        link_element.appendChild(image_element);

        return link_element;
    },

    create_item_element: function( p_data, p_level )
    {
        if(p_level == undefined || p_level == 0) {
            var item_holder = document.createElement("div");
            item_holder.setAttribute('class', 'item');
            item_holder.setAttribute('id', 'item-'+p_data.title);

            item_holder.onmouseover = function() { this.set_item_display( item_holder ) }.bind(this);
            item_holder.onmouseout = function() { this.set_item_display( item_holder, 'none' ) }.bind(this);

            //Event.observe(item_holder, "mouseover", this.event_ItemHover);
            //Event.observe(item_holder, "mouseout", this.event_ItemHover);

            var root_item = document.createElement("h1");
            if(p_data.href != undefined && p_data.href != '') {
                var link_element = document.createElement("a");
                link_element.setAttribute('href', p_data.href);
                if(p_data.classname) {
                    link_element.setAttribute('class', p_data.classname);
                }
                if(p_data.target) {
                    link_element.setAttribute('target', p_data.target);
                }

                link_element.innerHTML = p_data.title;
                root_item.appendChild(link_element);
            } else {
                root_item.innerHTML = p_data.title;
            }

            item_holder.appendChild(root_item);
            if(p_data.children != undefined && p_data.children.length>0) {
                item_holder.appendChild(this.create_item_element(p_data,1));
            }
        } else {
            var item_holder = document.createElement("ul");
            for(var i=0;i<p_data.children.length;i++) {
                var lastitem = (i==p_data.children.length-1)?true:false;
                var item = this.create_subitem_element( p_data.children[i], lastitem );
                item_holder.appendChild(item);
            }
        }

        return item_holder;
    },

    create_subitem_element: function( p_data, p_lastitem )
    {
        var subitem = document.createElement('li');
        var classSuffix = p_lastitem?' last_item':'';
        if(p_data.classname) {
            subitem.setAttribute('class',p_data.classname+classSuffix);
        } else {
            subitem.setAttribute('class','enabled'+classSuffix);
        }

        if(p_data.special != undefined && p_data.special != '') {
            if(p_data.special == 'hr') {
                subitem.setAttribute('class', 'separator');
                subitem.innerHTML = '<hr />';
            }
        }

        if(p_data.href != undefined && p_data.href != '' && p_data.content == '') {
            var link_element = document.createElement("a");
            link_element.setAttribute('href', p_data.href);
            if(p_data.target) {
                link_element.setAttribute('target', p_data.target);
            }

            if(p_data.image != undefined && p_data.image != '') {
                var imagesrc = this.imagepath + p_data.image;
                var img_element = document.createElement("img");
                img_element.setAttribute('src',imagesrc);
                img_element.setAttribute('alt',p_data.title);
                img_element.setAttribute('title',p_data.title);
                img_element.setAttribute('border','0');
                img_element.setAttribute('align','left');
                link_element.appendChild(img_element);
            }

            link_element.appendChild(img_element);
            link_element.innerHTML += p_data.title;
            subitem.appendChild(link_element);
        }

        if(p_data.content != undefined && p_data.content != '') {
            subitem.innerHTML = p_data.content;
        }

        return subitem;
    },

    set_item_display: function( p_item, p_display )
    {
        var display = p_display?p_display:'block';

        var item = document.getElementById(p_item.id);
        var listul = item.getElementsByTagName('ul');

        if(listul[0] != undefined && listul[0] != null) {
            listul[0].style.display = display;
        }
    },

    bodyreader: function( p_id, p_recursive )
    {
        var recursive = p_recursive || false;
        //var element = document.getElementById(p_id);
        if(recursive) {
            if(this.recursiveHack = true) {
                this.debug("bodyreader recursive (hack):" + p_id);

                Object.extend(String.prototype, {
                  strip: function() {
                    return this.replace(/^\s+/, '').replace(/\s+$/, '');
                  }
                });

                var Selector = Class.create();
                Selector.prototype = {
                  initialize: function(expression) {
                    this.params = {classNames: []};
                    this.expression = expression.toString().strip();
                    this.parseExpression();
                    this.compileMatcher();
                  },
                
                  parseExpression: function() {
                    function abort(message) { throw 'Parse error in selector: ' + message; }
                
                    if (this.expression == '')  abort('empty expression');
                
                    var params = this.params, expr = this.expression, match, modifier, clause, rest;
                    while (match = expr.match(/^(.*)\[([a-z0-9_:-]+?)(?:([~\|!]?=)(?:"([^"]*)"|([^\]\s]*)))?\]$/i)) {
                      params.attributes = params.attributes || [];
                      params.attributes.push({name: match[2], operator: match[3], value: match[4] || match[5] || ''});
                      expr = match[1];
                    }
                
                    if (expr == '*') return this.params.wildcard = true;
                
                    while (match = expr.match(/^([^a-z0-9_-])?([a-z0-9_-]+)(.*)/i)) {
                      modifier = match[1], clause = match[2], rest = match[3];
                      switch (modifier) {
                        case '#':       params.id = clause; break;
                        case '.':       params.classNames.push(clause); break;
                        case '':
                        case undefined: params.tagName = clause.toUpperCase(); break;
                        default:        abort(expr.inspect());
                      }
                      expr = rest;
                    }
                
                    if (expr.length > 0) abort(expr.inspect());
                  },
                
                  buildMatchExpression: function() {
                    var params = this.params, conditions = [], clause;
                
                    if (params.wildcard)
                      conditions.push('true');
                    if (clause = params.id)
                      conditions.push('element.id == ' + clause.inspect());
                    if (clause = params.tagName)
                      conditions.push('element.tagName.toUpperCase() == ' + clause.inspect());
                    if ((clause = params.classNames).length > 0)
                      for (var i = 0; i < clause.length; i++)
                        conditions.push('Element.hasClassName(element, ' + clause[i].inspect() + ')');
                    if (clause = params.attributes) {
                      clause.each(function(attribute) {
                        var value = 'element.getAttribute(' + attribute.name.inspect() + ')';
                        var splitValueBy = function(delimiter) {
                          return value + ' && ' + value + '.split(' + delimiter.inspect() + ')';
                        }
                
                        switch (attribute.operator) {
                          case '=':       conditions.push(value + ' == ' + attribute.value.inspect()); break;
                          case '~=':      conditions.push(splitValueBy(' ') + '.include(' + attribute.value.inspect() + ')'); break;
                          case '|=':      conditions.push(
                                            splitValueBy('-') + '.first().toUpperCase() == ' + attribute.value.toUpperCase().inspect()
                                          ); break;
                          case '!=':      conditions.push(value + ' != ' + attribute.value.inspect()); break;
                          case '':
                          case undefined: conditions.push(value + ' != null'); break;
                          default:        throw 'Unknown operator ' + attribute.operator + ' in selector';
                        }
                      });
                    }
                
                    return conditions.join(' && ');
                  },
                
                  compileMatcher: function() {
                    this.match = new Function('element', 'if (!element.tagName) return false; \
                      return ' + this.buildMatchExpression());
                  },
                
                  findElements: function(scope) {
                    var element;
                
                    if (element = $(this.params.id))
                      if (this.match(element))
                        if (!scope || Element.childOf(element, scope))
                          return [element];
                
                    scope = (scope || document).getElementsByTagName(this.params.tagName || '*');
                
                    var results = [];
                    for (var i = 0; i < scope.length; i++)
                      if (this.match(element = scope[i]))
                        results.push(Element.extend(element));
                
                    return results;
                  },
                
                  toString: function() {
                    return this.expression;
                  }
                };

                function $$() {
                  return $A(arguments).map(function(expression) {
                    return expression.strip().split(/\s+/).inject([null], function(results, expr) {
                      var selector = new Selector(expr);
                      return results.map(selector.findElements.bind(selector)).flatten();
                    });
                  }).flatten();
                };

                var element = $$(p_id);
            } else {
                this.debug("bodyreader recursive:" + p_id);
                var element = $$(p_id);
            }
        } else {
            this.debug("bodyreader search:" + p_id);
            var element = $('#'+p_id);
            if(element == null || element == undefined)
                var element = document.getElementById(p_id);
        }
        //this.debug("bodyreader result:" + element);
        return element;
    },

    isMemoryEnabled: function()
    {
    },
    
    getMemoryContents: function()
    {
    },



    debug: function(p_msg, p_append, p_type)
    {
        if(p_append == undefined) {
            p_append = false;
        }

        if(this.debugON) {
            debug(p_msg, p_append);
        }
    }
};

function set_item_display( p_item, p_display )
{
    var p_display = p_display!=undefined&&p_display!=""?p_display:'block';
    var item = document.getElementById(p_item.id);
    
    var listul = item.getElementsByTagName('ul');
    if(listul[0] != undefined && listul[0] != null) {
        listul[0].style.display = p_display;
    }
}
