<?php
/**
 * @package org.openpsa.projects
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id: action.php,v 1.2 2006/05/10 16:27:39 rambo Exp $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Task action handler
 * 
 * @package org.openpsa.projects
 */
class org_openpsa_projects_handler_task_action extends midcom_baseclasses_components_handler
{
    var $_datamanagers;

    function org_openpsa_projects_handler_task_action() 
    {
        parent::midcom_baseclasses_components_handler();
    }
    
    function _on_initialize()
    {
        $this->_datamanagers =& $this->_request_data['datamanagers'];
    }

    function _load_task($identifier)
    {
        $task = new org_openpsa_projects_task($identifier);
        
        if (!is_object($task))
        {
            return false;
        }
        
        //Fill the customer field to DM
        debug_add("schema before \n===\n" . sprint_r($this->_datamanagers['task']->_layoutdb['default']) . "===\n");
        org_openpsa_helpers_schema_modifier($this->_datamanagers['task'], 'customer', 'widget', 'select', 'default', false);
        org_openpsa_helpers_schema_modifier($this->_datamanagers['task'], 'customer', 'widget_select_choices', org_openpsa_helpers_task_groups($task), 'default', false);
        debug_add("schema after \n===\n" . sprint_r($this->_datamanagers['task']->_layoutdb['default']) . "===\n");

        // Load the task to datamanager
        if (!$this->_datamanagers['task']->init($task))
        {
            return false;
        }
        
        return $task;
    }

    function _handler_action($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        
        // Check if we get the task
        $this->_request_data['task'] = $this->_load_task($args[0]);
        if (!$this->_request_data['task'])
        {
            return false;
        }
        
        // Check if the action is a valid one
        $this->_request_data['task_action'] = $args[1];
        switch ($args[1])
        {
            case 'reopen':
                $this->_request_data['task']->require_do('midgard:update');
                $this->_request_data['task']->reopen();
                $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
                $_MIDCOM->relocate("{$prefix}task/{$this->_request_data['task']->guid}/");
                // This will exit()

            case 'complete':
                $this->_request_data['task']->require_do('midgard:update');
                $this->_request_data['task']->complete();
                $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
                $_MIDCOM->relocate("{$prefix}task/{$this->_request_data['task']->guid}/");
                // This will exit()
                
            case 'edit':
                $_MIDCOM->auth->require_do('midgard:update', $this->_request_data['task']);

                $_MIDCOM->set_pagetitle(sprintf($this->_request_data['l10n']->get('edit task %s'), $this->_request_data['task']->title));
                
                switch ($this->_datamanagers['task']->process_form()) {
                    case MIDCOM_DATAMGR_EDITING:
                        $this->_view = "edit";

                        // Add toolbar items
                        org_openpsa_helpers_dm_savecancel($this->_view_toolbar, $this);
                        
                        return true;

                    case MIDCOM_DATAMGR_SAVED:                
                    case MIDCOM_DATAMGR_CANCELLED:
                        $this->_view = "default";
                        $_MIDCOM->relocate($_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)
                            . "task/" . $this->_request_data["task"]->guid . "/");
                        // This will exit()
                
                    case MIDCOM_DATAMGR_FAILED:
                        $this->errstr = "Datamanager: " . $GLOBALS["midcom_errstr"];
                        $this->errcode = MIDCOM_ERRCRIT;
                        return false;
                }
                return true;
            default:
                return false;
        }
    }

    function _show_action($handler_id, &$data)
    {
        if ($this->_view == "edit")
        {
            $this->_request_data['task_dm']  = $this->_datamanagers['task'];
            midcom_show_style("show-task-edit");
        }
    }
}
?>