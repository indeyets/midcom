<?php

/**
 * Favourites index page handler
 *
 * @package net.nemein.favourites
 */
class net_nemein_favourites_handler_view extends midcom_baseclasses_components_handler
{
    var $_favourite_objects = Array();

    /**
    * Simple default constructor.
    */
    function net_nemein_favourites_handler_view()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * Load the paged query builder
     */
    function _on_initialize()
    {   
        $_MIDCOM->load_library('org.openpsa.qbpager');
    }

    function _handler_view($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
    
        // Getting favourite objects for the current user
        $qb = new org_openpsa_qbpager('net_nemein_favourites_favourite_dba', 'net_nemein_favourites');
        $data['qb'] =& $qb;
    	$qb->add_constraint('metadata.creator', '=', $_MIDCOM->auth->user->guid);
    	$qb->add_constraint('bury', '=', false);
    	$qb->add_order('metadata.created', 'DESC');
        $qb->results_per_page = (int) $this->_config->get('favourites_per_page');

        $this->_favourite_objects = $qb->execute();
  
        return true;
    }

    function _show_view($handler_id, &$data) 
    {
        midcom_show_style('show_index_header');
        $data['favourite_object'] = null;

        foreach ($this->_favourite_objects as $favourite_object)
        {
    	    $data['favourite_object'] = $favourite_object;
            midcom_show_style('show_index_item');
        }

    	midcom_show_style('show_index_footer');
    }
    
}

?>
