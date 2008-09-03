<?php
/**
 * @package net.nemein.featured
 */

/**
 * Featured index page handler
 *
 * @package net.nemein.featured
 */
class net_nemein_featured_handler_view extends midcom_baseclasses_components_handler
{
    var $_content_topic = null;

    var $_featured_objects = Array();
    var $_featured_groups = array();

    /**
     * Simple default constructor.
     */
    function net_nemein_featured_handler_view()
    {
        parent::__construct();
    }

    function _on_initialize()
    {
        $this->_content_topic =& $this->_request_data['content_topic'];
        $this->_request_data['featured_objects'] =& $this->_featured_objects;
        $this->_request_data['featured_groups'] =& $this->_featured_groups;
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_view($handler_id, $args, &$data)
    {
        $this->_featured_groups = $this->_config->get('groups');

        $qb = net_nemein_featured_item_dba::new_query_builder();
        $qb->add_constraint('topicGuid', '=', $this->_content_topic->guid);
        $qb->add_order('metadata.score', 'ASC');

        if (   $handler_id == 'list'
            || $handler_id == 'list_top')
        {
            $qb->add_constraint('groupName', '=', $args[0]);

            foreach ($this->_featured_groups as $key => $group)
            {
                if ($key != $args[0])
                {
                    unset($this->_featured_groups[$key]);
                }
            }

            if ($handler_id == 'list_top')
            {
                $qb->set_limit($args[1]);
            }
        }

        $featured_objects = $qb->execute();
        foreach ($this->_featured_groups as $key => $group)
        {
            $this->_featured_objects[$key] = array();
        }

        foreach($featured_objects as $featured)
        {
            $this->_featured_objects[$featured->groupName][] = $featured;
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
        midcom_show_style('show_featured');
    }
}

?>