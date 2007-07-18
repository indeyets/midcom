<?php

/**
 * Featured index page handler
 *
 * @package net.nemein.featured
 */
class net_nemein_featured_handler_view extends midcom_baseclasses_components_handler
{
    var $_content_topic = null;

    var $_featured_objects = Array();

    /**
    * Simple default constructor.
    */
    function net_nemein_featured_handler_view()
    {
        parent::midcom_baseclasses_components_handler();
    }

    function _on_initialize()
    {
        $this->_content_topic =& $this->_request_data['content_topic'];
    }

    function _handler_view($handler_id, $args, &$data)
    {
        $qb = net_nemein_featured_item_dba::new_query_builder();
	$qb->add_constraint('topicGuid', '=', $this->_content_topic->guid);
        $qb->add_order('metadata.score', 'ASC');

        $featured_objects = $qb->execute();

        if (!$featured_objects)
	{
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('Failed to get any featured objects');
	    debug_pop();
	}
	else
	{
            foreach($featured_objects as $featured)
	    {
	        $this->_featured_objects[$featured->groupName][] = $featured;
	    }
	}

        return true;
    }

    function _show_view($handler_id, &$data) 
    {
       $this->_request_data['featured_objects'] = $this->_featured_objects;

	midcom_show_style('show_featured');
    }
}

?>
