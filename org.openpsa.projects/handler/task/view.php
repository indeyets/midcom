<?php
/**
 * @package org.openpsa.projects
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id: view.php,v 1.3 2006/05/12 16:49:51 rambo Exp $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Task view handler
 * 
 * @package org.openpsa.projects
 */
class org_openpsa_projects_handler_task_view extends midcom_baseclasses_components_handler
{
    var $_datamanagers;

    function org_openpsa_projects_handler_task_view() 
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
    
    function _initialize_hours_widget(&$task)
    {
        $nap = new midcom_helper_nav();
        $node = $nap->get_node($nap->get_current_node());
        $this->_request_data['hours_widget'][$task->guid] = new org_openpsa_projects_hours_widget(&$task, $this->_datamanagers['hours'], "{$node[MIDCOM_NAV_FULLURL]}hours/task/{$task->id}/", $this->_request_data);
    }

    function _handler_view($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        
        // Get the requested task object
        $this->_request_data['task'] = $this->_load_task($args[0]);
        if (!$this->_request_data['task'])
        {
            return false;
        }
        
        $_MIDCOM->set_pagetitle(sprintf($this->_request_data['l10n']->get('task %s'), $this->_request_data['task']->title));
        
        // Add toolbar items
        if ($this->_request_data['task']->up)
        {
            $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
            $parent = $this->_request_data['task']->get_parent();
            if ($parent->orgOpenpsaObtype == ORG_OPENPSA_OBTYPE_PROJECT)
            {
                $parent_url = "{$prefix}project/{$parent->guid}/";
            }
            else
            {
                $parent_url = "{$prefix}task/{$parent->guid}/";
            }
            $this->_view_toolbar->add_item(
                Array(
                    MIDCOM_TOOLBAR_URL => $parent_url,
                    MIDCOM_TOOLBAR_LABEL => $parent->title,
                    MIDCOM_TOOLBAR_HELPTEXT => null,
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_left.png',
                    MIDCOM_TOOLBAR_ENABLED => true,
                )
            );
        }
        
        if (   count($args) == 1
            && $_MIDCOM->auth->can_do('midgard:update', $this->_request_data['task']))
        {
            $this->_initialize_hours_widget(&$this->_request_data['task']);
            $this->_view_toolbar->add_item(
                Array(
                    MIDCOM_TOOLBAR_URL => 'task/'.$this->_request_data['task']->guid().'/edit/',
                    MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n']->get('edit'),
                    MIDCOM_TOOLBAR_HELPTEXT => null,
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
                    MIDCOM_TOOLBAR_ENABLED => true,
                )
            );
            
            if ($this->_request_data['task']->status == ORG_OPENPSA_TASKSTATUS_CLOSED)
            {
                // TODO: Make POST request
                $this->_view_toolbar->add_item(
                    Array(
                        MIDCOM_TOOLBAR_URL => "task/{$this->_request_data['task']->guid}/reopen/",
                        MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n']->get('reopen'),
                        MIDCOM_TOOLBAR_HELPTEXT => null,
                        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/folder-expanded.png',
                        MIDCOM_TOOLBAR_ENABLED => true,
                    )
                );
            }
            elseif ($this->_request_data['task']->status_type == 'ongoing')
            {
                // TODO: Make POST request
                $this->_view_toolbar->add_item(
                    Array(
                        MIDCOM_TOOLBAR_URL => "task/{$this->_request_data['task']->guid}/complete/",
                        MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n']->get('mark completed'),
                        MIDCOM_TOOLBAR_HELPTEXT => null,
                        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/new_task.png',
                        MIDCOM_TOOLBAR_ENABLED => true,
                    )
                );
            }
        }

        if ($handler_id == 'task_view')
        {
            $this->_view_toolbar->add_item(
                Array(
                    MIDCOM_TOOLBAR_URL => "task/related/{$this->_request_data['task']->guid}/",
                    MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n']->get('view related information'),
                    MIDCOM_TOOLBAR_HELPTEXT => null,
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/attach.png',
                    MIDCOM_TOOLBAR_ENABLED => true,
                )
            );
        }
        else
        {
            $this->_view_toolbar->add_item(
                Array(
                    MIDCOM_TOOLBAR_URL => "task/{$this->_request_data['task']->guid}/",
                    MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n']->get('back to task'),
                    MIDCOM_TOOLBAR_HELPTEXT => null,
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_left.png',
                    MIDCOM_TOOLBAR_ENABLED => true,
                )
            );
            
            // Load "Create X" buttons for all the related info
            $relatedto_button_settings = org_openpsa_relatedto_handler::common_toolbar_buttons_defaults();
            $relatedto_button_settings['wikinote']['wikiword'] = sprintf($this->_request_data['l10n']->get('notes for task %s on %s'), $this->_request_data['task']->title, date('Y-m-d H:i'));
            
            // No sense to create tasks related to task?
            unset($relatedto_button_settings['task']);
            
            org_openpsa_relatedto_handler::common_node_toolbar_buttons($this->_node_toolbar, $this->_request_data['task'], $this->_component, $relatedto_button_settings);
        }
        
        $this->_view_toolbar->bind_to($this->_request_data['task']);
        
        return true;
    }
    
    function _show_view($handler_id, &$data)
    {
        if ($handler_id == 'task_view')
        {
            $this->_request_data['task_dm']  = $this->_datamanagers['task'];
            midcom_show_style("show-task");
        }
        else
        {
            midcom_show_style("show-task-related");
        }
    }
    
    function _populate_deliverables_dm(&$datamanager, $schema)
    {
        $deliverable_interface = new org_openpsa_projects_deliverables_interface();
        
        $deliverable_plugins = $deliverable_interface->list_plugins();
        $plugins_displayed = 0;
        foreach ($deliverable_plugins as $deliverable)
        {
            $field_name = 'deliverable_' . $deliverable->name;
            // Set datatype
            org_openpsa_helpers_schema_modifier(&$datamanager, $field_name, 'datatype', 'boolean', $schema, true);
            // Set widget
            org_openpsa_helpers_schema_modifier(&$datamanager, $field_name, 'widget', 'checkbox', $schema, true);
            // Set description
            org_openpsa_helpers_schema_modifier(&$datamanager, $field_name, 'description', $deliverable->render_select_plugin(), $schema, true);
            org_openpsa_helpers_schema_modifier(&$datamanager, $field_name, 'widget_checkbox_textafter', true, $schema, true);
            org_openpsa_helpers_schema_modifier(&$datamanager, $field_name, 'widget_checkbox_allow_html', true, $schema, true);
            
            if ($plugins_displayed == 0)
            {
                $fieldgroup_array = array(
                    'title'     => $this->_request_data['l10n']->get('deliverables'),
                    'css_group' => 'area deliverables',
                );
                org_openpsa_helpers_schema_modifier(&$datamanager, $field_name, 'start_fieldgroup', $fieldgroup_array, $schema, true);
            }
            $plugins_displayed++;
            if ($plugins_displayed == count($deliverable_plugins))
            {
                org_openpsa_helpers_schema_modifier(&$datamanager, $field_name, 'end_fieldgroup', '', $schema, true);
            }
        }
    }
}
?>