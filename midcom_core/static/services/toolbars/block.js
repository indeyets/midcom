/**
 * @package midcom_core
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

(function($){
    
    $.midcom.services.toolbars = $.midcom.services.toolbars || {};
    
    $.midcom.services.toolbars.block = function(id, holder, config) {
        this._id = id;
        
        $.midcom.services.toolbars.config.type_config[$.midcom.services.toolbars.TYPE_BLOCK] = {
            height: 25,
            width: 0,
            draggable: false,
            class_name: 'midcom_services_toolbars_block',
        };
        
        this.holder = holder;
        this.config = $.midcom.services.configuration.merge(
            $.midcom.services.toolbars.config,
            config
        );
        
        $.midcom.logger.log('midcom.services.toolbars.block inited');
        $.midcom.logger.debug(this.config);
        
        this.show();
        
        $.midcom.events.signals.trigger('midcom.services.toolbars::block-ready');
    };
    $.extend($.midcom.services.toolbars.block.prototype, {
        show: function() {
            this.holder.show();
        }
    });

})(jQuery);