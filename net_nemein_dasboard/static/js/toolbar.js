jQuery(function($) {
    $.midcom_toolbar = function(options) {
        var defaults = {
            static_url: '/midcom-static/net_nemein_dasboard',
            toggle_active_class: 'active',
        };
        
        var opts = $.extend({}, defaults, options);
        
        init(opts);
        
        function init(opts)
        {
            // Include related files
            $('<link />').attr({rel: 'stylesheet', type: 'text/css', href: opts.static_url+'/css/toolbar.css'}).appendTo('head');
            $('<link />').attr({rel: 'stylesheet', type: 'text/css', href: opts.static_url+'/css/toolbar.modal.css'}).appendTo('head');
            $.getScript(opts.static_url+'/js/toolbar.modal.js', function(){
                $modal = $.midcom_toolbar_modal;
            });
            
            $root = $('<div />').attr('id', 'toolbar').append("<div id='toolbar:toggle'></div><ul id='toolbar:tabs'><li><a href='#toolbar:items'>items</a></li></ul><div id='toolbar:actions'><div id='toolbar:items'></div></div>").hide();
            $('body').append($root);
            
            $toolbar = $('#toolbar');
            var $toggler = $('#toolbar\\:toggle').fadeTo(0, 0.25);
            $tab_container = $('#toolbar\\:tabs').hide();
            $tabs = $('#toolbar\\:tabs a');
            $action_container = $("#toolbar\\:actions");
            $actions = $("#toolbar\\:actions > div").hide();
            $editing = $actions.filter('#toolbar\\:items');
            
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

            debug('foo');
            
            $editables.click(function() {
                objguid = this.getAttribute('href');
                $modal({ guid: objguid });
                return false;
            });

            
            $toggler.toggle(
                function() {
                    // TODO: Convert this in to a filter that opens the first tab with a click.
                    $tab_container.show();
                    $actions.filter(':first').show();
                    $toggler.data('active', true).addClass(opts.toggle_class);
                },
                function() {
                    $tab_container.hide();
                    $actions.hide();
                    $toggler.data('active', false).removeClass(opts.toggle_class);
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
            
            $tabs.click(function() {
                // TODO: Save the last clicked tab in to a cookie, possibly on window unload or on every click?
                hash = this.hash.replace(/:/, "\\:");
                $actions.hide().filter(hash).show();
                $tabs.removeClass('selected');
                $(this).addClass('selected');
                return false;
            });
            
            debug('Go!');
            // Ready!
            $root.show();
        }
        
        // Utility methods
        function debug (msg)
        {
            console.log('debug: ' + msg);
        }
    };
        
    $.midcom_toolbar({});
});