<?php
/**
 * @package midgard.webdav.styles
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is an URL handler class for midgard.webdav.styles
 *
 * The midcom_baseclasses_components_handler class defines a bunch of helper vars
 * See: http://www.midgard-project.org/api-docs/midcom/dev/midcom.baseclasses/midcom_baseclasses_components_handler.html
 * 
 * @package midgard.webdav.styles
 */
class midgard_webdav_styles_handler_index  extends midgard_webdav_styles_handler
{

    /**
     * Simple default constructor.
     */
    function midgard_webdav_styles_handler_index()
    {
        parent::midcom_baseclasses_components_handler();
        
    }    
    
    /**
     * The handler for the index article. 
     * @param mixed $handler_id the array key from the requestarray
     * @param array $args the arguments given to the handler
     * 
     */
    function _handler_index ($handler_id, $args, &$data)
    {
		$this->log(__CLASS__ . ": index_handler");
        $server = new midgard_webdav_styles_dav_style_index();
	    $server->set_style( $this->get_root_style());
        $server->ServeRequest();
        return true;
    }
    
    /**
     * Showfunctions are not in use
     */
    function _show_index($handler_id, &$data)
    {
    }
    
}
?>
