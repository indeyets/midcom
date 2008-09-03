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
    function org_openpsa_expenses_handler_index()
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

        $data['requested_time'] = time();

        $this->_calculate_week();

        $hours_qb = org_openpsa_projects_hour_report::new_query_builder();
        $hours_qb->add_constraint('person', '=', $_MIDGARD['user']);
        $hours_qb->add_constraint('date', '>=', $data['week_start']);
        $hours_qb->add_constraint('date', '<=', $data['week_end']);
        $hours_qb->add_order('task');
        $hours_qb->add_order('date');
        $data['hours'] = $hours_qb->execute();

        return true;
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