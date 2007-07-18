<?php

/**
 * Featured AIS interface class.
 * 
 * @package net.nemein.featured
 */
class net_nemein_featured_admin extends midcom_baseclasses_components_request_admin
{
    var $_content_topic = null;

    function net_nemein_featured_admin($topic, $config) 
    {
         parent::midcom_baseclasses_components_request_admin($topic, $config);
    }

    function _on_initialize()
    {
        $this->_content_topic = $this->_request_data['content_topic'];
        return true;
    }

    function get_groups()
    {
        $data =& $_MIDCOM->get_custom_context_data('request_data');
        $config_groups = $data['config']->get('groups');

	$groups = array();

	foreach($config_groups as $name => $group)
	{
            $groups[$name] = $group['title'];
	}

        return  $groups;  //array('info' => 'Info', 'video' => 'Video');
    }

}

?>
