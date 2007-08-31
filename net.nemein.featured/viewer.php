<?php

/**
 * Featured
 *
 * @package net.nemein.featured
 */
class net_nemein_featured_viewer extends midcom_baseclasses_components_request
{
    var $_content_topic = null;

    function net_nemein_featured_viewer($topic, $config)
    {
        parent::midcom_baseclasses_components_request($topic, $config);
    }

    function _on_initialize()
    {
        $this->_request_switch['create'] = Array
	(
	    'handler' => Array('net_nemein_featured_handler_featured', 'manage'),
            'fixed_args' => Array('create'),
	    'variable_args' => 2,
	);

	$this->_request_data['content_topic'] =& $this->_content_topic;

        // Request switches
        $this->_request_switch['manage'] = Array
	(
	    'handler' => Array('net_nemein_featured_handler_featured', 'manage'),
            'fixed_args' => Array('manage'),
	    //'variable_args' => 2,
	);
        $this->_request_switch['edit'] = Array
	(
	    'handler' => Array('net_nemein_featured_handler_featured', 'edit'),
            'fixed_args' => Array('edit'),
	    'variable_args' => 1,
	);
        $this->_request_switch['delete'] = Array
	(
	    'handler' => Array('net_nemein_featured_handler_featured', 'delete'),
            'fixed_args' => Array('delete'),
	    'variable_args' => 1,
	);
        $this->_request_switch['move_down'] = Array
	(
	    'handler' => Array('net_nemein_featured_handler_featured', 'move_down'),
            'fixed_args' => Array('move_down'),
	    'variable_args' => 1,
	);
        $this->_request_switch['move_up'] = Array
	(
	    'handler' => Array('net_nemein_featured_handler_featured', 'move_up'),
            'fixed_args' => Array('move_up'),
	    'variable_args' => 1,
	);
        $this->_request_switch['index'] = Array
	(
	    'handler' => Array('net_nemein_featured_handler_view', 'view'),
	);
    }

    function _populate_node_toolbar()
    {
        if ($this->_content_topic->can_do('midgard:create'))
	{
	    if (array_key_exists('schemadb', $this->_request_data))
	    {
	        $this->_node_toolbar->add_item(
		    Array(
		        MIDCOM_TOOLBAR_URL => "manage", 
			MIDCOM_TOOLBAR_LABEL => sprintf($this->_l10n->get('manage'), 
			    $this->_l10n->get($this->_request_data['schemadb']['default']->description)),		
		        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/new-text.png',
		        MIDCOM_TOOLBAR_ACCESSKEY => 'n',
		    )
		);
	    }
	}
    }

    function _on_handle($handler_id, $args)
    {
	$this->_content_topic = new midcom_db_topic($this->_topic->id);

        $this->_request_data['schemadb'] = midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb'));
	    
        $this->_populate_node_toolbar();

	return true;
    }
}

?>
