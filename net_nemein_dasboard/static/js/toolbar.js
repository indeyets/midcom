jQuery(function($) {
    $.midcom_toolbar = function(options) {
        var defaults = {
            static_url: '/midcom-static/net_nemein_dasboard',
            toggle_active_class: 'active',
            json_url: '/mgd:actions/',
        };
        
        var opts = $.extend({}, defaults, options);
        
        init(opts);
        
        function init(opts)
        {
            // Include related files
            $('<link />').attr({rel: 'stylesheet', type: 'text/css', href: opts.static_url+'/css/toolbar.css'}).appendTo('head');
            $('<link />').attr({rel: 'stylesheet', type: 'text/css', href: opts.static_url+'/css/toolbar.modal.css'}).appendTo('head');
            $('<link />').attr({rel: 'stylesheet', type: 'text/css', href: opts.static_url+'/css/toolbar.highlight.css'}).appendTo('head');
            $.getScript(opts.static_url+'/js/toolbar.modal.js');
            $.getScript(opts.static_url+'/js/toolbar.highlight.js');
            
            
            $root = $('<div />').attr('id', 'toolbar').append("<div id='toolbar:toggle'></div><ul id='toolbar:tabs'><li><a href='#toolbar_items'>items</a></li></ul><div id='toolbar:actions'><div id='toolbar_items'></div></div>").hide();
            $('body').append($root);
            
            $toolbar = $('#toolbar');
            var $toggler = $('#toolbar\\:toggle').fadeTo(0, 0.25);
            $tab_container = $('#toolbar\\:tabs').hide();
            $action_container = $("#toolbar\\:actions");
            $actions = $("#toolbar\\:actions > div").hide();
            $editing = $actions.filter('#toolbar_items');
            $tabs = $('#toolbar\\:tabs li a');
            $elements = $('[mgd\\:guid]');

            var editables = [];
            var a = 0;
            var elcount = $elements.length;
            for (var i = 0; i < elcount; i++){
                editables[a++] = '<a class="';
                editables[a++] = $elements[i].getAttribute('mgd:type');
                editables[a++] = '" href="';
                editables[a++] = $elements[i].getAttribute('mgd:guid');
                editables[a++] = '">';
                editables[a++] = $elements[i].getAttribute('mgd:label');
                editables[a++] = '</a>';
            };
            $editing.append(editables.join(''));

            $editables = $('a', $editing);

            $editables.click(function() {
                objguid = this.getAttribute('href');
                $.midcom_toolbar_modal({ guid: objguid });
                return false;
            }).hover(function() {
                $(this).highlight({state: "on" });
            }, function() {
                $(this).highlight({state: "off" });
            });

            // At this point, we are interested in all the midgard pages since they can add actions to the toolbar
            $pages = $("[mgd\\:type^='midgard_page']");

            $.getJSON(opts.json_url+'categories.json',{},
            function(json){
                for (key in json.categories)
                {
                    $('<li></li>').append( 
                        $tab = $('<a />').attr("href", "#toolbar_"+json.categories[key]).text(json.categories[key])
                    ).appendTo($tab_container);
                    $tabs = $tabs.add($tab);
                    
                    $category_actions = $('<div />').attr("id", "toolbar_"+json.categories[key]).hide().appendTo($action_container);
                    $actions = $actions.add($category_actions);
                    
                    for (var i=0; i < $pages.length; i++) {
                        guid = $pages[i].getAttribute('mgd:guid');
                        url = opts.json_url+guid+'/'+json.categories[key]+'.json';
                        $.ajax({
                            type: 'GET',
                            url: url,
                            dataType: 'json',
                            success: function (json) {
                                for (action in json.actions)
                                {
                                    $('<a />').attr("href", json.actions[action].url).text(json.actions[action].label).css('background-image', "url('/midcom-static/"+json.actions[action].icon+"')").appendTo($category_actions);
                                }
                            },
                            data: {},
                            async: false
                        });                        
                    };
                }
            });
                        
            $toggler.toggle(
                function() {
                    // TODO: Convert this in to a filter that opens the first tab with a click.
                    last_tab = cookie('midcom_toolbar');
                    if (last_tab)
                    {
                        $tab_container.show().find("li a[href^='"+last_tab+"']").click()
                    }
                    else
                    {
                        $tab_container.show().find(':first a').click();
                    }                    
                    $toggler.data('active', true).addClass(opts.toggle_active_class);
                },
                function() {
                    $tab_container.hide();
                    $actions.hide();
                    $toggler.data('active', false).removeClass(opts.toggle_active_class);
                }
            ).hover(
                function() {
                    $toggler.fadeTo('fast', 1)
                }, 
                function() {
                    if ($toggler.data('active'))
                    {
                        // Do nothing
                    }
                    else
                    {
                        $toggler.fadeTo('fast', 0.25)                
                    }
                }
            );
            
            $tabs.live("click", function(event) {
                // TODO: Save the last clicked tab in to a cookie, possibly on window unload or on every click?
                hash = this.hash.replace(/:/, "\\:");
                $actions.hide().filter(hash).show();
                cookie('midcom_toolbar', hash);
                $tabs.removeClass('selected');
                $(this).addClass('selected');
                return false;
            });

            // Ready!
            $root.show();
        }
        
        // Utility methods
        function debug (msg)
        {
            console.log('debug: ' + msg);
        }
        
        function cookie(name, value, options)
        {
            if (typeof value != 'undefined') { // name and value given, set cookie
                options = options || {};
                if (value === null) {
                    value = '';
                    options.expires = -1;
                }
                var expires = '';
                if (options.expires && (typeof options.expires == 'number' || options.expires.toUTCString)) {
                    var date;
                    if (typeof options.expires == 'number') {
                        date = new Date();
                        date.setTime(date.getTime() + (options.expires * 24 * 60 * 60 * 1000));
                    } else {
                        date = options.expires;
                    }
                    expires = '; expires=' + date.toUTCString(); // use expires attribute, max-age is not supported by IE
                }
                // CAUTION: Needed to parenthesize options.path and options.domain
                // in the following expressions, otherwise they evaluate to undefined
                // in the packed version for some reason...
                var path = options.path ? '; path=' + (options.path) : '';
                var domain = options.domain ? '; domain=' + (options.domain) : '';
                var secure = options.secure ? '; secure' : '';
                document.cookie = [name, '=', encodeURIComponent(value), expires, path, domain, secure].join('');
            } else { // only name given, get cookie
                var cookieValue = null;
                if (document.cookie && document.cookie != '') {
                    var cookies = document.cookie.split(';');
                    for (var i = 0; i < cookies.length; i++) {
                        var cookie = jQuery.trim(cookies[i]);
                        // Does this cookie string begin with the name we want?
                        if (cookie.substring(0, name.length + 1) == (name + '=')) {
                            cookieValue = decodeURIComponent(cookie.substring(name.length + 1));
                            break;
                        }
                    }
                }
                return cookieValue;
            }
        }
    };
        
    $.midcom_toolbar({});
});