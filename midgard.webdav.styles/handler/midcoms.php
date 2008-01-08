<?php
/**
 * @package midgard.webdav.styles
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is a URL handler class for midgard.webdav.styles
 *
 * The midcom_baseclasses_components_handler class defines a bunch of helper vars
 *
 * @see midcom_baseclasses_components_handler
 * @package midgard.webdav.styles
 */
class midgard_webdav_styles_handler_midcoms  extends midgard_webdav_styles_handler
{

    /**
     * Simple default constructor.
     */
    function midgard_webdav_styles_handler_midcoms()
    {
        parent::midcom_baseclasses_components_handler();
    }


    /**
     * The handler for the midcoms article.
     * @param mixed $handler_id the array key from the request array
     * @param array $args the arguments given to the handler
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_midcoms ($handler_id, $args, &$data)
    {

        $server = new midgard_webdav_styles_handler_midcoms_webdav();
        $server->ServeRequest();
        return true;
    }

    /**
     * Showfunctions are not in use
     */
    function _show_midcoms($handler_id, &$data)
    {
    }

    /**
     * A listing of styleelements
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_styleelements ( $handler_id, $args, &$data )
    {
        $midcom = $args[0];
 		$this->log(__CLASS__ . "styleelements_handler");
        $server = new midgard_webdav_styles_handler_midcoms_files( );
        $server->setMidcom( $midcom );
        $server->setStyle( $this->get_root_style( ) ) ;
        $server->ServeRequest();
        return true;

    }
    /**
     * one element.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_element ( $handler_id, $args, &$data ) {
        $midcom= $args[0];
        $element = $args[1];
        $server = new midgard_webdav_styles_handler_midcoms_element( );
        $server->setMidcom( $midcom );
        $server->set_element( $element );
        $server->ServeRequest( ) ;
        return true;
    }

    function get_root_style(  ) {
 		$this->log(__CLASS__ . ": get_root_style");
        $style = new midcom_db_style($_MIDGARD['style']);
        if (!$style->id)
		{
			return false;
		}
        return $style;
    }
}



?>
