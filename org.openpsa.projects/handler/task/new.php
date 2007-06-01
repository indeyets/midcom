<?php
/**
 * @package org.openpsa.projects
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: new.php,v 1.4 2006/07/06 15:49:49 rambo Exp $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * New task handler
 *
 * @package org.openpsa.projects
 */
class org_openpsa_projects_handler_task_new extends midcom_baseclasses_components_handler
{
    var $_datamanagers;

    function org_openpsa_projects_handler_task_new()
    {
        parent::midcom_baseclasses_components_handler();
    }

    function _on_initialize()
    {
        $this->_datamanagers =& $this->_request_data['datamanagers'];
    }

    function _creation_dm_callback(&$datamanager)
    {
        // This is what Datamanager calls to actually create a person
        $result = array (
            "success" => false,
            "storage" => null,
        );

        $task = new org_openpsa_projects_task();

        if (   array_key_exists('project', $this->_request_data)
            && !empty($this->_request_data['project']))
        {
            // Add the task to the project
            $task->up = (int) $this->_request_data['project']->id;

            // Populate some default data from parent as needed
            $task->orgOpenpsaAccesstype = $this->_request_data['project']->orgOpenpsaAccesstype;
            $task->orgOpenpsaOwnerWg = $this->_request_data['project']->orgOpenpsaOwnerWg;
            /* This is not the correct way to get the default to editor,
               (causes problems if task is created after project is supposed to end)
            $task->end = $this->_request_data['project']->end;
            */
        }

        //debug_add("About to create task\n===\n" . sprint_r($task) . "===\n");
        //mgd_debug_start();
        $stat = $task->create();
        //mgd_debug_stop();
        if ($stat)
        {
            $this->_request_data['task'] = new org_openpsa_projects_task($task->id);
            $rel_ret = org_openpsa_relatedto_handler::on_created_handle_relatedto($this->_request_data['task'], 'org.openpsa.projects');
            debug_add("org_openpsa_relatedto_handler returned \n===\n" . sprint_r($rel_ret) . "===\n");
            //Debugging
            $result["storage"] =& $this->_request_data['task'];
            $result["success"] = true;
            return $result;
        }
        return null;
    }


    function _handler_new($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();

        if (count($args) > 0)
        {
            // Get the related object
            switch ($args[0])
            {
                case "project":
                    // This task is to be connected to a project
                    $this->_request_data['project'] = new org_openpsa_projects_project($args[1]);
                    if (!$this->_request_data['project'])
                    {
                        return false;
                    }
                    $_MIDCOM->auth->require_do('midgard:create', $this->_request_data['project']);

                    // Copy permissions from project, TO BE DEPRECATED
                    org_openpsa_helpers_schema_modifier(&$this->_datamanagers['task'], 'orgOpenpsaAccesstype', 'default', $this->_request_data['project']->orgOpenpsaAccesstype, 'newtask', false);
                    org_openpsa_helpers_schema_modifier(&$this->_datamanagers['task'], 'orgOpenpsaOwnerWg', 'default', $this->_request_data['project']->orgOpenpsaOwnerWg, 'newtask', false);

                    // Copy resources and contacts from project
                    org_openpsa_helpers_schema_modifier(&$this->_datamanagers['task'], 'resources', 'default', $this->_request_data['project']->resources, 'newtask', false);
                    org_openpsa_helpers_schema_modifier(&$this->_datamanagers['task'], 'contacts', 'default', $this->_request_data['project']->contacts, 'newtask', false);

                    // Populate deliverable editor
                    // TODO: Enable again when we have actually working deliverables
                    //$this->_populate_deliverables_dm(&$this->_datamanagers['task'], 'newtask');
                    break;

                default:
                    return false;
            }
        }
        else
        {
            $_MIDCOM->auth->require_user_do('midgard:create', null, 'org_openpsa_projects_task');
        }

        if (!$this->_datamanagers['task']->init_creation_mode("newtask",$this,"_creation_dm_callback"))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                "Failed to initialize datamanager in creation mode for schema 'newtask'.");
            // This will exit
        }

        if (array_key_exists('project', $this->_request_data))
        {
            $_MIDCOM->set_pagetitle(sprintf($this->_request_data['l10n']->get('new task to project %s'), $this->_request_data['project']->title));
        }
        else
        {
            $_MIDCOM->set_pagetitle($this->_request_data['l10n']->get('new task'));
        }

        switch ($this->_datamanagers['task']->process_form()) {
            case MIDCOM_DATAMGR_CREATING:
                debug_add('First call within creation mode');

                // Add toolbar items
                org_openpsa_helpers_dm_savecancel($this->_view_toolbar, $this);
                break;

            case MIDCOM_DATAMGR_SAVED:
            case MIDCOM_DATAMGR_EDITING:
                debug_add("First time submit, the DM has created an object");
                // Change schema setting
                $this->_request_data['task']->parameter("midcom.helper.datamanager","layout","default");

                // TODO: index

                // Relocate to group view
                $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
                debug_pop();
                /* this for some reason dies
                $_MIDCOM->relocate("{$prefix}task/{$this->_request_data['task']->guid}/edit/");
                */
                $_MIDCOM->relocate("{$prefix}task/{$this->_request_data['task']->guid}/");
                break;

            case MIDCOM_DATAMGR_CANCELLED_NONECREATED:
                debug_add('Cancel without anything being created, redirecting to the welcome screen.');

                if (array_key_exists('project', $this->_request_data))
                {
                    $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
                    $_MIDCOM->relocate("{$prefix}project/{$this->_request_data['project']->guid}/");
                }
                else
                {
                    $_MIDCOM->relocate('');
                }
                // This will exit

            case MIDCOM_DATAMGR_CANCELLED:
                $this->errcode = MIDCOM_ERRCRIT;
                $this->errstr = 'Method MIDCOM_DATAMGR_CANCELLED unknown for creation mode.';
                debug_pop();
                return false;

            case MIDCOM_DATAMGR_FAILED:
            case MIDCOM_DATAMGR_CREATEFAILED:
                debug_add('The DM failed critically, see above.');
                $this->errstr = 'The Datamanger failed to process the request, see the Debug Log for details';
                $this->errcode = MIDCOM_ERRCRIT;
                debug_pop();
                return false;

            default:
                $this->errcode = MIDCOM_ERRCRIT;
                $this->errstr = 'Method unknown';
                debug_pop();
                return false;

        }

        debug_pop();
        return true;
    }

    function _show_new($handler_id, &$data)
    {
        $this->_request_data['task_dm']  = $this->_datamanagers['task'];
        midcom_show_style("show-task-new");
    }
}
?>