<?php

/**
 * @package net.nemein.hourview2
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Net.nemein.hourview2 site interface class.
 * 
 * Display a list of un-approved hours limited to a certain company.
 * Also provide a form to approve those hours according to selections
 * made with checkboxes.
 * 
 * ...
 * 
 * @package net.nemein.hourview2
 */
class net_nemein_hourview2_viewer extends midcom_baseclasses_components_request
{
    /**
     * Array for user's companies
     * 
     * @var midgard_group
     */
     var $_companies = Array();
     
    /**
     * Array for cached tasks
     * 
     * @var org_openpsa_task
     */
     var $_tasks = Array();
     
    /**
     * Array for UI messages
     */
     var $_messages = Array();

    /**
     * Constructor.
     * 
     * Nothing fancy, defines the request switch.
     */
    function net_nemein_hourview2_viewer($topic, $config)
    {
        parent::midcom_baseclasses_components_request($topic, $config);
        
        if ($_MIDGARD['user'])
        {
            // List companies the user belongs to
            $qb_member = new MidgardQueryBuilder('midgard_member');
            $qb_member->add_constraint('uid', '=', $_MIDGARD['user']);
            $connections = $qb_member->execute();
            foreach ($connections as $connection)
            {
                $this->_companies[] = $connection->gid;
            }
        }
        
        // Always run in uncached mode
        $GLOBALS["midcom"]->cache->content->no_cache();
        
        $this->_request_switch[] = Array 
        ( 
            /* These two are the default values anyway, so we can skip them. */
            // 'fixed_arguments' => null,
            // 'variable_arguments' => 0,
            'handler' => 'index'
        );
        
        $GLOBALS["midcom"]->add_link_head(
            array
            (
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL."/net.nemein.hourview2/hourview2.css",
            )
        );        
    }
    
    function _list_hours($hide_approved = true)
    {
        $hours_by_task = array();
        
        // By default list hour reports from last two months
        $start_timestamp = mktime(0,0,0,date('n')-2,1,date('Y'));
        
        if (array_key_exists("net_nemein_hourview2_view_all",$_REQUEST))
        {
            // User has requested all hour reports
            $start_timestamp = 0;
        }
        
        // List hour reports until yesterday
        $end_timestamp = mktime(0,0,0,date('n'),date('j'),date('Y'));
        
        if (count($this->_companies) == 0)
        {
            return Array();
        }
        
        // List tasks user is customer in
        $task_qb = new MidgardQueryBuilder('org_openpsa_task');
        $task_qb->begin_group('OR');
        foreach ($this->_companies as $company_id)
        {
            $task_qb->add_constraint('customer', '=', $company_id);
        }
        $task_qb->end_group();
        $task_qb->add_constraint('status', '>=', 6560); // ORG_OPENPSA_TASKSTATUS_COMPLETED
        $tasks = $task_qb->execute();
        foreach ($tasks as $task)
        {
            $this->_tasks[$task->id] = $task;
        }
        
        // List hour reports
        $qb = new MidgardQueryBuilder('org_openpsa_hour_report');

        if ($hide_approved)
        {
            $qb->add_constraint('approved', '=', '0000-00-00 00:00:00');
        }

        $qb->add_constraint('invoiceable', '=', 1);
        $qb->begin_group('OR');
        foreach ($this->_tasks as $task)
        {
            $qb->add_constraint('task', '=', $task->id);
        }
        $qb->end_group();
        $hours = $qb->execute();

        foreach ($hours as $hour_report)
        {
            $hours_by_task[$hour_report->task][$hour_report->id] = $hour_report;
        }
        
        return $hours_by_task;
    }
        
    /**
     * Index page handler (list).
     * 
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param mixed $data The local request data. 
     * @return bool Indicating success.
     */
    function _handler_index($handler_id, $args, &$data)
    {
        if (isset($_POST['net_nemein_hourview2_submit']))
        {
            // Handle approvals
            error_reporting(E_ALL ^ E_NOTICE);
            ignore_user_abort(true);
            ini_set('max_execution_time', 0);
            
            //Get SG config for sudo
            mgd_include_snippet_php('/NemeinNet_Core/get_sg_config');
            mgd_include_snippet_php('/NemeinNet_Core/Mail');
            
            // Get user
            $user = mgd_get_person($_MIDGARD["user"]);
            
            $hours_by_task = $this->_list_hours();
            foreach ($hours_by_task as $task_id => $hours)
            {
                $hour_reports_approved = array();
                $hour_reports_failedapproval = array();
                $hour_reports_notapproved = array();        
            
                // Get the process
                $task = $this->_tasks[$task_id];
                
                // TODO: we need to sudo here
                /*
                $sudo = mgd_auth_midgard($GLOBALS["system_user"], $GLOBALS["system_pass"], 0);
                if (!$sudo)
                {
                    $this->_messages[] = "Failed to sudo to ".$GLOBALS["system_user"].", reason ".mgd_errstr();
                }
                $GLOBALS['midgard']=mgd_get_midgard();
                */
                
                // Run through the hours
                foreach ($hours as $hour_report)
                {
                    if (   array_key_exists('net_nemein_hourview2_approve', $_POST)
                        && array_key_exists($hour_report->id, $_POST['net_nemein_hourview2_approve'])
                        && $_POST['net_nemein_hourview2_approve'][$hour_report->id] == 1)
                    {
                        $hour_report->approved = date('Y-m-d H:i:s');
                        $hour_report->approver = $_MIDGARD['user'];
                        if ($hour_report->update())
                        {
                            //Approved succesfully
                            $hour_reports_approved[] = $hour_report;
                        } 
                        else
                        {
                            //Error handling
                            $hour_reports_failedapproval[] = $hour_report;
                            $this->_messages[] = "Failed to approve hour report ".$hour_report->id.", reason ".mgd_errstr();
                        }
                    }
                    else
                    {
                        // User did not approve this hour report
                        $hour_reports_notapproved[] = $hour_report;
                    }
                }
                
                //TODO: We need unsetuid here
                
                // Get the manager, he will receive some spam
                $manager = new midgard_person();
                $manager->get_by_id($task->manager);
            
                $message = "{$user->name} has processed the following hour reports in process ".$task->title."\n\n";
            
                if (count($hour_reports_approved) > 0)
                {
                    $this->_messages[] = "Approved ".count($hour_reports_approved)." hour reports";
                    $message .= "Approved hours:\n";
                    foreach ($hour_reports_approved as $hour_report)
                    {
                        $reporter = new midgard_person();
                        $reporter->get_by_id($hour_report->person);
                        $message .= strftime("%x",$hour_report->date).": {$hour_report->hours}h, {$reporter->lastname} {$reporter->firstname}\n";
                    }
                }
            
                if (count($hour_reports_notapproved) > 0)
                {
                    $message .= "\nNot approved hours:\n";
                    foreach ($hour_reports_notapproved as $hour_report)
                    {
                        $reporter = new midgard_person();
                        $reporter->get_by_id($hour_report->person);
                        $message .= strftime("%x",$hour_report->date).": {$hour_report->hours}h, {$reporter->lastname} {$reporter->firstname}\n";
                    }
                }
                
                if (count($hour_reports_failedapproval) > 0)
                {
                    $message .= "\nApproval failed for hours:\n";
                    foreach ($hour_reports_failedapproval as $hour_report)
                    {
                        $reporter = new midgard_person();
                        $reporter->get_by_id($hour_report->person);
                        $message .= strftime("%x",$hour_report->date).": {$hour_report->hours}h, {$reporter->lastname} {$reporter->firstname}\n";
                    }
                }
                            
                $message .= "\nComments:\n".$_POST["net_nemein_hourview2_comments"];
                
                if ($manager->email)
                {
                    // TODO: Use MidCOM mailtemplate
                    //mail($manager->name. "<".$manager->email.">","Processed hour reports in ".$task->title,$message,"From: ".$user->name. "<".$user->email.">\nContent-Type: text/plain;charset=".$this->_i18n->get_current_charset());
                    $mail=new nemeinnet_mail();
                    $mail->to = '"'.$manager->firstname.' '.$manager->lastname.'" <'.$manager->email.'>';
                    $mail->subject = "Processed hour reports in ".$task->title;
                    if ($user->email)
                    {
                        $mail->from = '"'.$user->name.'" <'.$user->email.'>';
                    }
                    else
                    {
                        $mail->from = '"'.$user->name.'" <noreply@openpsa.org>';
                    }
                    $mail->body = $message;
                    error_reporting(E_ALL ^ E_NOTICE);
                    $mail->send();
                    $this->_messages[] = "Mailed note to {$manager->firstname} {$manager->lastname} <{$manager->email}>";
                }
                else
                {
                    // TODO: Send to a default address?
                }

            }
            error_reporting(E_ALL);
        }
        return true;
    }
    
    /**
     * Display a list of un-approved hours.
     * 
     * @param mixed $handler_id The ID of the handler.
     * @param mixed $data The local request data. 
     */
    function _show_index($handler_id, &$data)
    {
        global $view_topic;
        $view_topic = $this->_topic;
        global $view_messages;
        $view_messages = $this->_messages;
        global $view_l10n;
        $view_l10n = $this->_l10n;
        global $view_l10n_midcom;
        $view_l10n_midcom = $this->_l10n_midcom;
        
        $hours_by_task = $this->_list_hours();
        
        if (count($hours_by_task) > 0)
        {
            midcom_show_style('show-index-header');
            foreach ($hours_by_task as $task_id => $hours)
            {
                global $view_process;
                $view_process = $this->_tasks[$task_id];
                midcom_show_style('show-process-header');
                $GLOBALS["view_even"] = false;
                
                foreach ($hours as $hour_report)
                {
                    global $view;                    
                    $view = $hour_report;
                    midcom_show_style('show-process-hour-report');
                    
                    if (!$GLOBALS["view_even"])
                    {
                        $GLOBALS["view_even"] = true;
                    } 
                    else
                    {
                        $GLOBALS["view_even"] = false;
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
