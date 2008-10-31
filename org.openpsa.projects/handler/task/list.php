<?php
/**
 * @package org.openpsa.projects
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: list.php,v 1.1 2006/05/10 13:00:45 rambo Exp $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Task list handler
 *
 * @package org.openpsa.projects
 */
class org_openpsa_projects_handler_task_list extends midcom_baseclasses_components_handler
{
    var $_task_cache = Array();

    function __construct()
    {
        parent::__construct();
    }

    function _on_initialize()
    {
    }
    
    /**
     * Add a task to a requested task list view
     * 
     * @param integer $task_id ID of an org_openpsa_projects_task_dba object
     * @param string $list Key of the task list
     * @return boolean
     */
    function _add_task_to_list($task_id, $list = 'current')
    {
        // Ensure the requested list is available
        if (!array_key_exists($list, $this->_request_data['tasks']))
        {
            $this->_request_data['tasks'][$list] = Array();
        }

        // Instantiate each task only once
        if (!array_key_exists($task_id, $this->_task_cache))
        {
            $this->_task_cache[$task_id] = new org_openpsa_projects_task_dba($task_id);
        }

        // Only accept tasks to this list, projects need not apply
        if ($this->_task_cache[$task_id]->orgOpenpsaObtype != ORG_OPENPSA_OBTYPE_TASK)
        {
            return false;
        }

        // Add task to a list only once
        if (!array_key_exists($task_id, $this->_request_data['tasks'][$list]))
        {
            $this->_request_data['tasks'][$list][$task_id] = $this->_task_cache[$task_id];
        }
        return true;
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_list($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        $this->_request_data['tasks'] = Array();

        $structure = new org_openpsa_core_structure();
        $this->_request_data['contacts_url'] = $structure->get_node_full_url('org.openpsa.contacts');
        $this->_request_data['sales_url'] = $structure->get_node_full_url('org.openpsa.sales');
        $this->_request_data['prefix'] = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

        $_MIDCOM->add_link_head
        (
            array
            (
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL . "/org.openpsa.core/list.css",
            )
        );

        if (count($args) > 0)
        {
            switch ($args[0])
            {
                case 'all':
                    return $this->_handler_list_all($args);
                    break;
                case 'project':
                    return $this->_handler_list_project($args);
                default:
                    return false;
            }
        }
        else
        {
            return $this->_handler_list_user();
        }
        return false;
    }

    private function _handler_list_user()
    {
        // Query user's current tasks
        $this->_request_data['view'] = 'my_tasks';

        // Tasks proposed to the user
        $qb = org_openpsa_projects_task_resource_dba::new_query_builder();
        $qb->add_constraint('person', '=', $_MIDGARD['user']);
        $qb->add_constraint('orgOpenpsaObtype', '=', ORG_OPENPSA_OBTYPE_PROJECTRESOURCE);
        $qb->add_constraint('task.orgOpenpsaObtype', '=', ORG_OPENPSA_OBTYPE_TASK);
        $qb->add_constraint('task.status', '=', ORG_OPENPSA_TASKSTATUS_PROPOSED);
        // Workgroup filtering
        if ($GLOBALS['org_openpsa_core_workgroup_filter'] != 'all')
        {
            $qb->add_constraint('task.orgOpenpsaOwnerWg', '=', $GLOBALS['org_openpsa_core_workgroup_filter']);
        }
        $ret = $qb->execute();
        if (   is_array($ret)
            && count($ret) > 0)
        {
            foreach ($ret as $resource)
            {
                $this->_add_task_to_list($resource->task, 'proposed');
            }
        }

        // Tasks user has under work
        $qb = org_openpsa_projects_task_resource_dba::new_query_builder();
        $qb->add_constraint('person', '=', $_MIDGARD['user']);
        $qb->add_constraint('orgOpenpsaObtype', '=', ORG_OPENPSA_OBTYPE_PROJECTRESOURCE);
        $qb->add_constraint('task.orgOpenpsaObtype', '=', ORG_OPENPSA_OBTYPE_TASK);
        $qb->begin_group('OR');
            $qb->begin_group('AND');
                $qb->add_constraint('task.status', '>=', ORG_OPENPSA_TASKSTATUS_STARTED);
                $qb->add_constraint('task.status', '<', ORG_OPENPSA_TASKSTATUS_COMPLETED);
            $qb->end_group();
            $qb->add_constraint('task.status', '=', ORG_OPENPSA_TASKSTATUS_ACCEPTED);
        $qb->end_group();
        // Workgroup filtering
        if ($GLOBALS['org_openpsa_core_workgroup_filter'] != 'all')
        {
            $qb->add_constraint('task.orgOpenpsaOwnerWg', '=', $GLOBALS['org_openpsa_core_workgroup_filter']);
        }
        $ret = $qb->execute();
        if (   is_array($ret)
            && count($ret) > 0)
        {
            foreach ($ret as $resource)
            {
                $this->_add_task_to_list($resource->task, 'current');
            }
        }

        // Tasks completed by user and pending approval
        $qb = org_openpsa_projects_task_resource_dba::new_query_builder();
        $qb->add_constraint('person', '=', $_MIDGARD['user']);
        $qb->add_constraint('orgOpenpsaObtype', '=', ORG_OPENPSA_OBTYPE_PROJECTRESOURCE);
        $qb->add_constraint('task.orgOpenpsaObtype', '=', ORG_OPENPSA_OBTYPE_TASK);
        $qb->add_constraint('task.status', '=', ORG_OPENPSA_TASKSTATUS_COMPLETED);
        // Workgroup filtering
        if ($GLOBALS['org_openpsa_core_workgroup_filter'] != 'all')
        {
            $qb->add_constraint('task.orgOpenpsaOwnerWg', '=', $GLOBALS['org_openpsa_core_workgroup_filter']);
        }
        $ret = $qb->execute();
        if (   is_array($ret)
            && count($ret) > 0)
        {
            foreach ($ret as $resource)
            {
                $this->_add_task_to_list($resource->task, 'completed');
            }
        }

        // Tasks user is manager of that are pending acceptance
        $qb = org_openpsa_projects_task_dba::new_query_builder();
        $qb->add_constraint('status', '=', ORG_OPENPSA_TASKSTATUS_PROPOSED);
        $qb->add_constraint('orgOpenpsaObtype', '=', ORG_OPENPSA_OBTYPE_TASK);
        $qb->add_constraint('manager', '=', $_MIDGARD['user']);
        // Workgroup filtering
        if ($GLOBALS['org_openpsa_core_workgroup_filter'] != 'all')
        {
            $qb->add_constraint('orgOpenpsaOwnerWg', '=', $GLOBALS['org_openpsa_core_workgroup_filter']);
        }
        $ret = $qb->execute();
        if (   is_array($ret)
            && count($ret) > 0)
        {
            foreach ($ret as $task)
            {
                if (!isset($this->_task_cache[$task->id]))
                {
                    $this->_task_cache[$task->id] = $task;
                }
                $this->_add_task_to_list($task->id, 'pending_accept');
            }
        }

        // Tasks user is manager of that are have been declined by all resources
        $qb = org_openpsa_projects_task_dba::new_query_builder();
        $qb->add_constraint('status', '=', ORG_OPENPSA_TASKSTATUS_DECLINED);
        $qb->add_constraint('orgOpenpsaObtype', '=', ORG_OPENPSA_OBTYPE_TASK);
        $qb->add_constraint('manager', '=', $_MIDGARD['user']);
        // Workgroup filtering
        if ($GLOBALS['org_openpsa_core_workgroup_filter'] != 'all')
        {
            $qb->add_constraint('orgOpenpsaOwnerWg', '=', $GLOBALS['org_openpsa_core_workgroup_filter']);
        }
        $ret = $qb->execute();
        if (   is_array($ret)
            && count($ret) > 0)
        {
            foreach ($ret as $task)
            {
                if (!isset($this->_task_cache[$task->id]))
                {
                    $this->_task_cache[$task->id] = $task;
                }
                $this->_add_task_to_list($task->id, 'declined');
            }
        }

        // Tasks user is manager of that are pending approval
        $qb = org_openpsa_projects_task_dba::new_query_builder();
        $qb->add_constraint('status', '=', ORG_OPENPSA_TASKSTATUS_COMPLETED);
        $qb->add_constraint('orgOpenpsaObtype', '=', ORG_OPENPSA_OBTYPE_TASK);
        $qb->add_constraint('manager', '=', $_MIDGARD['user']);
        // Workgroup filtering
        if ($GLOBALS['org_openpsa_core_workgroup_filter'] != 'all')
        {
            $qb->add_constraint('orgOpenpsaOwnerWg', '=', $GLOBALS['org_openpsa_core_workgroup_filter']);
        }
        $ret = $qb->execute();
        if (   is_array($ret)
            && count($ret) > 0)
        {
            foreach ($ret as $task)
            {
                if (!isset($this->_task_cache[$task->id]))
                {
                    $this->_task_cache[$task->id] = $task;
                }
                $this->_add_task_to_list($task->id, 'pending_approve');
            }
        }

        // Tasks user is manager of that are on hold
        $qb = org_openpsa_projects_task_dba::new_query_builder();
        $qb->add_constraint('status', '=', ORG_OPENPSA_TASKSTATUS_ONHOLD);
        $qb->add_constraint('orgOpenpsaObtype', '=', ORG_OPENPSA_OBTYPE_TASK);
        $qb->add_constraint('manager', '=', $_MIDGARD['user']);
        // Workgroup filtering
        if ($GLOBALS['org_openpsa_core_workgroup_filter'] != 'all')
        {
            $qb->add_constraint('orgOpenpsaOwnerWg', '=', $GLOBALS['org_openpsa_core_workgroup_filter']);
        }
        $ret = $qb->execute();
        if (   is_array($ret)
            && count($ret) > 0)
        {
            foreach ($ret as $task)
            {
                if (!isset($this->_task_cache[$task->id]))
                {
                    $this->_task_cache[$task->id] = $task;
                }
                $this->_add_task_to_list($task->id, 'onhold');
            }
        }

        return true;     
    }

    private function _handler_list_project(&$args)
    {
        $this->_request_data['project'] = new org_openpsa_projects_project($args[1]);
        if (!$this->_request_data['project'])
        {
            return false;
        }

        // Query tasks of a project
        $this->_request_data['view'] = 'project_tasks';

        $qb = org_openpsa_projects_task_dba::new_query_builder();
        $qb->add_constraint('up', '=', $this->_request_data['project']->id);
        $qb->add_constraint('orgOpenpsaObtype', '=', ORG_OPENPSA_OBTYPE_TASK);
        //When we have the read-only link to object status etc use those to narrow this down

        $ret = $qb->execute();
        $this->_request_data['tasks'] = array();
        if (   is_array($ret)
            && count($ret) > 0)
        {
            foreach($ret as $task)
            {
                switch ($task->status)
                {
                    case ORG_OPENPSA_TASKSTATUS_PROPOSED:
                        $list = 'proposed';
                        break;
                    case ORG_OPENPSA_TASKSTATUS_ACCEPTED:
                    case ORG_OPENPSA_TASKSTATUS_STARTED:
                    case ORG_OPENPSA_TASKSTATUS_REJECTED:
                    case ORG_OPENPSA_TASKSTATUS_REOPENED:
                    default:
                        $list = 'current';
                        break;
                    case ORG_OPENPSA_TASKSTATUS_COMPLETED:
                        $list = 'completed';
                        break;
                    case ORG_OPENPSA_TASKSTATUS_APPROVED:
                    case ORG_OPENPSA_TASKSTATUS_CLOSED:
                        $list = 'closed';
                        break;
                }
                /*
                $this->_task_cache[$task->id] = &$task;
                $this->_add_task_to_list($task->id, $list);
                */
                $this->_request_data['tasks'][$list][$task->id] = $task;
            }
        }
        return true;
    }

    /**
     * List all tasks, optionally filtered by status
     */
    private function _handler_list_all(&$args)
    {
        $qb = org_openpsa_projects_task_dba::new_query_builder();
        $qb->add_order('up');
        $qb->add_order('customer');
        $qb->add_order('end');
        $qb->add_constraint('orgOpenpsaObtype', '=', ORG_OPENPSA_OBTYPE_TASK);
        // Default to open tasks list if none specified
        if (   !isset($args[1])
            || empty($args[1]))
        {
            $args[1] = 'open';
        }
        switch ($args[1])
        {
            case 'agreement':
                if (!$args[2])
                {
                    return false;
                }
                $agreement_id = (int) $args[2];
                $qb->add_constraint('agreement', '=', $agreement_id);
                break;
            case 'all':
            case 'both':
                $args[1] = 'all';
                break;
            case 'open':
                $this->_component_data['active_leaf'] = "{$this->_topic->id}:tasks_open";
                $qb->add_constraint('status', '<', ORG_OPENPSA_TASKSTATUS_CLOSED);
                break;
            case 'closed':
                $this->_component_data['active_leaf'] = "{$this->_topic->id}:tasks_closed";
                $qb->add_constraint('status', '=', ORG_OPENPSA_TASKSTATUS_CLOSED);
                break;
            case 'current':
                // TODO: Convert to IN constraint once 1.8 is out
                $qb->begin_group('OR');
                    $qb->add_constraint('status', '=', ORG_OPENPSA_TASKSTATUS_ACCEPTED);
                    $qb->add_constraint('status', '=', ORG_OPENPSA_TASKSTATUS_STARTED);
                    $qb->add_constraint('status', '=', ORG_OPENPSA_TASKSTATUS_REJECTED);
                    $qb->add_constraint('status', '=', ORG_OPENPSA_TASKSTATUS_REOPENED);
                $qb->end_group();
                break;
            case 'invoiceable':
                $this->_component_data['active_leaf'] = "{$this->_topic->id}:tasks_invoiceable";
            case 'invoiced':
                // FIXME: We should determine based on agreement instead
                $qb->begin_group('OR');
                    $qb->add_constraint('agreement', '<>', 0);
                    // TODO: This is legacy non-agreement project support, remove as soon as it is possible
                    $qb->add_constraint('hoursInvoiceableDefault', '=', 1);
                $qb->end_group();
                break;
            default:
                debug_add("Filter {$args[1]} not recognized", MIDCOM_LOG_ERROR);
                return false;
                break;
        }
        $this->_request_data['table-heading'] = $args[1] . ' tasks';
        $this->_request_data['view_identifier'] = $args[1];
        $tasks = $qb->execute();
        if ($tasks === false)
        {
            return false;
        }
        $this->_request_data['view'] = 'task_table';
        $this->_request_data['tasks'] = $tasks;
        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_list($handler_id, &$data)
    {
        switch ($this->_request_data['view'])
        {
            case 'task_table':
                midcom_show_style("show-task_table-header");
                $data['even'] = false;

                $data['total_hours'] = Array
                (
                    'invoiceable' => 0,
                    'invoiced'    => 0,
                    'reported'    => 0,
                    'planned'     => 0,
                );

                foreach ($this->_request_data['tasks'] as $task)
                {
                    $data['task'] = $task;
                    $data['hours'] = $task->list_hours();

                    if (   $data['view_identifier'] == 'invoiceable'
                        && $data['hours']['invoiceable'] == 0)
                    {
                        // No invoiceable hours in this task, skip
                        continue;
                    }

                    if (   $data['view_identifier'] == 'invoiced'
                        && $data['hours']['invoiced'] == 0)
                    {
                        // No invoiced hours in this task, skip
                        continue;
                    }

                    $data['total_hours']['invoiceable'] += $data['hours']['invoiceable'];
                    $data['total_hours']['invoiced'] += $data['hours']['invoiced'];
                    $data['total_hours']['reported'] += $data['hours']['reported'];
                    $data['total_hours']['planned'] += $data['task']->plannedHours;

                    $data['cells'] = $this->_get_table_row_data($task, $data);
                    midcom_show_style("show-task_table-item");

                    if ($data['even'])
                    {
                        $data['even'] = false;
                    }
                    else
                    {
                        $data['even'] = true;
                    }
                }
                midcom_show_style("show-task_table-footer");
                break;
            default:
                if (count($this->_request_data['tasks']) > 0)
                {
                    midcom_show_style("show-tasks-header");
                    foreach ($this->_request_data['tasks'] as $list_type => $tasks)
                    {
                        if (count($tasks) == 0)
                        {
                            // No tasks, skip this category
                            continue;
                        }

                        midcom_show_style("show-{$list_type}-tasks-header");

                        foreach ($tasks as $task)
                        {
                            $this->_request_data['task'] = &$task;
                            midcom_show_style("show-{$list_type}-tasks-item");
                        }

                        midcom_show_style("show-{$list_type}-tasks-footer");

                    }
                    midcom_show_style("show-tasks-footer");
                }
                break;
        }
    }
    
    /**
     * Helper to get the relevant data for cells in table view
     */
    private function _get_table_row_data(&$task, &$data)
    {
        $ret = array();
        
        static $row_cache = array
        (
            'parent' => array(),
            'customer' => array(),
            'agreement' => array(),
            'manager' => array(),
        );
        
        // Get parent object
        if (!array_key_exists($task->up, $row_cache['parent']))
        {
            $html = "&nbsp;";
            if ($task->up)
            {
                $parent = $task->get_parent();
                if ($parent->orgOpenpsaObtype == ORG_OPENPSA_OBTYPE_PROJECT)
                {
                    $parent_url = $data['prefix'] . "project/{$parent->guid}/";
                }
                else
                {
                    $parent_url = $data['prefix'] . "task/{$parent->guid}/";
                }
                $html = "<a href=\"{$parent_url}\">{$parent->title}</a>";
            }
            $row_cache['parent'][$task->up] = $html;
        }
        $ret['parent'] =& $row_cache['parent'][$task->up];

        // Get task manager
        if (!array_key_exists($task->manager, $row_cache['manager']))
        {
            $manager = new org_openpsa_contacts_person_dba($task->manager);
            $widget = new org_openpsa_contactwidget($manager);
            $row_cache['manager'][$task->manager] = $widget->show_inline();
        }
        $ret['manager'] =& $row_cache['manager'][$task->manager];

        // Get agreement and customer (if applicable)        
        if ($data['view_identifier'] != 'agreement')
        {
            if (!array_key_exists($task->customer, $row_cache['customer']))
            {
                $html = $this->_l10n->get('no customer');
                if ($task->customer)
                {
                    $customer = new org_openpsa_contacts_group_dba($task->customer);
                    $customer_url = "{$data['contacts_url']}group/{$customer->guid}";
                    $html = "<a href='{$customer_url}'>{$customer->official}</a>";
                }
                $row_cache['customer'][$task->customer] = $html;
            }
            $ret['customer'] =& $row_cache['customer'][$task->customer];
            
            if (!array_key_exists($task->agreement, $row_cache['agreement']))
            {
                $html = "&nbsp;";
                if ($task->agreement)
                {
                    $agreement = new org_openpsa_sales_salesproject_deliverable_dba($task->agreement);
                    $salesproject = new org_openpsa_sales_salesproject_dba($agreement->salesproject);
                    $agreement_url = "{$data['sales_url']}salesproject/{$salesproject->guid}";
                    $html = "<a href='{$agreement_url}'>{$agreement->title}</a>";
                }
                $row_cache['agreement'][$task->agreement] = $html;
            }
            $ret['agreement'] =& $row_cache['agreement'][$task->agreement];            
        }
        
        return $ret;
    }
}
?>