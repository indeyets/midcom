<?php

/**
 * Forum AIS interface class.
 * 
 * @package net.nemein.favourites
 */
class net_nemein_favourites_admin extends midcom_baseclasses_components_request_admin
{
    var $_content_topic = null;

    function net_nemein_favourites_admin($topic, $config) 
    {
         parent::midcom_baseclasses_components_request_admin($topic, $config);
    }

    function _on_initialize()
    {
        $this->_content_topic = $this->_request_data['content_topic'];
        return true;
    }

    function render_add_link($objectType, $guid)
    {
        if (!empty($objectType) && !empty($guid))
	{ 
	    $node = midcom_helper_find_node_by_component('net.nemein.favourites');
	    if (empty($node))
	    {
                return false;
	    }
	    else
	    {
	        $url = $node[MIDCOM_NAV_FULLURL];
                echo "<a href=\"{$url}create/{$objectType}/{$guid}.html\" class=\"net_nemein_favourites_create\">Add to favourites</a>";
                return true;
	    }
	}
    }
}

?>
