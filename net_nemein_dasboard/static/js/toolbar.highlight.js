jQuery(function ($) {
    $.fn.highlight = function(options) {
        var defaults = {};
        var opts = $.extend(defaults, options);
        return this.each(function() {
            $this = $(this);
            $body = $('body');
            $target = $("[mgd\\:guid='"+$this.attr('href')+"']");
            // One element to contain all the overlays
            $overlay = $('#highlight\\:overlay').size() > 0 ? $('#highlight\\:overlay') : $('<div />').attr('id', 'highlight:overlay').hide().appendTo($body);
            $overlay_top = $('#highlight\\:overlay_top').size() > 0 ? $('#highlight\\:overlay_top') : $('<div />').attr('id', 'highlight:overlay_top').addClass('highlight_overlay').appendTo($overlay);
            $overlay_bottom = $('#highlight\\:overlay_bottom').size() > 0 ? $('#highlight\\:overlay_bottom') : $('<div />').attr('id', 'highlight:overlay_bottom').addClass('highlight_overlay').appendTo($overlay);
            $overlay_left = $('#highlight\\:overlay_left').size() > 0 ? $('#highlight\\:overlay_left') : $('<div />').attr('id', 'highlight:overlay_left').addClass('highlight_overlay').appendTo($overlay);
            $overlay_right = $('#highlight\\:overlay_right').size() > 0 ? $('#highlight\\:overlay_right') : $('<div />').attr('id', 'highlight:overlay_right').addClass('highlight_overlay').appendTo($overlay);
            $focused = $('#highlight\\:focused').size() > 0 ? $('#highlight\\:focused') : $('<div />').attr('id', 'highlight:focused').hide().appendTo($target);
            
            $focused.css({
                 top: $target.position().top,
                 left: $target.position().left,
                 width: $target.width() + 'px',
                 height: $target.height() + 'px'
            });
            
            var top = $target.position().top;
            var left = $target.position().left;
            var height = $target.height();
            var width = $target.width();
            
            $overlay_top.css({
                height: top + 'px'
            });
            
            $overlay_bottom.css({
                top: top + height,
                height: $body.height() - height + 'px'
            });
            
            $overlay_left.css({
                top: top,
                height: height + 'px',
                width: left + 'px'
            });
            
            $overlay_right.css({
                left: left + width,
                top: top,
                height: height + 'px'
            });
            
            if (opts.state == "on")
            {
                $overlay.fadeIn('200');
                // $overlay_top.fadeIn('200');
                // $overlay_bottom.fadeIn('200');
                // $overlay_left.fadeIn('200');
                // $overlay_right.fadeIn('200');
                $focused.fadeIn('400');                
            }
            else
            {
                $focused.hide();
                $overlay.hide();
                // $overlay_top.hide();
                // $overlay_bottom.hide();
                // $overlay_left.hide();
                // $overlay_right.hide();
            }
        });
    };
});