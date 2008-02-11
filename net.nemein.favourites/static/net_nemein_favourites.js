(function($){
    $.net = typeof($.net) == 'undefined' ? {} : $.net;
    $.net.nemein = typeof($.net.nemein) == 'undefined' ? {} : $.net.nemein;

    $.fn.extend({
        net_nemein_favourites: function(options)
        {
            options = jQuery.extend({}, $.net.nemein.favourites.defaults, options || {});
            return this.each(function() {
                return new $.net.nemein.favourites.controller(this, options);
            });
        },
        net_nemein_favourites_execute: function(options, action, guid, url)
        {
            $.net.nemein.favourites.execute(options, action, guid, url);
        }        
    });
    
    $.net.nemein.favourites = {
        defaults: {
            favs_enabled: true,
            bury_enabled: true,
            classes: {
                fav_btn: 'fav_btn',
                bury_btn: 'bury_btn',
                favs_count: 'favs_count',
                bury_count: 'bury_count'
            },
            show_activity: false,
            force_ssl: false
        },
        executed_list: {},
        execute: function() {
            var options = jQuery.extend({}, $.net.nemein.favourites.defaults, arguments[0] || {});
            var url = arguments[3];
            var action = arguments[1];            
            var btn_class = options.classes[action+'_btn'];
            var do_post = true;
            
            var base_url = url.substr(0,url.indexOf('/json')) + '/';
            
            if (typeof $.net.nemein.favourites.executed_list[url] != 'undefined') {
                return false;
            }
            $.net.nemein.favourites.executed_list[url] = true;
            
            if (typeof arguments[2] == 'string') {
                var guid = arguments[2];
                
                var loading_class = btn_class + '_loading';
                var button = $('#net_nemein_favourites_for_'+guid+' .'+btn_class);

                button.addClass(loading_class);
                $.ajax({
                    url: ''+url+'?net_nemein_favourites_topic_url='+base_url,
                    type: "POST",
                    global: false,
                    cache: false,
                    dataType: "json",
                    contentType: 'application/json',
                    processData: false,
                    error: function(req) {
                        button.removeClass(loading_class);
                        return false;
                    },
                    success: function(data) {
                        button.removeClass(loading_class);
                        $.net.nemein.favourites.update_view(options, data, guid);
                        return true;
                    }
                });
            } else {
                var button = arguments[2];
                var holder = arguments[4];
                var loading_class = options.classes[action+'_btn'] + '_loading';

                if (   options.force_ssl
                    && window.location.protocol != 'https:')
                {
                    do_post = false;

                    var current_url = '' + (window.location).toString().split('?')[0];
                    current_url = current_url.replace(/http/, 'https');

                    var url_parts = url.split('/');                
                    var guid = url_parts[url_parts.length-1];
                    if (guid == '') {
                        guid = url_parts[url_parts.length-2];
                    }

                    window.location.href = current_url + '?net_nemein_favourites_execute='+action+'&net_nemein_favourites_execute_for='+guid+'&net_nemein_favourites_url='+url;

                    return true;
                }
                
                if (do_post) {
                    button.addClass(loading_class);

                    $.ajax({
                        url: ''+url+'?net_nemein_favourites_topic_url='+base_url,
                        type: "POST",
                        global: false,
                        cache: false,
                        dataType: "json",
                        contentType: 'application/json',
                        processData: false,
                        error: function(req) {
                            button.removeClass(loading_class);
                            return false;
                        },
                        success: function(data) {
                            button.removeClass(loading_class);
                            $.net.nemein.favourites.update_view(options, data, holder);
                            return true;
                        }
                    });
                }
            }
        },
        update_view: function(options, data, holder) {
            if (typeof holder == 'string') {
                var holder = $('#net_nemein_favourites_for_'+holder);
            }

            var fav_btn = $('.'+options.classes.fav_btn, holder);
            var bury_btn = $('.'+options.classes.bury_btn, holder);
            var favs_cnt = $('.'+options.classes.favs_count, holder);
            var bury_cnt = $('.'+options.classes.bury_count, holder);
            

            if (typeof data['favs'] != 'undefined') {
                favs_cnt.html(data.favs.toString());            
            }
            if (typeof data['buries'] != 'undefined') {
                bury_cnt.html(data.buries.toString());
            }

            if (   data.can_fav
                && options.favs_enabled)
            {
                fav_btn.removeClass(options.classes.fav_btn+'_disabled');
                favs_cnt.removeClass(options.classes.favs_count+'_disabled');
                fav_btn.bind("click", function(){        
                    return $.net.nemein.favourites.execute(options, 'fav', fav_btn, data.fav_url, holder);
                });
                fav_btn.mouseover(function(){
                    fav_btn.addClass(options.classes.fav_btn+'_hover');
                });
                fav_btn.mouseout(function(){
                    fav_btn.removeClass(options.classes.fav_btn+'_hover');
                });
            } else {
                fav_btn.unbind("click");
                fav_btn.addClass(options.classes.fav_btn+'_disabled');
                favs_cnt.addClass(options.classes.favs_count+'_disabled');
            }

            if (   data.can_bury
                && options.bury_enabled)
            {
                bury_btn.removeClass(options.classes.bury_btn+'_disabled');
                bury_cnt.removeClass(options.classes.bury_count+'_disabled');
                bury_btn.bind("click", function(){
                    return $.net.nemein.favourites.execute(options, 'bury', bury_btn, data.bury_url, holder);
                });
                bury_btn.mouseover(function(){
                    bury_btn.addClass(options.classes.bury_btn+'_hover');
                });
                bury_btn.mouseout(function(){
                    bury_btn.removeClass(options.classes.bury_btn+'_hover');
                });
            } else {
                bury_btn.unbind("click");
                bury_btn.addClass(options.classes.bury_btn+'_disabled');
                bury_cnt.addClass(options.classes.bury_count+'_disabled');
            }
        }
    };
    
    $.net.nemein.favourites.controller = function(holder, options) {
        $.meta.setType("class");
        var data = $(holder).data();

        $.net.nemein.favourites.update_view(options, data, holder);
    };

})(jQuery);

// To use this on site, something like the following must be included in the header:
/*
<?php
$_MIDCOM->componentloader->load_graceful('net.nemein.favourites');
net_nemein_favourites_admin::get_ajax_headers();
?>
*/

// Default html structure to use:
/*
<div id="net_nemein_favourites_for_<?php echo $data['article']->guid; ?>" class='net_nemein_favourites <?php echo net_nemein_favourites_admin::get_json_data($data['article']->__new_class_name__, $data['article']->guid, '/favourites/');?>'>
    <div class="fav_btn"><span class="favs_count">0</span></div>
    <div class="bury_btn"><span class="bury_count">0</span></div>
    <div class="net_nemein_favourites_clearfix"></div>
</div>
*/