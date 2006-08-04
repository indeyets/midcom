<?php

/**
 * @package org.openpsa.projects
 * @author Nemein Oy http://www.nemein.com/
 * @version $Id: viewer.php,v 1.22 2006/05/13 11:36:45 rambo Exp $
 * @copyright Nemein Oy http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * org.openpsa.projects site interface class.
 */
class org_openpsa_projects_viewer extends midcom_baseclasses_components_request
{

    var $_datamanagers = array();
    var $_view = "default";
    var $_hours_handler = null;    
    var $_workflow_handler = null; 
    var $_toolbars;
       
    /**
     * Constructor.
     * 
     */
    function org_openpsa_projects_viewer($topic, $config)
    {
        parent::midcom_baseclasses_components_request($topic, $config);
        
        $this->_toolbars =& midcom_helper_toolbars::get_instance();      
    }
    
    function _on_initialize()  
    {
        // Load handler classes
        $this->_workflow_handler = new org_openpsa_projects_workflow_handler(&$this->_datamanagers, &$this->_request_data);

        // Match /project/list/<status>
        $this->_request_switch[] = array(
            'fixed_args' => array('project','list'),
            'variable_args' => 1,            
            'handler' => Array('org_openpsa_projects_handler_project_list', 'list'),
        );          
        // Match /project/list
        $this->_request_switch[] = array(
            'fixed_args' => array('project','list'),
            'handler' => Array('org_openpsa_projects_handler_project_list', 'list'),
        );          

        // Match /project/GUID/action
        $this->_request_switch[] = array(
            'fixed_args' => 'project',
            'variable_args' => 2,
            'handler' => Array('org_openpsa_projects_handler_project_action', 'action'),
        );
        // Match /project/new
        $this->_request_switch[] = array(
            'fixed_args' => array('project','new'),
            'handler' => Array('org_openpsa_projects_handler_project_new', 'new'),
        );        
        // Match /project/GUID
        $this->_request_switch[] = array(
            'fixed_args' => 'project',
            'variable_args' => 1,
            'handler' => Array('org_openpsa_projects_handler_project_view', 'view'),
        );
        // Match /task/list/<Project GUID>
        $this->_request_switch[] = array(
            'fixed_args' => array('task','list'),
            'variable_args' => 2,            
            'handler' => Array('org_openpsa_projects_handler_task_list', 'list'),
        );                
        // Match /task/list/
        $this->_request_switch[] = array(
            'fixed_args' => array('task','list'),     
            'handler' => Array('org_openpsa_projects_handler_task_list', 'list'),
        );                

        // Match /task/related/GUID
        $this->_request_switch['task_view_related'] = array(
            'fixed_args' => Array('task', 'related'),
            'variable_args' => 1,
            'handler' => Array('org_openpsa_projects_handler_task_view', 'view'),
        );  
        // Match /task/GUID/action
        $this->_request_switch[] = array(
            'fixed_args' => 'task',
            'variable_args' => 2,
            'handler' => Array('org_openpsa_projects_handler_task_action', 'action'),
        );
        // Match /task/new
        $this->_request_switch[] = array(
            'fixed_args' => array('task','new'),
            'handler' => Array('org_openpsa_projects_handler_task_new', 'new'),
        );
        // Match /task/new/<Target type>/<Target GUID>
        $this->_request_switch[] = array(
            'fixed_args' => array('task','new'),
            'variable_args' => 2,            
            'handler' => Array('org_openpsa_projects_handler_task_new', 'new'),
        );
        // Match /task/GUID
        $this->_request_switch['task_view'] = array(
            'fixed_args' => 'task',
            'variable_args' => 1,
            'handler' => Array('org_openpsa_projects_handler_task_view', 'view'),
        );
                     
        // Match /hours/<listtype>/<GUID>
        $this->_request_switch[] = array(
            'fixed_args' => 'hours',
            'variable_args' => 2,
            'handler' => Array('org_openpsa_projects_handler_hours_list', 'list'),
        );        
        // Match /hours/<listtype>/<GUID>/action
        $this->_request_switch[] = array(
            'fixed_args' => 'hours',
            'variable_args' => 3,
            'handler' => Array('org_openpsa_projects_handler_hours_action', 'action'),
        );
        // Match /workflow/GUID/ACTION
        $this->_request_switch[] = array(
            'fixed_args' => 'workflow',
            'variable_args' => 2,
            'handler' => array(&$this->_workflow_handler,'action'),
        );
        // Match /workflow/GUID
        $this->_request_switch[] = array(
            'fixed_args' => 'workflow',
            'variable_args' => 1,
            'handler' => array(&$this->_workflow_handler,'post'),
        );
        $this->_request_switch[] = array(
            'fixed_args' => 'debug',
            'handler' => 'debug'
        );
        // Match /
        $this->_request_switch[] = array(
            'handler' => Array('org_openpsa_projects_handler_frontpage', 'frontpage'),
        );
        
        //Add common relatedto request switches
        org_openpsa_relatedto_handler::common_request_switches($this->_request_switch, 'org.openpsa.projects');
        //If you need any custom switches add them here

        // This component uses Ajax, include the handler javascripts
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL."/org.openpsa.helpers/ajaxutils.js");
        $this->_request_data['view'] = 'default';
        $this->_request_data['config'] = $this->_config;

        // Pass the topic on to handlers
        $this->_request_data['project_topic'] =& $this->_topic;
                
        // Always run in uncached mode
        $_MIDCOM->cache->content->no_cache();

        // Load datamanagers for main classes
        $this->_initialize_datamanager('project', $this->_config->get('schemadb_project'));        
        $this->_initialize_datamanager('task', $this->_config->get('schemadb_task'));
        $this->_initialize_datamanager('hours', $this->_config->get('schemadb_hours'));
        
        $this->_request_data['datamanagers'] =& $this->_datamanagers;
    }
    
    function _initialize_datamanager($type, $schemadb_snippet)
    {
        // Load schema database snippet or file
        debug_add("Loading Schema Database", MIDCOM_LOG_DEBUG);
        $schemadb_contents = midcom_get_snippet_content($schemadb_snippet);
        eval("\$schemadb = Array ( {$schemadb_contents} );");
        // Initialize the datamanager with the schema
        $this->_datamanagers[$type] = new midcom_helper_datamanager($schemadb);

        if (!$this->_datamanagers[$type]) {
            $GLOBALS["midcom"]->generate_error(MIDCOM_ERRCRIT, "Datamanager could not be instantinated.");
            // This will exit. 	 
        }
    }

    function _hack_dm_for_ajax_hours()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add('called');
        //DM searches for this variable in the REQUEST, unfortunately we cannot cleanly pass it with the Ajax, so we add here.
        if (   array_key_exists('hours', $this->_datamanagers)
            && is_object($this->_datamanagers['hours'])
            && isset($this->_datamanagers['hours']->form_prefix))
        {
            $_REQUEST[$this->_datamanagers['hours']->form_prefix . 'submit'] = true;
        }
        //Checkbox widget *really* wants this key regardless of the actual prefix.
        $_REQUEST['midcom_helper_datamanager_submit'] = true;
        debug_add("_REQUEST is now:\n===\n" . sprint_r($_REQUEST) . "===\n");
        debug_pop();
    }


    //For playing with stuff
    function _handler_debug($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        return true;
    }
    function _show_debug($handler_id, &$data)
    {
        midcom_show_style("show-debug");
    }
    
}
?>