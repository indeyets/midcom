<?php
/**
 * @package org.openpsa.mypage
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: today.php 3757 2006-07-27 14:32:42Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * My page today handler
 *
 * @package org.openpsa.mypage
 */
class org_openpsa_mypage_handler_today extends midcom_baseclasses_components_handler
{
    var $user = null;

    function __construct()
    {
        parent::__construct();
    }

    function _calculate_day()
    {
        require_once 'Calendar/Day.php';

        // Get start and end times
        $this->_request_data['this_day'] =& new Calendar_Day(date('Y', $this->_request_data['requested_time']), date('m', $this->_request_data['requested_time']), date('d', $this->_request_data['requested_time']));
        $this->_request_data['prev_day'] = $this->_request_data['this_day']->prevDay('object');
        $this->_request_data['day_start'] = $this->_request_data['prev_day']->getTimestamp() + 1;
        $this->_request_data['next_day'] = $this->_request_data['this_day']->nextDay('object');
        $this->_request_data['day_end'] = $this->_request_data['next_day']->getTimestamp() - 1;
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

    function _populate_toolbar()
    {
        $prev_day = date('Y-m-d', $this->_request_data['prev_day']->getTimestamp());
        $next_day = date('Y-m-d', $this->_request_data['next_day']->getTimestamp());
        $this->_view_toolbar->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => "day/{$prev_day}/",
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
                MIDCOM_TOOLBAR_URL => "day/{$next_day}/",
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

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_today($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        $this->user = $_MIDCOM->auth->user->get_storage();

        if ($handler_id == 'today')
        {
            $data['requested_time'] = time();
        }
        else
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
        }

        $this->_calculate_day();
        $this->_calculate_week();

        // List work hours this week
        $hours_qb = org_openpsa_projects_hour_report_dba::new_query_builder();
        $hours_qb->add_constraint('person', '=', $_MIDGARD['user']);
        $hours_qb->add_constraint('date', '>=', $data['week_start']);
        $hours_qb->add_constraint('date', '<=', $data['week_end']);
        $hours_qb->add_order('task');
        $hours_qb->add_order('date');
        $data['hours'] = $hours_qb->execute();

        $this->_populate_toolbar();

        $data['title'] = strftime('%a %x', $data['requested_time']);
        $_MIDCOM->set_pagetitle($data['title']);

        // Add the JS file for "now working on" calculator
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL . "/Pearified/JavaScript/Prototype/prototype.js");
    
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL . "/org.openpsa.mypage/workingon.js");
        $_MIDCOM->add_link_head
        (
            array
            (
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL . "/org.openpsa.mypage/mypage.css",
            )
        );

        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_today($handler_id, &$data)
    {
        $structure = new org_openpsa_core_structure();
        $data['calendar_url'] = $structure->get_node_relative_url('org.openpsa.calendar');
        $data['projects_url'] = $structure->get_node_full_url('org.openpsa.projects');
        $data['expenses_url'] = $structure->get_node_full_url('org.openpsa.expenses');
        $data['wiki_url'] = $structure->get_node_relative_url('net.nemein.wiki');

        midcom_show_style('show-today');
    }
}
?>