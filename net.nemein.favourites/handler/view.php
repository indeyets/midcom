<?php

/**
 * Favourites index page handler
 *
 * @package net.nemein.favourites
 */
class net_nemein_favourites_handler_view extends midcom_baseclasses_components_handler
{
    var $_content_topic = null;

    /**
    * Simple default constructor.
    */
    function net_nemein_favourites_handler_view()
    {
        parent::midcom_baseclasses_components_handler();
    }

    function _on_initialize()
    {
        $this->_content_topic =& $this->_request_data['content_topic'];
    }

    function _handler_view($handler_id, $args, &$data)
    {
        return true;
    }

    function _show_view($handler_id, &$data) 
    {
        midcom_show_style('show_index_header');
        midcom_show_style('show_index_item');
        midcom_show_style('show_index_footer');
    }
    
}

?>
