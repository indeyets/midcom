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

    function __construct()
    {
        parent::__construct();
    }

    function _on_initialize()
    {
    }

    function _load_task($identifier)
    {
        $task = new org_openpsa_projects_task($identifier);

        if (!is_object($task))
        {
            return false;
        }

        // We must load schemadb only after that is in request_data so we can populate the resource pulldown
        $this->_request_data['task'] =& $task;
        $this->_request_data['schemadb_task_dm2'] = midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb_task_dm2'));

        $this->_request_data['controller'] =& midcom_helper_datamanager2_controller::create('ajax');
        $this->_request_data['controller']->schemadb =& $this->_request_data['schemadb_task_dm2'];
        $this->_request_data['controller']->set_storage($task);
        $this->_request_data['controller']->process_ajax();

        return $task;
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
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

        if (   count($args) == 1
            && $this->_request_data['task']->can_do('midgard:update'))
        {
            //$this->_initialize_hours_widget(&$this->_request_data['task']);
            $this->_view_toolbar->add_item(
                Array(
                    MIDCOM_TOOLBAR_URL => "task/edit/{$data['task']->guid}/",
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('edit'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
                    MIDCOM_TOOLBAR_ENABLED => true,
                )
            );

            if ($data['task']->hourCache == 0)
            {
                // Enable deletion only if there are no hours
                $this->_view_toolbar->add_item
                (
                    array
                    (
                        MIDCOM_TOOLBAR_URL => "task/delete/{$data['task']->guid}/",
                        MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('delete'),
                        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
                        MIDCOM_TOOLBAR_ENABLED => $data['task']->can_do('midgard:delete'),
                    )
                );
            }

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
            // Load "Create X" buttons for all the related info
            $relatedto_button_settings = org_openpsa_relatedto_handler::common_toolbar_buttons_defaults();
            $relatedto_button_settings['wikinote']['wikiword'] = sprintf($this->_request_data['l10n']->get('notes for task %s on %s'), $this->_request_data['task']->title, date('Y-m-d H:i'));

            // No sense to create tasks related to task?
            unset($relatedto_button_settings['task']);

            org_openpsa_relatedto_handler::common_node_toolbar_buttons($this->_view_toolbar, $this->_request_data['task'], $this->_component, $relatedto_button_settings);
        }

        $data['calendar_node'] = midcom_helper_find_node_by_component('org.openpsa.calendar');

        $_MIDCOM->bind_view_to_object($data['task'], $data['controller']->datamanager->schema->name);

        $breadcrumb = org_openpsa_projects_viewer::update_breadcrumb_line($data['task']);

        if ($handler_id == 'task_view_related')
        {
            $breadcrumb[] = array
            (
                MIDCOM_NAV_URL => "task/related/{$data['task']->guid}/",
                MIDCOM_NAV_NAME => $data['l10n']->get('view related information'),
            );
        }

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $breadcrumb);
        return true;
    }

    function _list_bookings($task)
    {
        $this->_request_data['task_booked_time'] = 0;
        $bookings = array
        (
            'confirmed' => array(),
            'suspected' => array(),
        );
        $qb = org_openpsa_relatedto_relatedto_dba_dba::new_query_builder();
        $qb->add_constraint('toGuid', '=', $task->guid);
        $qb->add_constraint('fromComponent', '=', 'org.openpsa.calendar');
        $qb->add_constraint('status', '<>', ORG_OPENPSA_RELATEDTO_STATUS_NOTRELATED);
        // TODO: fromClass too?
        $relations = $qb->execute();
        foreach ($relations as $relation)
        {
            $booking = new org_openpsa_calendar_event($relation->fromGuid);
            if (!$booking)
            {
                continue;
            }

            if ($relation->status == ORG_OPENPSA_RELATEDTO_STATUS_CONFIRMED)
            {
                $bookings['confirmed'][] = $booking;

                $this->_request_data['task_booked_time'] += ($booking->end - $booking->start) / 3600;
            }
            else
            {
                $bookings['suspected'][] = $booking;
            }
        }

        // Sort by start time (uses an anonymous function since usort cannot use object methods even statically)
        usort($bookings['confirmed'], create_function('$a,$b', $this->_code_for_sort_by_time()));
        usort($bookings['suspected'], create_function('$a,$b', $this->_code_for_sort_by_time()));

        $this->_request_data['task_booked_time'] = round($this->_request_data['task_booked_time']);

        if ($task->plannedHours == 0)
        {
            $this->_request_data['task_booked_percentage'] = 100;
        }
        else
        {
            $this->_request_data['task_booked_percentage'] = round(100 / $task->plannedHours * $this->_request_data['task_booked_time']);
        }

        return $bookings;
    }

    /**
     * Code to sort array of events by $event->start, from smallest to greatest
     *
     * Used by $this->_list_bookings()
     */
    function _code_for_sort_by_time()
    {
        return <<<EOF
        \$ap = \$a->start;
        \$bp = \$b->start;
        if (\$ap > \$bp)
        {
            return 1;
        }
        if (\$ap < \$bp)
        {
            return -1;
        }
        return 0;
EOF;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_view($handler_id, &$data)
    {
        if ($handler_id == 'task_view')
        {
            $data['view_task'] = $data['controller']->get_content_html();
            $data['datamanager'] =& $data['controller']->datamanager;

            //$this->_request_data['task_dm']  = $this->_datamanagers['task'];
            $data['task_bookings'] = $this->_list_bookings($data['task']);

            midcom_show_style('show-task');
        }
        else
        {
            midcom_show_style('show-task-related');
        }
    }

    /*
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
    }*/
}
?>