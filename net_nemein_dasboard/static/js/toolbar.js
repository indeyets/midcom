jQuery(function($) {
    $.midcom_toolbar = function(options) {
        var defaults = {
            
        };
        
        var opts = $.extend({}, defaults, options);
        
        init(opts);
        
        function init(opts)
        {
            $root = $('<div />').attr('id', 'toolbar').append("<div id='toolbar:toggle'></div><ul id='toolbar:tabs'><li><a href='#toolbar:editing'>Editing</a></li></ul><div id='toolbar:actions'><div id='toolbar:editing'></div></div>").hide();
            $('body').append($root);
            
            $toolbar = $('#toolbar');
            var $toggler = $('#toolbar\\:toggle').fadeTo(0, 0.25);
            $tab_container = $('#toolbar\\:tabs').hide();
            $tabs = $('#toolbar\\:tabs a');
            $action_container = $("#toolbar\\:actions");
            $actions = $("#toolbar\\:actions > div").hide();
            $editing = $actions.filter('#toolbar\\:editing');

            $elements = $('[mgd\\:guid]');
            // editables = '';
            // console.time('concat');
            // for (var i = $elements.length - 1; i >= 0; i--){
            //     editables += '<a class='+$elements[i].getAttribute('mgd:type')+' href='+$elements[i].getAttribute('mgd:guid')+'>'+$elements[i].getAttribute('mgd:label')+'</a>';
            // };
            // $editing.append(editables);
            // console.timeEnd('concat');

            var editables = [];
            var a = 0;
            for (var i = $elements.length - 1; i >= 0; i--){
                editables[a++] = '<a class="'+$elements[i].getAttribute('mgd:type');
                editables[a++] = '" href="';
                editables[a++] = $elements[i].getAttribute('mgd:guid');
                editables[a++] = '">';
                editables[a++] = $elements[i].getAttribute('mgd:label');
                editables[a++] = '</a>';
            };
            $editing.append(editables.join(''));
            
            $editables = $('a', $editing);
            
            $editables.click(function() {
                return false;
                $.getJSON('/mgd:actions/'+this.getAttribute('href')+'.json',{},
                 function(json){
                    
                });
            });

            
            $toggler.toggle(
                function() {
                    $tab_container.show();
                    $toggler.data('active', true);
                },
                function() {
                    $tab_container.hide();
                    $actions.hide();
                    $toggler.data('active', false);
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
                hash = this.hash.replace(/:/, "\\:");
                $actions.hide().filter(hash).show();
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
    };
        
    $.midcom_toolbar();
});