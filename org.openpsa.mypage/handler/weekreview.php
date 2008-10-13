<?php
/**
 * @package org.openpsa.mypage
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: weekreview.php 3757 2006-07-27 14:32:42Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * My page weekreview handler
 *
 * @package org.openpsa.mypage
 */
class org_openpsa_mypage_handler_weekreview extends midcom_baseclasses_components_handler
{
    function __construct()
    {
        parent::__construct();
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     */
    function _handler_redirect($handler_id, $args, &$data)
    {
        $date = date('Y-m-d', time());
        $_MIDCOM->relocate("weekreview/{$date}.html");
    }

    function _calculate_week()
    {
        require_once 'Calendar/Week.php';

        // Get start and end times
        $this->_request_data['this_week'] =& new Calendar_Week(date('Y', $this->_request_data['requested_time']), date('m', $this->_request_data['requested_time']), date('d', $this->_request_data['requested_time']));
        $this->_request_data['prev_week'] = $this->_request_data['this_week']->prevWeek('object');
        $this->_request_data['week_start'] = $this->_request_data['prev_week']->getTimestamp() + 1;
        $this->_request_data['next_week'] = $this->_request_data['this_week']->nextWeek('object');
        $this->_request_data['week_end'] = $this->_request_data['next_week']->getTimestamp() - 1;

        // Build list of days
        $this->_request_data['this_week']->build();
        $this->_request_data['week_days'] = $this->_request_data['this_week']->fetchAll();
    }

    function _populate_toolbar()
    {
        $prev_week = date('Y-m-d', $this->_request_data['prev_week']->getTimestamp());
        $next_week = date('Y-m-d', $this->_request_data['next_week']->getTimestamp());
        $this->_view_toolbar->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => "weekreview/{$prev_week}.html",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('previous'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/up.png',
                MIDCOM_TOOLBAR_ENABLED => true,
            )
        );
        $this->_view_toolbar->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => "weekreview/{$next_week}.html",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('next'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/down.png',
                MIDCOM_TOOLBAR_ENABLED => true,
            )
        );

        $this->_view_toolbar->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => "weekreview/",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('week review'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/properties.png',
                MIDCOM_TOOLBAR_ENABLED => true,
            )
        );
    }

    function _list_events_between(&$data_array, $person, $from, $to)
    {
        // List user's event memberships
        $qb = midcom_db_eventmember::new_query_builder();
        $qb->add_constraint('uid', '=', $person);

        // Find all events that occur during [$from, $end]
        $qb->begin_group('OR');
            // The event begins during [$from, $to]
            $qb->begin_group('AND');
                $qb->add_constraint('eid.start', '>=', $from);
                $qb->add_constraint('eid.start', '<=', $to);
            $qb->end_group();
            // The event begins before and ends after [$from, $to]
            $qb->begin_group('AND');
                $qb->add_constraint('eid.start', '<=', $from);
                $qb->add_constraint('eid.end', '>=', $to);
            $qb->end_group();
            // The event ends during [$from, $to]
            $qb->begin_group("AND");
                $qb->add_constraint('eid.end', '>=', $from);
                $qb->add_constraint('eid.end', '<=', $to);
            $qb->end_group();
        $qb->end_group();
        $eventmembers = $qb->execute();

        foreach ($eventmembers as $eventmember)
        {
            $event = new org_openpsa_calendar_event($eventmember->eid);
            $time = $event->start;
            $date = date('Y-m-d', $time);
            if (!array_key_exists($date, $data_array))
            {
                $data_array[$date] = array();
            }
            if (!array_key_exists($time, $data_array[$date]))
            {
                $data_array[$date][$time] = array();
            }
            $data_array[$date][$time][$event->guid] = $event;
        }
    }

    function _list_hour_reports_between(&$data_array, $person, $from, $to)
    {
        // List user's hour reports
        $qb = org_openpsa_projects_hour_report_dba::new_query_builder();
        $qb->add_constraint('date', '>=', $from);
        $qb->add_constraint('date', '<=', $to);
        $qb->add_constraint('person', '=', $person);
        $hour_reports = $qb->execute();

        foreach ($hour_reports as $hour_report)
        {
            $time = mktime(date('H', $hour_report->metadata->created), date('i', $hour_report->metadata->created), date('s', $hour_report->metadata->created), date('m', $hour_report->date), date('d', $hour_report->date), date('Y', $hour_report->date));
            $date = date('Y-m-d', $time);
            if (!array_key_exists($date, $data_array))
            {
                $data_array[$date] = array();
            }
            if (!array_key_exists($time, $data_array[$date]))
            {
                $data_array[$date][$time] = array();
            }
            $data_array[$date][$time][$hour_report->guid] = $hour_report;
        }
    }

    function _list_task_statuses_between(&$data_array, $person, $from, $to)
    {
        // List user's hour reports
        $qb = org_openpsa_projects_task_status_dba::new_query_builder();
        $qb->add_constraint('timestamp', '>=', $from);
        $qb->add_constraint('timestamp', '<=', $to);
        $qb->begin_group('OR');
            $qb->add_constraint('targetPerson', '=', $person);
            $qb->add_constraint('creator', '=', $person);
        $qb->end_group();
        $task_statuses = $qb->execute();

        foreach ($task_statuses as $task_status)
        {
            $time = $task_status->timestamp;
            $date = date('Y-m-d', $time);
            if (!array_key_exists($date, $data_array))
            {
                $data_array[$date] = array();
            }
            if (!array_key_exists($time, $data_array[$date]))
            {
                $data_array[$date][$time] = array();
            }
            $data_array[$date][$time][$task_status->guid] = $task_status;
        }
    }

    function _list_positions_between(&$data_array, $person, $from, $to)
    {
        if (!$GLOBALS['midcom_config']['positioning_enable'])
        {
            return false;
        }
        
        $_MIDCOM->load_library('org.openpsa.positioning');
        
        // List user's position reports
        $qb = org_routamc_positioning_log_dba::new_query_builder();
        $qb->add_constraint('date', '>=', $from);
        $qb->add_constraint('date', '<=', $to);
        $qb->add_constraint('person', '=', $person);
        $positions = $qb->execute();

        foreach ($positions as $position)
        {
            $time = $position->date;
            $date = date('Y-m-d', $time);
            if (!array_key_exists($date, $data_array))
            {
                $data_array[$date] = array();
            }
            if (!array_key_exists($time, $data_array[$date]))
            {
                $data_array[$date][$time] = array();
            }
            $data_array[$date][$time][$position->guid] = $position;
        }
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_review($handler_id, $args, &$data)
    {
        // TODO: Check format as YYYY-MM-DD via regexp
        $requested_time = @strtotime($args[0]);
        if ($requested_time)
        {
            $data['requested_time'] = $requested_time;
        }
        else
        {
            // We couldn't generate a date
            return false;
        }

        // Calculate start and end times
        $this->_calculate_week();

        // Empty the data array
        $data['review_data'] = array();

        // Then start looking for stuff to display
        $this->_list_events_between(&$data['review_data'], $_MIDGARD['user'], $data['week_start'], $data['week_end']);
        $this->_list_hour_reports_between(&$data['review_data'], $_MIDGARD['user'], $data['week_start'], $data['week_end']);
        $this->_list_task_statuses_between(&$data['review_data'], $_MIDGARD['user'], $data['week_start'], $data['week_end']);
        $this->_list_positions_between(&$data['review_data'], $_MIDGARD['user'], $data['week_start'], $data['week_end']);

        // Arrange by date/time
        ksort($data['review_data']);

        // Set page title
        if ($data['requested_time'] > time())
        {
            $title_string = 'preview for week %s';
        }
        else
        {
            $title_string = 'review of week %s';
        }

        $data['title'] = sprintf($this->_l10n->get($title_string), strftime('%W %Y', $data['requested_time']));
        $_MIDCOM->set_pagetitle($data['title']);

        $this->_populate_toolbar();
        $this->_view_toolbar->hide_item('weekreview/');

        $tmp = Array();
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => 'weekreview/',
            MIDCOM_NAV_NAME => $this->_l10n->get('week review'),
        );
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);

        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_review($handler_id, &$data)
    {
        $structure = new org_openpsa_core_structure();
        $data['calendar_node'] = midcom_helper_find_node_by_component('org.openpsa.calendar');
        $data['projects_url'] = $structure->get_node_full_url('org.openpsa.projects');

        $week_hours_invoiceable = 0;
        $week_hours_total = 0;

        midcom_show_style('weekreview-header');
        foreach ($data['week_days'] as $data['day'])
        {
            $data['day_start'] = $data['day']->getTimestamp();
            $next_day = $data['day']->nextDay('object');
            $data['day_end'] = $next_day->getTimestamp() - 1;

            midcom_show_style('weekreview-day-header');

            $date = date('Y-m-d', $data['day_start']);

            if (!array_key_exists($date, $data['review_data']))
            {
                // Nothing for today
                continue;
            }

            $day_hours_invoiceable = 0;
            $day_hours_total = 0;

            // Arrange entries per time
            ksort($data['review_data'][$date]);

            foreach ($data['review_data'][$date] as $time => $guids)
            {
                foreach ($guids as $guid => $object)
                {
                    $data['time'] = $time;
                    $data['object'] = $object;
                    switch (get_class($object))
                    {
                        case 'org_openpsa_calendar_event':
                            midcom_show_style('weekreview-day-item-event');
                            break;
                        case 'org_openpsa_projects_hour_report_dba':
                            midcom_show_style('weekreview-day-item-hour-report');

                            if ($object->invoiceable)
                            {
                                $day_hours_invoiceable += $object->hours;
                            }
                            $day_hours_total += $object->hours;

                            break;
                        case 'org_openpsa_projects_task_status_dba':
                            midcom_show_style('weekreview-day-item-task-status');
                            break;
                        case 'org_routamc_positioning_log_dba':
                            midcom_show_style('weekreview-day-item-position');
                            break;
                    }
                }
            }

            $data['day_hours_invoiceable'] = $day_hours_invoiceable;
            $week_hours_invoiceable += $day_hours_invoiceable;
            $data['day_hours_total'] = $day_hours_total;
            $week_hours_total += $day_hours_total;

            midcom_show_style('weekreview-day-footer');
        }
        $data['week_hours_invoiceable'] = $week_hours_invoiceable;
        $data['week_hours_total'] = $week_hours_total;
        midcom_show_style('weekreview-footer');
    }
}
?>