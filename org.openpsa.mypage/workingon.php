<?php
/**
 * @package org.openpsa.mypage
 * @author Nemein Oy http://www.nemein.com/
 * @version $Id: admin.php,v 1.1 2005/06/20 17:49:05 rambo Exp $
 * @copyright Nemein Oy http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * org.openpsa.mypage "now working on" handler
 *
 * @package org.openpsa.mypage
 */
class org_openpsa_mypage_workingon
{
    /**
     * Time person started working on the task
     * @access protected
     */
    var $start = 0;

    /**
     * Time spent working on the task, in seconds
     * @access protected
     */
    var $time = 0;

    /**
     * Task being worked on
     * @access protected
     */
    var $task = null;

    /**
     * Person working on the task
     * @access protected
     */
    var $person = null;

    /**
     * Constructor.
     *
     *�@param midcom_db_person $person Person to handle "now working on" for. By default current user
     */
    function __construct($person = null)
    {
        if (is_null($person))
        {
            $_MIDCOM->auth->require_valid_user();
            $this->person = $_MIDCOM->auth->user->get_storage();
        }
        else
        {
            // TODO: Check that this is really a person object
            $this->person =& $person;
        }

        // Figure out what the person is working on
        $this->_get();
    }

    /**
     * Load task and time person is working on
     */
    function _get()
    {
        $task_guid = $this->person->get_parameter('org.openpsa.mypage:workingon', 'task');
        if (!$task_guid)
        {
            // Person isn't working on anything at the moment
            return;
        }

        $task_time = $this->person->get_parameter('org.openpsa.mypage:workingon', 'start');
        if (!$task_time)
        {
            // The time worked on is not available, remove task as well
            $this->person->delete_parameter('org.openpsa.mypage:workingon', 'task');
            return false;
        }
        $task_time = strtotime("{$task_time} GMT");

        // Set the protected vars
        $this->task = new org_openpsa_projects_task($task_guid);
        $this->time = time() - $task_time;
        $this->start = $task_time;

        return true;
    }

    /**
     * Set a task the user works on. If user was previously working on something else hours will be reported automatically.
     */
    function set($task_guid = '')
    {
        if ($this->task)
        {
            // We were previously working on another task. Report hours
            // Generate a message
            $description = sprintf($_MIDCOM->i18n->get_string('worked from %s to %s', 'org.openpsa.mypage'), strftime('%x %X', $this->start), strftime('%x %X', time()));

            // Do the actual report
            $this->_report_hours($description);
        }

        if ($task_guid == '')
        {
            // We won't be working on anything from now on. Delete existing parameters
            $this->person->set_parameter('org.openpsa.mypage:workingon', 'task', '');
            $stat = $this->person->set_parameter('org.openpsa.mypage:workingon', 'start', '');
            return $stat;
        }

        // Mark the new task work session as started
        $this->person->set_parameter('org.openpsa.mypage:workingon', 'task', $task_guid);
        $stat = $this->person->set_parameter('org.openpsa.mypage:workingon', 'start', gmdate('Y-m-d H:i:s', time()));
        return $stat;
    }

    /**
     * Report hours based on time used
     * @access private
     * @return boolean
     */
    function _report_hours($description)
    {
        $hour_report = new org_openpsa_projects_hour_report();
        $hour_report->date = $this->start;
        $hour_report->person = $this->person->id;
        $hour_report->task = $this->task->id;
        $hour_report->description = $description;
        $hour_report->hours = $this->time / 3600; // TODO: Round?
        $stat = $hour_report->create();
        if (!$stat)
        {
            $_MIDCOM->uimessages->add($_MIDCOM->i18n->get_string('org.openpsa.mypage', 'org.openpsa.mypage'), sprintf($_MIDCOM->i18n->get_string('reporting %d hours to task %s failed, reason %s', 'org.openpsa.mypage'), $hour_report->hours, $this->task->title, mgd_errstr()), 'error');
            return false;
        }
        $_MIDCOM->uimessages->add($_MIDCOM->i18n->get_string('org.openpsa.mypage', 'org.openpsa.mypage'), sprintf($_MIDCOM->i18n->get_string('successfully reported %d hours to task %s', 'org.openpsa.mypage'), $hour_report->hours, $this->task->title), 'ok');
        return true;
    }

    /**
     * Return the elapsed time in a nicely formatted fashion
     *
     * Keep in sync with the workingOnCalculator.formatTime JS function
     */
    function format_time()
    {
        $seconds = floor($this->time % 60);
        $minutes = floor($this->time / 60) % 60;
        $hours = floor($this->time / 3600) % 3600;

        return sprintf('%d:%02d:%02d', $hours, $minutes, $seconds);
    }
}
?>