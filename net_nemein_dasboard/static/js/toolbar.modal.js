jQuery(function($) {
    $.midcom_toolbar_modal = function(options) {
        // TODO:
        // 1. Make it check whether the modal already exists or not and then just show it or append anew
        // 2. Better controls
        var defaults = {
            json_url: '/mgd:actions/',
            guid: ''
        };
        var opts = $.extend({}, defaults, options);
        
        var $content = $('<div />').attr('class', 'modal:content');
        var $close = $('<a />').addClass('close').attr({href: '#close'}).text('Close');
        var $overlay = $('<div />').attr('id', 'modal:overlay').hide();
        var $container = $('<div />').attr('id', 'modal:container').hide();
        
        $('body').append(
            $overlay, $container.show().css({
                'top': Math.round(($(window).height() - $container.outerHeight()) / 4) + 'px',
                'left': Math.round(($(window).width() - $container.outerWidth()) / 4) + 'px',
                'margin-top': 0,
                'margin-left': 0
            }).hide()
        );
        
        $close.click(function() {
            $overlay.add($container).fadeOut('normal').remove();
            $container.remove();
            return false;
        });
        url = opts.json_url + opts.guid + '.json';
        console.log(url);
        ///mgd:actions/<guid>.json
        $.getJSON(url,{}, 
            function (json) {
                action_count = json.length;
                for (key in json.actions)
                {
                    $content.append(
                            $('<a />').attr('href', json.actions[key].url ).css('background-image','/'+json.actions[key].icon).text(json.actions[key].label)
                        );
                }
                $container.append($close, $content).show();
                $overlay.show();
        });
    };
});