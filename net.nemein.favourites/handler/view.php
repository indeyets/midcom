<?php

/**
 * Favourites index page handler
 *
 * @package net.nemein.favourites
 */
class net_nemein_favourites_handler_view extends midcom_baseclasses_components_handler
{
    var $_content_topic = null;

    var $_favourite_objects = Array();

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
        // Getting favourite objects for the current user
	$qb = net_nemein_favourites_favourite_dba::new_query_builder();
	$qb->metadata_creator = $_MIDGARD['user'];
	$qb->add_order('metadata.created', 'DESC');

        $this->_favourite_objects = $qb->execute();
  
        return true;
    }

    function _show_view($handler_id, &$data) 
    {
        midcom_show_style('show_index_header');
        $this->_request_data['favourite_object'] = null;

        foreach ($this->_favourite_objects as $favourite_object)
        {
	    $this->_request_data['favourite_object'] = $favourite_object;
            midcom_show_style('show_index_item');
        }

	midcom_show_style('show_index_footer');
    }
    
}

?>
