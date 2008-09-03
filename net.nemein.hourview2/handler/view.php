<?php
/**
 * @package net.nemein.hourview2
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id: viewer.php 5405 2007-02-23 14:57:51Z rambo $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Net.nemein.hourview2 handler for viewing and approving hours
 * 
 * @package net.nemein.hourview2
 */
class net_nemein_hourview2_handler_view extends midcom_baseclasses_components_handler
{
    /**
     * Array for user's agreements
     * 
     * @var Array of org_openpsa_salesproject_deliverable objects
     */
     var $_agreements = Array();
     
    /**
     * Array for cached tasks
     * 
     * @var org_openpsa_task
     */
     var $_tasks = Array();
     
     /**
      * Constructor, connect to parent class constructor.
      * 
      * @access public
      */
     function net_nemein_hourview2_handler_view()
     {
         parent::__construct();
     }
     
    function _on_initialize()
    {
        // Require authenticated used
        $_MIDCOM->auth->require_valid_user();
        
        // Add link head
        $_MIDCOM->add_link_head
        (
            array
            (
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL.'/net.nemein.hourview2/hourview.css',
            )
        );
    }
    
    function _list_agreements()
    {
        // List agreements where user is part of
        $qb_member = org_openpsa_sales_salesproject_member::new_query_builder();
        $qb_member->add_constraint('person', '=', $_MIDGARD['user']);
        $memberships = $qb_member->execute_unchecked();
        
        if (count($memberships) > 0)
        {
        
            $qb_agreement = org_openpsa_sales_salesproject_deliverable::new_query_builder();
            $qb_agreement->add_constraint('state', '>=', 400); // ORG_OPENPSA_SALESPROJECT_DELIVERABLE_STATUS_ORDERED

            $qb_agreement->begin_group('OR');
                foreach ($memberships as $member)
                {
                    $qb_agreement->add_constraint('salesproject', '=', $member->salesproject);
                }
            $qb_agreement->end_group();
            
            $agreements = $qb_agreement->execute_unchecked();
            
            foreach ($agreements as $agreement)
            {
                $this->_agreements[$agreement->id] = $agreement;
            }
        }
    }
    
    function _list_hours($hide_approved = true)
    {
        $hours_by_task = array();
        
        // By default list hour reports from last two months
        $start_timestamp = mktime(0,0,0,date('n')-2,1,date('Y'));
        
        /*if (array_key_exists("net_nemein_hourview2_view_all",$_REQUEST))
        {
            // User has requested all hour reports
            $start_timestamp = 0;
            $hide_approved = false;
        }*/
        
        // List hour reports until yesterday
        $end_timestamp = mktime(0,0,0,date('n'),date('j'),date('Y'));
        
        if (count($this->_agreements) == 0)
        {
            return Array();
        }
        
        // List tasks user is customer in
        $task_qb = org_openpsa_projects_task::new_query_builder();
        
        $task_qb->begin_group('OR');
        foreach ($this->_agreements as $agreement)
        {
            $task_qb->add_constraint('agreement', '=', $agreement->id);
        }
        $task_qb->end_group();
        //$task_qb->add_constraint('status', '>=', 6560); // ORG_OPENPSA_TASKSTATUS_COMPLETED
        $tasks = $task_qb->execute_unchecked();
        foreach ($tasks as $task)
        {
            $this->_tasks[$task->id] = $task;
        }
        
        // No tasks where current user is a customer
        if (count($this->_tasks) == 0)
        {
            return Array();
        }
        
        // List hour reports
        $qb = org_openpsa_projects_hour_report::new_query_builder();

        if ($hide_approved)
        {
            $qb->add_constraint('approver', '=', 0);
        }
        
        // Always skip those that are invoiced
        $qb->add_constraint('invoiced', '<=', '0000-00-00 00:00:00');

        $qb->add_constraint('invoiceable', '=', 1);
        $qb->begin_group('OR');
        foreach ($this->_tasks as $task)
        {
            $qb->add_constraint('task', '=', $task->id);
        }
        $qb->end_group();
        $hours = $qb->execute_unchecked();
        
        foreach ($hours as $hour_report)
        {
            $hours_by_task[$hour_report->task][$hour_report->id] = $hour_report;
        }
        
        return $hours_by_task;
    }
    
    function _process_approvals()
    {
        // Handle approvals
        ignore_user_abort(true);
        ini_set('max_execution_time', 0);
        
        // Get user
        $user = new midcom_db_person($_MIDGARD['user']);
        
        $hours_by_task = $this->_list_hours();
        foreach ($hours_by_task as $task_id => $hours)
        {
            $hour_reports_approved = array();
            $hour_reports_failedapproval = array();
            $hour_reports_notapproved = array();        
        
            // Get the task
            $task = $this->_tasks[$task_id];
            
            // Run through the hours
            $_MIDCOM->auth->request_sudo('net.nemein.hourview2');
            foreach ($hours as $hour_report)
            {
                if (   array_key_exists('net_nemein_hourview2_approve', $_POST)
                    && array_key_exists($hour_report->id, $_POST['net_nemein_hourview2_approve'])
                    && $_POST['net_nemein_hourview2_approve'][$hour_report->id] == 1)
                {
                    if ($hour_report->approve())
                    {
                        //Approved successfully
                        $hour_reports_approved[] = $hour_report;
                    } 
                    else
                    {
                        //Error handling
                        $hour_reports_failedapproval[] = $hour_report;
                        $_MIDCOM->uimessages->add($this->_l10n->get('net.nemein.hourview2'), sprintf($this->_l10n->get('failed to approve hour report #%s, reason %s'), $hour_report->id, mgd_errstr()), 'error');
                    }
                }
                else
                {
                    // User did not approve this hour report
                    $hour_reports_notapproved[] = $hour_report;
                }
            }
            $_MIDCOM->auth->drop_sudo();
            
            // Get the manager, he will receive some spam
            $manager = new midcom_db_person($task->manager);
                    
            $message = Array();
            $message['content'] = sprintf($this->_l10n->get('%s has processed the following hour reports in task %s'), $user->name, $task->title) . "\n\n";
        
            if (count($hour_reports_approved) > 0)
            {
                $_MIDCOM->uimessages->add($this->_l10n->get('net.nemein.hourview2'), sprintf($this->_l10n->get('approved %s hour reports in task "%s"'), count($hour_reports_approved), $task->title), 'ok');
                
                $message['content'] .= $this->_l10n->get('approved hours') . ":\n";
                foreach ($hour_reports_approved as $hour_report)
                {
                    $reporter = new midcom_db_person($hour_report->person);
                    $message['content'] .= strftime('%x', $hour_report->date) . ": {$hour_report->hours}h, {$reporter->lastname} {$reporter->name}\n";
                }
            }
        
            if (count($hour_reports_notapproved) > 0)
            {
                $message['content'] .= "\n" . $this->_l10n->get('not approved hours') . ":\n";
                foreach ($hour_reports_notapproved as $hour_report)
                {
                    $reporter = new midcom_db_person($hour_report->person);
                    $message['content'] .= strftime('%x',$hour_report->date) . ": {$hour_report->hours}h, {$reporter->lastname} {$reporter->name}\n";
                }
            }
            
            if (count($hour_reports_failedapproval) > 0)
            {
                $message['content'] .= "\n" . $this->_l10n->get('approval failed for hours') . ":\n";
                foreach ($hour_reports_failedapproval as $hour_report)
                {
                    $reporter = new midcom_db_person($hour_report->person);
                    $message['content'] .= strftime('%x', $hour_report->date) . ": {$hour_report->hours}h, {$reporter->name}\n";
                }
            }
                        
            $message['content'] .= "\n" . $this->_l10n->get('comments') . ":\n" . $_POST['net_nemein_hourview2_comments'];
            
            $message['title'] = sprintf($this->_l10n->get('processed hour reports in %s'), $task->title);
            
            // Notify project manager
            org_openpsa_notifications::notify('org.openpsa.projects:hour_reports_approved', $manager->guid, $message);
            
            //Send the notification also to the owner of the salesproject
            $salesproject = new org_openpsa_sales_salesproject($this->_agreements[$task->agreement]->salesproject);
            $owner = new midcom_db_person($salesproject->owner);
            if ($owner->id != $manager->id)
            {
                org_openpsa_notifications::notify('org.openpsa.projects:hour_reports_approved', $owner->guid, $message);
            }
        }
    }
        
    /**
     * Index page handler (list).
     * 
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param mixed &$data The local request data. 
     * @return boolean Indicating success.
     */
    function _handler_index($handler_id, $args, &$data)
    {
        $this->_list_agreements();
    
        if (array_key_exists('net_nemein_hourview2_submit', $_POST))
        {
            // Process approvals
            $this->_process_approvals();
        }
        
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: " . $this->_l10n->get('net.nemein.hourview2')); 
        
        return true;
    }
    
    /**
     * Display a list of un-approved hours.
     * 
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data. 
     */
    function _show_index($handler_id, &$data)
    {
        $data['node'] = $this->_topic;
        
        if ($handler_id == 'list_all')
        {  
            $hours_by_task = $this->_list_hours(false);
        }
        else
        {
            $hours_by_task = $this->_list_hours();
        }
        
        if (count($hours_by_task) > 0)
        {
            midcom_show_style('show-index-header');
            foreach ($hours_by_task as $task_id => $hours)
            {
                $data['process'] = $this->_tasks[$task_id];
                midcom_show_style('show-process-header');
                $data['view_even'] = false;
                
                foreach ($hours as $hour_report)
                {
                    global $view;                    
                    $data['hour_report'] = $hour_report;
                    midcom_show_style('show-process-hour-report');
                    
                    if (!$data['view_even'])
                    {
                        $data['view_even'] = true;
                    } 
                    else
                    {
                        $data['view_even'] = false;
                    }
                }
                midcom_show_style('show-process-footer');
            }
            midcom_show_style('show-index-footer');    
        }
        else
        {
            midcom_show_style('show-index-empty');
        }
    }
}
?>