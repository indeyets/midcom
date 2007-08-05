/**
 * Live preview - jQuery plugin
 * Jerry Jalava <jerry.jalava@gmail.com>
 */

/**
 * Creates a live preview of input.
 * Currently supports client side rendering in plain text and markdown
 *   
 * @example jQuery('input[@type=textarea]').livePreview();
 * @cat plugin
 * @type jQuery 
 *
 */

jQuery.fn.livePreview = function(options) {
    options = jQuery.extend({
        previewClass: 'livePreview_holder',
        converter: 'markdown',
        markdown_lib: 'showdown.js',
        remote_url: null
    }, options);

    this.each(function(i,element) {
        var jq_object = jQuery(element);
        
        var rand_id = new Date().getTime() + "" + Math.floor(Math.random()*10);
        var preview_id = element.id || rand_id;
        
        jq_object.one('focus', function() {
            create_holder();
        });
        jq_object.bind('keyup',on_change);
        
        function create_holder()
        {
            jq_object.parent().after('<div id="livePreview_holder_for_' + preview_id + '" class="livePreview_holder ' + options.previewClass + '"><div id="livePreview_content_for_' + preview_id + '" class="livePreview_content"></div></div>');
        }
        
        function on_change()
        {
            content = jq_object.val();
            
            if (options.converter == 'markdown')
            {
                converter = new Showdown.converter();
                new_content = converter.makeHtml(content);
            }
            else
            {
            	content = content.replace(/\n/g, "<br />").replace(/\n\n+/g, '<br /><br />').replace(/(<\/?)script/g,"$1noscript");
                new_content = content;
            }

        	jQuery('#livePreview_content_for_' + preview_id).html( new_content );
        }
    });

};