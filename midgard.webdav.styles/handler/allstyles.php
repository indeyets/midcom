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
class midgard_webdav_styles_handler_allstyles  extends midgard_webdav_styles_handler
{

    /**
     * Simple default constructor.
     */
    function midgard_webdav_styles_handler_allstyles()
    {
        parent::__construct();
    }

    /**
     * Handles files
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_styles ( $handler_id, $args, &$data )
    {
        $this->log(__CLASS__ . ": styles handler");
           $server = new midgard_webdav_styles_dav_style();
        $server->set_style( $this->get_root_style( ) ) ;
        if (sizeof($args) == 1 && preg_match("/\.php$/",$args[0]))
        {
            $filename = $args[0];
            $this->log(__CLASS__ . ": filename " . $filename);
            $server = new midgard_webdav_styles_dav_element( );
            $server->set_element_name( $filename );
            $server->set_style( $this->get_root_style( ) ) ;
        }
        else
        {
            $this->walk_style_tree($args,$server);
        }
            $server->ServeRequest();
        return true;
    }

    /**
     * The handler for the allstyles article.
     * @param mixed $handler_id the array key from the requestarray
     * @param array $args the arguments given to the handler
     *
     */
    function _handler_allstyles_index ($handler_id, $args, &$data)
    {
        //$_MICOM->auth->require_admin_user();
           $server = new midgard_webdav_styles_dav_style();
        $style = new midcom_db_style;
        $style->id = 0;
        $server->set_style( $style );
        if (sizeof($args) > 0 )
        {
            $this->walk_style_tree($args,$server);
        }
           $server->ServeRequest();
        return true;
    }

    function _show_allstyles_index(  ) {
    }

    /**
     * Showfunctions are not in use
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_allstyles($handler_id, &$data)
    {
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_styles($handler_id, &$data)
    {
    }


    /**
     * Handles substyles
     */
    function _handler_allstyles ( $handler_id, $args, &$data )
    {
        $server = new midgard_webdav_styles_dav_style( );
        $filename = $args[0];
        $server->set_style( $this->get_style( $filename ) ) ;
        $server->ServeRequest();
        return true;

    }

    function walk_style_tree( $args , &$server)
    {
        $this->log(__CLASS__ . "::walk_style_tree, current style: " . $server->style->id);
        $this->log($args);
        $filename = array_shift($args);
        $server->set_style( $this->get_style( $filename, $server->style->id ) ) ;
        foreach ($args as $filename)
        {
            $this->log(__CLASS__ . "::walk_style_tree, current style: " . $server->style->id);
            if ( preg_match("/\.php$/",$filename))
            {
                $style = $server->style;
                   $server = new midgard_webdav_styles_dav_element( );
                   $server->set_element_name( $filename );
                   $server->set_style($style);
            }
            else
            {
                $server->set_style($this->get_style( $filename , $server->style->id));
            }
        }
    }


    function get_style( $filename , $parent_style = 0) {
        $filename = str_replace("+"," ",$filename);
        $this->log(__CLASS__ . ": get_style: " . $filename . " parent style: " . $parent_style);
        $qb = midcom_db_style::new_query_builder(  );
        $qb->add_constraint( 'name' , "=", $filename );
        $qb->add_constraint( 'up', '=' , $parent_style );
        $style = array_shift( $qb->execute() );
        if ( !$style )
        {
            return false;
        }
        $this->log(__CLASS__ . ": found style " . $style->name);
        return $style;
/*
        foreach ( $args as $arg ) {

        }
        $style = new midcom_db_style(  );
        $style = get_by_path( $path );
        if ( !$style ) var_dump( $path );
        var_dump( $style );
        var_dump( $path );
        return $style;
*/
    }
}

?>