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
     * The reporter cache
     *
     * @var Array
     * @access private
     */
    private $reporters = array();

    /**
     * The task cache
     *
     * @var Array
     * @access private
     */
    private $tasks = array();

    /**
     * Simple default constructor.
     */
    function __construct()
    {
        parent::__construct();
    }

    /**
     * Prepare a paged query builder for listing photos
     */
    function &_prepare_qb()
    {
        $qb = new org_openpsa_qbpager('org_openpsa_projects_hour_report_dba', 'org_openpsa_projects_hour_report_dba');
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

        $_MIDCOM->componentloader->load('org.openpsa.contactwidget');

        // List hours
        $qb =& $this->_prepare_qb();

        $show_all = false;
        $mode = 'full';

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

                $data['view_title'] = $data['l10n']->get($handler_id);
                break;

            case 'list_hours_task':
                $qb->add_constraint('person', '=', $_MIDGARD['user']);
                // Fallthrough
            case 'list_hours_task_all':
                $task = new org_openpsa_projects_task_dba($args[0]);
                if (   !$task
                    || !$task->guid)
                {
                    // No such task
                    return false;
                }
                $qb->add_constraint('task', '=', $task->id);

                $mode = 'simple';
                $data['view_title'] = sprintf($data['l10n']->get($handler_id . " %s"), $task->get_label());
                break;
        }

        $qb->add_order('date', 'DESC');
        $data['hours'] = $qb->execute();

        $this->load_hour_data($data['hours']);

        $data['mode'] = $mode;


        $_MIDCOM->add_link_head
        (
                array
            (
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL . "/org.openpsa.core/list.css",
            )
        );

        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$data['view_title']}");
        $this->_update_breadcrumb_line();

        return true;
    }

    /**
     * Helper to load the data linked to the hour reports
     *
     * @param array &$hours the hour reports we're working with
     */
    private function load_hour_data(&$hours)
    {
        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
        foreach($hours as $report)
        {
            if (!array_key_exists($report->person, $this->reporters))
            {
                $reporter = new midcom_db_person($report->person);
                $reporter_card = new org_openpsa_contactwidget($reporter);
                $this->reporters[$report->person] = $reporter_card->show_inline();
            }

            if (!array_key_exists($report->task, $this->tasks))
            {
                $task = new org_openpsa_projects_task_dba($report->task);
                $task_html = "<a href=\"{$prefix}hours/task/{$task->guid}/\">" . $task->get_label() . "</a>";
                $this->tasks[$report->task] = $task_html;
            }


        }
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
            MIDCOM_NAV_NAME => $this->_request_data['view_title'],
        );

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
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

        $data['reporters'] =& $this->reporters;

        $total_hours = 0;
        $class = "even";

        foreach ($data['hours'] as $hour_report)
        {
            if ($class == "even")
            {
                $class = "odd";
            }
            else
            {
                $class = "even";
            }
            $data['class'] = $class;
            $data['hour_report'] = $hour_report;
            $total_hours += $hour_report->hours;

            midcom_show_style('hours_list_item');
        }

        $data['total_hours'] = $total_hours;
        midcom_show_style('hours_list_footer');
    }
}
?>