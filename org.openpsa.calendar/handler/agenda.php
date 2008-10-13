<?php
/**
 * @package org.openpsa.calendar
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: report.php,v 1.2 2006/05/10 16:26:10 rambo Exp $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Calendar agenda handler
 *
 * @package org.openpsa.calendar
 */
class org_openpsa_calendar_handler_agenda extends midcom_baseclasses_components_handler
{
    function __construct()
    {
        parent::__construct();
    }

    function _on_initialize()
    {
        $_MIDCOM->load_library('org.openpsa.calendarwidget');
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_day($handler_id, $args, &$data)
    {
        // Generate start/end timestamps for the day
        $requested_time = @strtotime($args[0]);
        if (!$requested_time)
        {
            return false;
        }

        // Use calendarwidget for time calculations
        $this->_request_data['calendar'] = new org_openpsa_calendarwidget(date('Y', $requested_time), date('m', $requested_time), date('d', $requested_time));
        $this->_request_data['calendar']->type = ORG_OPENPSA_CALENDARWIDGET_DAY;

        $from = $this->_request_data['calendar']->get_day_start();
        $to = $this->_request_data['calendar']->get_day_end();

        // List user's event memberships
        $qb = midcom_db_eventmember::new_query_builder();
        $qb->add_constraint('uid', '=', $_MIDGARD['user']);

        // Find all events that occur during [$from, $end]
        $qb->begin_group("OR");
            // The event begins during [$from, $to]
            $qb->begin_group("AND");
                $qb->add_constraint("eid.start", ">=", $from);
                $qb->add_constraint("eid.start", "<=", $to);
            $qb->end_group();
            // The event begins before and ends after [$from, $to]
            $qb->begin_group("AND");
                $qb->add_constraint("eid.start", "<=", $from);
                $qb->add_constraint("eid.end", ">=", $to);
            $qb->end_group();
            // The event ends during [$from, $to]
            $qb->begin_group("AND");
                $qb->add_constraint("eid.end", ">=", $from);
                $qb->add_constraint("eid.end", "<=", $to);
            $qb->end_group();
        $qb->end_group();

        $eventmembers = $qb->execute();
        $this->_request_data['events'] = array();
        foreach ($eventmembers as $eventmember)
        {
            $this->_request_data['events'][] = new org_openpsa_calendar_event($eventmember->eid);
        }

        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_day($handler_id, &$data)
    {
        midcom_show_style('show-day-header');

        foreach ($this->_request_data['events'] as $event)
        {
            $this->_request_data['event'] = $event;
            midcom_show_style('show-day-item');
        }

        midcom_show_style('show-day-footer');
    }
}
?>