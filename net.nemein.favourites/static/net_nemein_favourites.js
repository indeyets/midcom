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
            show_activity: false
        }
    };
    
    $.net.nemein.favourites.controller = function(holder, options) {        
        var _self = this;
        var fav_btn = $('.'+options.classes.fav_btn, holder);
        var bury_btn = $('.'+options.classes.bury_btn, holder);
        var favs_cnt = $('.'+options.classes.favs_count, holder);
        var bury_cnt = $('.'+options.classes.bury_count, holder);

        $.meta.setType("class");

        var data = $(holder).data();

        update_view(data);

        function execute(action, url) {
            $.ajax({
                url: url,
                type: "POST",
                global: false,
                cache: false,
                dataType: "json",
                contentType: 'application/json',
                processData: false,
                error: function(req) {
                    return false;
                },
                success: function(data) {
                    update_view(data, action);
                    return true;
                }
            });
        }

        function update_view(data, action) {
            if (typeof action == 'undefined') {
                action = false;
            }

            if (typeof data['favs'] != 'undefined') {
                favs_cnt.html(data.favs.toString());            
            }
            if (typeof data['buries'] != 'undefined') {
                bury_cnt.html(data.buries.toString());
            }

            if (   data.can_fav
                && options.favs_enabled)
            {
                fav_btn.bind("click", function(){        
                    execute('fav', data.fav_url);
                });        
            } else {
                fav_btn.unbind("click");
            }

            if (   data.can_bury
                && options.bury_enabled)
            {
                bury_btn.bind("click", function(){
                    execute('bury', data.bury_url);
                });
            } else {
                bury_btn.unbind("click");
            }
        }

    };

})(jQuery);

// To use this on site, following must be included in the header:
/*
<script type="text/javascript" src="<?php echo MIDCOM_STATIC_URL;?>/jQuery/jquery.metadata.js"></script>
<script type="text/javascript" src="<?php echo MIDCOM_STATIC_URL;?>/net.nemein.favourites/net_nemein_favourites.js"></script>
<script type="text/javascript">
    jQuery(document).ready(function(){
        jQuery('.net_nemein_favourites').net_nemein_favourites();
    });
</script>
*/

// Example html structure to use:
/*
<div class='net_nemein_favourites <?php echo net_nemein_favourites_admin::get_json_data($data['article']->__new_class_name__, $data['article']->guid, '/favourites/');?>'>
    <div class="fav_btn">Fav</div>
    <div class="bury_btn">Bury</div>
    <span class="favs_count">0</span>
    <span class="bury_count">0</span>
</div>
*/