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

    function __construct()
    {
        parent::__construct();
    }

    function _on_initialize()
    {
    }

    function _load_task($identifier)
    {
        $task = new org_openpsa_projects_task_dba($identifier);

        if (!is_object($task))
        {
            return false;
        }

        $task->get_members();

        // We must load schemadb only after that is in request_data so we can populate the resource pulldown
        $this->_request_data['task'] =& $task;
        $schemadb = midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb_task'));
        $this->_request_data['datamanager'] = new midcom_helper_datamanager2_datamanager($schemadb);
        if (   ! $this->_request_data['datamanager']
            || ! $this->_request_data['datamanager']->autoset_storage($task))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to create a DM2 instance for project {$task->id}.");
            // This will exit.
        }
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
            $this->_view_toolbar->add_item
            (
                array
                (
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
                $this->_view_toolbar->add_item
                (
                    array
                    (
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
                $this->_view_toolbar->add_item
                (
                    array
                    (
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
            $this->_view_toolbar->add_item
            (
                array
                (
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

        $_MIDCOM->bind_view_to_object($data['task'], $data['datamanager']->schema->name);

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
        $mc = org_openpsa_relatedto_relatedto_dba::new_collector('toGuid', $task->guid);
        $mc->add_value_property('status');
        $mc->add_value_property('fromGuid');
        $mc->add_constraint('fromComponent', '=', 'org.openpsa.calendar');
        $mc->add_constraint('status', '<>', ORG_OPENPSA_RELATEDTO_STATUS_NOTRELATED);
        // TODO: fromClass too?
        $mc->execute();
        
        $relations = $mc->list_keys();
        foreach ($relations as $guid => $empty)
        {
            $booking = new org_openpsa_calendar_event_dba($mc->get_subkey($guid, 'fromGuid'));
            if (!$booking)
            {
                continue;
            }

            if ($mc->get_subkey($guid, 'status') == ORG_OPENPSA_RELATEDTO_STATUS_CONFIRMED)
            {
                $bookings['confirmed'][] = $booking;

                $this->_request_data['task_booked_time'] += ($booking->end - $booking->start) / 3600;
            }
            else
            {
                $bookings['suspected'][] = $booking;
            }
        }

        usort($bookings['confirmed'], array('org_openpsa_projects_handler_task_view', '_sort_by_time'));
        usort($bookings['suspected'], array('org_openpsa_projects_handler_task_view', '_sort_by_time'));

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
    private static function _sort_by_time($a, $b)
    {
        $ap = $a->start;
        $bp = $b->start;
        if ($ap > $bp)
        {
            return 1;
        }
        if ($ap < $bp)
        {
            return -1;
        }
        return 0;
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
            $data['view_task'] = $data['datamanager']->get_content_html();
            $data['datamanager'] =& $data['datamanager'];

            $data['task_bookings'] = $this->_list_bookings($data['task']);

            midcom_show_style('show-task');
        }
        else
        {
            midcom_show_style('show-task-related');
        }
    }

}
?>