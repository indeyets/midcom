<?php
/**
 * @package net.nemein.teams
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is a plugin for creating user home topic
 *
 * @package net.nemein.teams
 */
class midgard_admin_sitewizard_plugin extends midcom_baseclasses_components_handler
{
    var $_host_guid = '';
    var $_structure_config_path = '';
    var $_verbose = false;
    var $_home_name = '';
    var $_home_title = '';
    var $_creation_root_topic_guid = '';
    var $_creation_root_topic_parent_guid = '';
    var $_creation_root_topic_component = '';
    var $_creation_root_topic_parameters = array();
    var $_creation_root_group_guid = '';
    var $_creation_root_group_parent_guid ='';
    var $_creation_root_group_name = '';

    var $_logger = null;
    var $_team_guid = '';

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

        $this->_logger =& new net_nemein_teams_logger();

        if (isset($this->_request_data['plugin_config']['host_guid'])
            && !empty($this->_request_data['plugin_config']['host_guid']))
	    {
            $this->_host_guid = $this->_request_data['plugin_config']['host_guid'];
	    }

        if (isset($this->_request_data['plugin_config']['creation_root_topic_component'])
            && !empty($this->_request_data['plugin_config']['creation_root_topic_component']))
	    {
            $this->_creation_root_topic_component = $this->_request_data['plugin_config']['creation_root_topic_component'];
	    }

        if (isset($this->_request_data['plugin_config']['creation_root_topic_parameters'])
            && !empty($this->_request_data['plugin_config']['creation_root_topic_parameters']))
	    {
            $this->_creation_root_topic_parameters = $this->_request_data['plugin_config']['creation_root_topic_parameters'];
	    }

        if (isset($this->_request_data['plugin_config']['creation_root_group_name'])
            && !empty($this->_request_data['plugin_config']['creation_root_group_name']))
	    {
            $this->_creation_root_group_name = $this->_request_data['plugin_config']['creation_root_group_name'];
	    }

        if (isset($this->_request_data['plugin_config']['structure_config_path'])
            && !empty($this->_request_data['plugin_config']['structure_config_path']))
	    {
            $this->_structure_config_path = $this->_request_data['plugin_config']['structure_config_path'];
	    }

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
	            'handler' => array('midgard_admin_sitewizard_plugin', 'create_team_home'),
	        ),
	    );
    }

	/**
     * @return boolean Indicating success.
	 */
    function _handler_create_team_home()
    {
        $user = $_MIDCOM->auth->user;

        // Lets get the team group
        $qb = net_nemein_teams_team_dba::new_query_builder();
        $qb->add_constraint('managerguid', '=', $user->guid);
        $teams = $qb->execute();

        if (count($teams) > 1)
        {
            // TODO: this shouldn't happen! Handle this error
        }

        $this->_team_guid = $teams[0]->groupguid;
        $group = new midcom_db_group($this->_team_guid);

        $url_name = $group->guid;
        if ($_MIDCOM->serviceloader->can_load('midcom_core_service_urlgenerator'))
        {
            $urlgenerator = $_MIDCOM->serviceloader->load('midcom_core_service_urlgenerator');
            $url_name = $urlgenerator->from_string($group->name);
        }

        $this->_home_name = $url_name;
        $this->_home_title = $group->name;

        return true;
    }

    function _show_create_team_home()
    {
        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
        echo "<pre>";
        try
        {
            $sitewizard = new midgard_admin_sitewizard();
            $sitewizard->set_verbose($this->_verbose);

	        $structure_creator = $sitewizard->initialize_structure_creation($this->_host_guid);
	        $structure_creator->read_config($this->_structure_config_path);

            if ($this->_creation_root_topic_guid != '')
	        {
                $structure_creator->set_creation_root_topic($this->_creation_root_topic_guid);
            }
	        elseif ($this->_creation_root_topic_parent_guid != '')
	        {

	            $structure_creator->create_creation_root_topic($this->_creation_root_topic_parent_guid,
		        $this->_home_name, $this->_home_title, $this->_creation_root_topic_component,
		        $this->_creation_root_topic_parameters, $this->_home_title, true);
	        }

            if ($this->_creation_root_group_guid != '')
	        {
	            $structure_creator->set_creation_root_group($this->_creation_root_group_guid);
            }
	        elseif ($this->_cretion_root_group_parent_guid != '')
	        {
	            $structure_creator->create_creation_root_group($this->_creation_root_group_guid,
	                $this->_creation_root_group_name);
	        }

	        $guid = $structure_creator->execute();

	        $this->_logger->log("Team home folder created by " . $_MIDCOM->auth->user->_storage->username, $this->_team_guid);

	        $_MIDCOM->relocate("{$prefix}{$this->_home_name}");

	    }
	    catch (midgard_admin_sitewizard_exception $e)
	    {
	        $this->_logger->log("Failed to createa team home folder by " . $_MIDCOM->auth->user->_storage->username, $this->_team_guid);
	        echo "<h2>Failed to create user home</h2>";
	        echo "<p>";
	        $e->error();
	        echo "</p>";
	        $_MIDCOM->relocate('error');
        }
	   echo "</pre>";
    }
}

?>

