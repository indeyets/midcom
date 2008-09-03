<?php
/**
 * @package net.nemein.favourites
 */

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
        parent::__construct();
    }

    /**
     * Load the paged query builder
     */
    function _on_initialize()
    {
        $_MIDCOM->load_library('org.openpsa.qbpager');
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_view($handler_id, $args, &$data)
    {
        // Getting favourite objects for the current user
        $qb = new org_openpsa_qbpager('net_nemein_favourites_favourite_dba', 'net_nemein_favourites');
        $data['qb'] =& $qb;
        $qb->add_constraint('metadata.creator', '=', $this->_request_data['user']->guid);
        $qb->add_constraint('bury', '=', false);
        $qb->add_order('objectType');
        $qb->add_order('metadata.created', 'DESC');
        $qb->results_per_page = (int) $this->_config->get('favourites_per_page');

        $favs = $qb->execute();
        foreach ($favs as $fav)
        {
            if (!isset($this->_favourite_objects[$fav->objectType]))
            {
                $this->_favourite_objects[$fav->objectType] = array();
            }
            $this->_favourite_objects[$fav->objectType][] = $fav;
        }

        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_view($handler_id, &$data)
    {
        midcom_show_style('show_index_header');

        foreach ($this->_favourite_objects as $type => $favs)
        {
            $data['type'] = $type;
            midcom_show_style('show_type_header');

            foreach ($favs as $favourite_object)
            {
                $data['favourite_object'] = $favourite_object;
                midcom_show_style('show_index_item');
            }

            midcom_show_style('show_type_footer');
        }

        midcom_show_style('show_index_footer');
    }

}

?>