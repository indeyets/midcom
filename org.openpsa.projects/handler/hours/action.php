<?php
/**
 * @package org.openpsa.projects
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: action.php,v 1.3 2006/05/13 11:36:45 rambo Exp $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Hour report action handler
 *
 * @package org.openpsa.projects
 */
class org_openpsa_projects_handler_hours_action extends midcom_baseclasses_components_handler
{
    var $_datamanagers;

    function org_openpsa_projects_handler_hours_action()
    {
        parent::midcom_baseclasses_components_handler();
    }

    function _on_initialize()
    {
        $this->_datamanagers =& $this->_request_data['datamanagers'];
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
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Datamanager could not be instantiated.");
            // This will exit.
        }
    }

    function _load_hours($identifier)
    {

        $hours = new org_openpsa_projects_hour_report($identifier);

        if (!is_object($hours))
        {
            return false;
        }

        /* checkbox widget won't work with ajax editing existing hours unless
           this is done already here */
        org_openpsa_projects_viewer::_hack_dm_for_ajax_hours();

        // Load the hours to datamanager
        if (!$this->_datamanagers['hours']->init($hours))
        {
            return false;
        }
        return $hours;
    }


    function _creation_dm_callback(&$datamanager)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        // This is what Datamanager calls to actually create an hour
        $result = array (
            "storage" => null,
            "success" => false,
        );

        $hour_report = new org_openpsa_projects_hour_report();
        // Be smart about the task
        if ($this->_list_type == 'task')
        {
            $hour_report->task = (int) $this->_list_identifier;
        }
        else
        {
            $hour_report->task = $_POST['midcom_helper_datamanager_field_task'];
        }
        if (   array_key_exists('midcom_helper_datamanager_field_invoiceable', $_REQUEST)
            && !empty($_REQUEST['midcom_helper_datamanager_field_invoiceable']))
        {
            $hour_report->invoiceable = true;
        }
        //debug_add("about to create hour_report\n===\n" . sprint_r($hour_report) . "===\n");

        if (!$hour_report->create())
        {
            debug_pop();
            return null;
        }

        $this->_request_data['hour_report'] = new org_openpsa_projects_hour_report($hour_report->id);
        $result["storage"] =& $this->_request_data['hour_report'];
        $result["success"] = true;

        //debug_add("created hour_report\n===\n" . sprint_r($this->_request_data['hour_report']) . "===\n");

        debug_pop();
        return $result;
    }

	/**
	 * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return bool Indicating success.
	 */
    function _handler_action($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        debug_push_class(__CLASS__, __FUNCTION__);
        if (count($args) != 3)
        {
            return false;
        }

        $this->_list_type = $args[0];
        $this->_list_identifier = $args[1];
        $action = $args[2];

        switch ($this->_list_type)
        {
            case 'task':
                $_MIDCOM->skip_page_style = true;
                break;
            default:
                return false;
        }

        debug_add("Got POST:\n===\n" . sprint_r($_POST) . "===\n");

        $ajax = new org_openpsa_helpers_ajax();
        switch ($action)
        {
            case 'update':
                $this->_request_data['hour_report'] = $this->_load_hours($_POST['midcom_helper_datamanager_field_guid']);
                if (!$this->_request_data['hour_report'])
                {
                    debug_pop();
                    $ajax->simpleReply(false, 'Could not load hour report object');
                    // This will exit
                }
                //debug_add("this->_request_data['hour_report']\n===\n" . sprint_r($this->_request_data['hour_report']) . "===\n");
                if (!$this->_datamanagers['hours']->init($this->_request_data['hour_report']))
                {
                    debug_pop();
                    $ajax->simpleReply(false, 'Failed to initialize DataManager');
                    // This will exit
                }
                org_openpsa_projects_viewer::_hack_dm_for_ajax_hours();
                //debug_add("DM->_storage before process_form\n===\n" . sprint_r($this->_datamanagers['hours']->_storage) . "===\n");
                //Workaround to weird MidCOM issue
                ini_set('error_reporting', E_ERROR);
                $dm_return = $this->_datamanagers['hours']->process_form();
                ini_set('error_reporting', E_ALL);
                switch ($dm_return) {
                    case MIDCOM_DATAMGR_EDITING:
                        debug_pop();
                        $ajax->simpleReply(false, 'MIDCOM_DATAMGR_EDITING');
                        // This will exit

                    case MIDCOM_DATAMGR_SAVED:
                        //debug_add("DM->_storage after process_form\n===\n" . sprint_r($this->_datamanagers['hours']->_storage) . "===\n");
                        debug_add("First time submit, the DM has created an object");
                        debug_pop();
                        $ajax->simpleReply(true, mgd_errstr());
                        // This will exit

                    case MIDCOM_DATAMGR_CANCELLED:
                        debug_pop();
                        $ajax->simpleReply(false, 'MIDCOM_DATAMGR_CANCELLED');
                        // This will exit

                    case MIDCOM_DATAMGR_FAILED:
                        debug_pop();
                        $ajax->simpleReply(false, mgd_errstr());
                        // This will exit
                    default:
                        debug_pop();
                        $ajax->simpleReply(false, 'Unknown DataManager return code');
                        // This will exit
                }

            case 'create':
                /* Ajax booleans won't work on create unless we hack them before even loading the schema
                   so we kill the previous instance (if initialized) and go on to create a hacked one */
                if (isset($this->_datamanagers['hours']))
                {
                    unset($this->_datamanagers['hours']);
                }
                org_openpsa_projects_viewer::_hack_dm_for_ajax_hours();
                $this->_initialize_datamanager('hours', $this->_config->get('schemadb_hours'));

                if (!$this->_datamanagers['hours']->init_creation_mode('default',$this,"_creation_dm_callback"))
                {
                    debug_pop();
                    $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                        "Failed to initialize datamanager in creation mode for schema 'default'.");
                    // This will exit
                }
                //Workaround to weird MidCOM issue
                ini_set('error_reporting', E_ERROR);
                $dm_return = $this->_datamanagers['hours']->process_form();
                ini_set('error_reporting', E_ALL);
                switch ($dm_return) {
                    case MIDCOM_DATAMGR_CREATING:
                        debug_add('First call within creation mode');
                        debug_pop();
                        $ajax->simpleReply(false, 'MIDCOM_DATAMGR_CREATING');
                        // This will exit

                    case MIDCOM_DATAMGR_EDITING:
                    case MIDCOM_DATAMGR_SAVED:
                        debug_add("First time submit, the DM has created an object");
                        debug_pop();
                        $ajax->simpleReply(true, mgd_errstr());
                        // This will exit

                    case MIDCOM_DATAMGR_CANCELLED_NONECREATED:
                        debug_add('Cancel without anything being created, redirecting to the welcome screen.');
                        debug_pop();
                        $ajax->simpleReply(false, 'MIDCOM_DATAMGR_CANCELLED_NONECREATED');
                        // This will exit

                    case MIDCOM_DATAMGR_CANCELLED:
                        debug_pop();
                        $ajax->simpleReply(false, 'MIDCOM_DATAMGR_CANCELLED');
                        // This will exit

                    case MIDCOM_DATAMGR_FAILED:
                    case MIDCOM_DATAMGR_CREATEFAILED:
                        debug_pop();
                        $ajax->simpleReply(false, mgd_errstr());
                        // This will exit

                    default:
                        debug_pop();
                        $ajax->simpleReply(false, 'Datamanager method unknown');
                        // This will exit
                }
            default:
                return false;
        }
        debug_add('for some reason we did not exit already from the saves');
        debug_pop();
        return true;
    }

    function _show_action($handler_id, &$data)
    {
        return;
    }
}
?>