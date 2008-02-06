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
class org_openpsa_expenses_handler_hours_list extends midcom_baseclasses_components_handler
{

    /**
     * The Datamanager of the hour reports to display.
     *
     * @var midcom_helper_datamanager2_datamanager
     * @access private
     */
    var $_datamanager = null;

    /**
     * Simple default constructor.
     */
    function org_openpsa_expenses_handler_hours_list()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * Internal helper, loads the datamanager for the current article. Any error triggers a 500.
     *
     * @access private
     */
    function _load_datamanager()
    {
        $this->_datamanager = new midcom_helper_datamanager2_datamanager($this->_request_data['schemadb_hours']);

        if (!$this->_datamanager)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to create a DM2 instance for hour reports.");
            // This will exit.
        }
    }

    /**
     * Prepare a paged query builder for listing photos
     */
    function &_prepare_qb()
    {
        $qb = new org_openpsa_qbpager('org_openpsa_projects_hour_report', 'org_openpsa_projects_hour_report');
        $qb->results_per_page = 30;
        $this->_request_data['qb'] =& $qb;
        return $qb;
    }

    /**
     * The handler for the index article.
     *
     * @param mixed $handler_id the array key from the request array
     * @param array $args the arguments given to the handler
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_list($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();

        // List photos
        $qb =& $this->_prepare_qb();

        $show_all = false;
        switch ($handler_id)
        {
            case 'list_hours_between':
                $qb->add_constraint('person', '=', $_MIDGARD['user']);
                // Fallthrough
            case 'list_hours_between_all':
                $start_time = @strtotime($args[0]);
                $end_time = @strtotime($args[1]);
                if (   $start_time == -1
                    || $end_time == -1)
                {
                    return false;
                }
                $qb->add_constraint('date', '>=', $start_time);
                $qb->add_constraint('date', '<=', $end_time);
                break;

            case 'list_hours_task':
                $qb->add_constraint('person', '=', $_MIDGARD['user']);
                // Fallthrough
            case 'list_hours_task_all':
                $task = new org_openpsa_projects_task($args[0]);
                if (   !$task
                    || !$task->guid)
                {
                    // No such task
                    return false;
                }
                $qb->add_constraint('task', '=', $task->id);
                break;
        }

        $qb->add_order('date', 'DESC');
        $data['hours'] = $qb->execute();

        $data['view_title'] = $data['l10n']->get($handler_id);

        $this->_load_datamanager();

        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$data['view_title']}");
        //$this->_update_breadcrumb_line($handler_id);

        return true;
    }

    /**
     * This function does the output.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_list($handler_id, &$data)
    {
        midcom_show_style('hours_list_header');

        $total_hours = 0;
        foreach ($data['hours'] as $hour_report)
        {
            $this->_datamanager->autoset_storage($hour_report);
            $data['hour_report'] = $hour_report;
            $data['view_hour_report'] = $this->_datamanager->get_content_html();
            $total_hours += $hour_report->hours;

            midcom_show_style('hours_list_item');
        }

        $data['total_hours'] = $total_hours;
        midcom_show_style('hours_list_footer');
    }
}
?>