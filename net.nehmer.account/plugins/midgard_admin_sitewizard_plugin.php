<?php


/**
 * This is a plugin for creatin user home topic
 */
class midgard_admin_sitewizard_plugin extends midcom_baseclasses_components_handler
{
    var $_structure_config_path = '';
    var $_verbose = false;
    var $_home_name = '';
    var $_home_title = '';
    var $_creation_root_topic_guid = '';
    var $_creation_root_topic_parent_guid = '';
    var $_creation_root_group_guid = '';
    var $_creation_root_group_parent_guid ='';

   /**
    * Simple constructor, which only initializes the parent constructor.
    */
    function midgard_admin_sitewizard_plugin()
    {
	parent::midcom_baseclasses_components_handler();
	
    }

    function _on_initialize()
    {
        require_once($this->_request_data['plugin_config']['sitewizard_path']);

        parent::_on_initialize();

        $this->_structure_config_path = $this->_request_data['plugin_config']['structure_config_path'];

        if (isset($this->_request_data['plugin_config']['verbose']) && !empty($this->_request_data['plugin_config']['verbose']))
	{
            $this->_verbose = $this->_request_data['plugin_config']['verbose'];
	}

	if (isset($this->_request_data['plugin_config']['creation_root_topic_parent_guid']) 
	    && !empty($this->_request_data['plugin_config']['creation_root_topic_parent_guid']))
	{
            $this->_creation_root_topic_parent_guid = $this->_request_data['plugin_config']['creation_root_topic_parent_guid'];
	}
	else if (isset($this->_request_data['plugin_config']['creation_root_topic_guid']) 
	    && !empty($this->_request_data['plugin_config']['creation_root_topic_guid']))
	{
            $this->_creation_root_topic_guid = $this->_request_data['plugin_config']['creation_root_topic_guid'];
	}

	if (isset($this->_request_data['plugin_config']['creation_root_group_parent_guid']) 
	    && !empty($this->_request_data['plugin_config']['creation_root_group_parent_guid']))
	{
            $this->_creation_root_group_parent_guid = $this->_request_data['plugin_config']['creation_root_group_parent_guid'];
	}
	else if (isset($this->_request_data['plugin_config']['creation_root_group_guid']) 
	    && !empty($this->_request_data['plugin_config']['creation_root_group_guid']))
	{
            $this->_creation_root_group_guid = $this->_request_data['plugin_config']['creation_root_group_guid'];
	}
    }

    function get_plugin_handlers()
    {
        return array
        (
	    'sitewizard' => array
	    (
	        'handler' => array('midgard_admin_sitewizard_plugin', 'create_user_home'),
	    ),
	);
    }

    function _handler_create_user_home()
    {
        $user = $_MIDCOM->auth->user;   
 
        $this->_home_name = "home_" . $user->username;
        $this->_home_title = $user->name;

        return true;
    }

    function _show_create_user_home()
    {
        echo "<pre>";
        try
        {
            $sitewizard = new midgard_admin_sitewizard();
            $sitewizard->set_verbose($this->_verbose);

	    $structure_creator = $sitewizard->initialize_structure_creation('983e66725acd11db845a197adaa843af43af');
	    $structure_creator->read_config($this->_structure_config_path);

            $structure_creator->set_creation_root_topic('6d3af3384be911dcb7b0b3bf4b275d0d5d0d');
            //$structure_creator->create_creation_root_topic('6d3af3384be911dcb7b0b3bf4b275d0d5d0d', "test", "Test", "net.nehmer.static", array("koe", "koe", "koe"));
	    $structure_creator->set_creation_root_group('94f058364f0f11dc93f803ebc4b67c0c7c0c'); 
            //$structure_creator->create_creation_root_group('94f058364f0f11dc93f803ebc4b67c0c7c0c', "testgroup8");
	    $structure_creator->execute();

	    $_MIDCOM->relocate("account");
	}
	catch (midgard_admin_sitewizard_exception $e)
	{
	    echo "<h2>Failed to create user home</h2>";
	    echo "<p>";
	    $e->error();
	    echo "</p>";
        }
	echo "</pre>";
    }
}

?>

