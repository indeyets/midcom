<?php

/**
 * Favourites
 *
 * @package net.nemein.favourites
 */
class net_nemein_favourites_viewer extends midcom_baseclasses_components_request
{
    var $_content_topic = null;

    function net_nemein_favourites_viewer($topic, $config)
    {
        parent::midcom_baseclasses_components_request($topic, $config);
    }

    function _on_initialize()
    {
	$this->_request_data['content_topic'] =& $this->_content_topic;

        // Request switches
        $this->_request_switch['create'] = Array
	(
	    'handler' => Array('net_nemein_favourites_handler_create', 'create'),
            'fixed_args' => Array('create'),
	    'variable_args' => 2,
	);
	$this->_request_switch['delete'] = Array
        (
	    'handler' => Array('net_nemein_favourites_handler_create', 'delete'),
	    'fixed_args' => Array('delete'),
            'variable_args' => 1,
        );
	
        $this->_request_switch['index'] = Array
	(
	    'handler' => Array('net_nemein_favourites_handler_view', 'view'),
	);
    }

    function _on_handle($handler_id, $args)
    {
        $_MIDCOM->auth->require_valid_user();
	
	$this->_content_topic = new midcom_db_topic($this->_topic->id);

	return true;
    }
}

?>
