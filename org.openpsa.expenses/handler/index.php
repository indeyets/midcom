<?php
/**
 * @package org.openpsa.expenses
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is a URL handler class for org.openpsa.expenses
 *
 * The midcom_baseclasses_components_handler class defines a bunch of helper vars
 *
 * @see midcom_baseclasses_components_handler
 * @package org.openpsa.expenses
 */
class org_openpsa_expenses_handler_index  extends midcom_baseclasses_components_handler
{

    /**
     * Simple default constructor.
     */
    function __construct()
    {
        parent::__construct();
    }

    /**
     * _on_initialize is called by midcom on creation of the handler.
     */
    function _on_initialize()
    {
    }

    function _calculate_week()
    {
        require_once 'Calendar/Week.php';

        // Get start and end times
        $this->_request_data['this_week'] =& new Calendar_Week(date('Y', $this->_request_data['requested_time']), date('m', $this->_request_data['requested_time']), date('d', $this->_request_data['requested_time']));
        $this->_request_data['prev_week'] = $this->_request_data['this_week']->prevWeek('object');
        $this->_request_data['this_week'] = $this->_request_data['prev_week']->nextWeek('object');
        $this->_request_data['week_start'] = $this->_request_data['this_week']->getTimestamp();
        $this->_request_data['next_week'] = $this->_request_data['this_week']->nextWeek('object');
        $this->_request_data['week_end'] = $this->_request_data['next_week']->getTimestamp() - 1;

        // Build list of days
        $this->_request_data['this_week']->build();
        $this->_request_data['week_days'] = $this->_request_data['this_week']->fetchAll();
    }

    /**
     * The handler for the index article.
     *
     * @param mixed $handler_id the array key from the request array
     * @param array $args the arguments given to the handler
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_index ($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();

        if (isset($args[0]))
        {
            $data['requested_time'] = $args[0];
        }
        else
        {
            $data['requested_time'] = time();
        }

        $this->_calculate_week();

        $hours_mc = org_openpsa_projects_hour_report_dba::new_collector('person', $_MIDGARD['user']);
        $hours_mc->add_value_property('task');
        $hours_mc->add_value_property('hours');
        $hours_mc->add_value_property('date');
                
        $hours_mc->add_constraint('date', '>=', $data['week_start']);
        $hours_mc->add_constraint('date', '<=', $data['week_end']);
        $hours_mc->add_order('task');
        $hours_mc->add_order('date');
        $hours_mc->execute();

        $hours = $hours_mc->list_keys();

        // Sort the reports by task and day
        $tasks = array();
        foreach ($hours as $guid => $empty)
        {
            $task = $hours_mc->get_subkey($guid, 'task');
            $date = $hours_mc->get_subkey($guid, 'date');
            $report_hours = $hours_mc->get_subkey($guid, 'hours');
            if (!isset($tasks[$task]))
            {
                $tasks[$task] = array();
            }

            $date_identifier = date('Y-m-d', $date);
            if (!isset($tasks[$task][$date_identifier]))
            {
                 $tasks[$task][$date_identifier] = 0;
            }
            $tasks[$task][$date_identifier] += $report_hours;
        }
                
        $data['tasks'] =& $tasks;

        $_MIDCOM->add_link_head
        (
                array
            (
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL . "/org.openpsa.expenses/expenses.css",
            )
        );

        $this->_update_breadcrumb_line();

        return true;
    }

    /**
     * Helper, updates the context so that we get a complete breadcrumb line towards the current
     * location.
     */
    private function _update_breadcrumb_line()
    {
        $tmp = Array();

        $tmp[] = array
        (
            MIDCOM_NAV_URL => "",
            MIDCOM_NAV_NAME => sprintf($this->_l10n->get("expenses in week %s"), strftime("%V", $this->_request_data['requested_time'])),
        );

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
    }

    /**
     * This function does the output.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_index($handler_id, &$data)
    {
        midcom_show_style('expenses_index_header');
        midcom_show_style('hours_week');
        midcom_show_style('expenses_index_footer');
    }
}
?>