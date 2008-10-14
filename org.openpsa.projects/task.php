<?php
/**
 * @package org.openpsa.projects
 * @author Nemein Oy http://www.nemein.com/
 * @copyright Nemein Oy http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * MidCOM wrapped access to the MgdSchema class, keep logic here
 *
 * @package org.openpsa.projects
 */
class org_openpsa_projects_task_dba extends __org_openpsa_projects_task_dba
{
    var $contacts = null; //Shorthand access for contact members
    var $resources = null; // --''--
    var $_locale_backup = '';
    var $_skip_acl_refresh = false;
    var $_skip_parent_refresh = false;
    var $status_comment = ''; //Shorthand access for the comment of last status
    var $status_time = false; //Shorthand access for the timestamp of last status
    var $status_type = '';    //Shorthand access to status type in simple format, eg. "ongoing"
    var $resource_seek_type = 'none';

    function __construct($id = null)
    {
        return parent::__construct($id);
    }

    /**
     * Deny midgard:read by default
     */
    function get_class_magic_default_privileges()
    {
        $privileges = parent::get_class_magic_default_privileges();
        $privileges['EVERYONE']['midgard:read'] = MIDCOM_PRIVILEGE_DENY;
        return $privileges;
    }

    function _on_creating()
    {
        $this->_locale_set();
        $this->orgOpenpsaObtype = ORG_OPENPSA_OBTYPE_TASK;
        $this->manager = $_MIDGARD['user'];
        return $this->_prepare_save();
    }

    function _on_created()
    {
        $this->_locale_restore();
        $this->_workflow_checks('created');
    }

    function _on_loaded()
    {
        /* This in theory can cause confusion if the actual DB status (cache) field is different.
          but it's better to get the correct value late than never, next update will make sure it's correct in DB as well */
        $this->status = $this->_get_status(true);

        if ($this->title == "")
        {
            $this->title = "Task #{$this->id}";
        }
        // Load seek type
        $this->resource_seek_type = $this->get_parameter('org.openpsa.projects', 'resource_seek_type');

        return true;
    }

    function _on_updating()
    {
        $this->_locale_set();
        if ($this->_prepare_save())
        {
            $this->_handle_resource_seek();
            return true;
        }
        //If we return false here then _on_updated() never gets called
        $this->_locale_restore();
        return false;
    }

    private function _handle_resource_seek()
    {
        // act on seek type
        switch($this->resource_seek_type)
        {
            case 'dbe':
                // Start DBE search if it's not still in progress
                $dbe_state = $this->get_parameter('org.openpsa.projects.projectbroker', 'remote_search');
                if ($dbe_state != 'SEARCH_IN_PROGRESS')
                {
                    $this->set_parameter('org.openpsa.projects.projectbroker', 'remote_search', 'REQUEST_SEARCH');
                    // TODO: Ping the DBE service to start search immediately in stead of waiting for interval check
                }
                // TODO: better way to prevent this being done on each update but also have reseek possible ??
                $this->resource_seek_type = 'none';
                // Fall-trough intentional
            case 'openpsa':
                $local_state = $this->get_parameter('org.openpsa.projects.projectbroker', 'local_search');
                if ($local_state != 'SEARCH_IN_PROGRESS')
                {
                    // Register AT service background seek.
                    $args = array
                    (
                        'task' => $this->guid,
                        'membership_filter' => array(),
                    );
                    $this->set_parameter('org.openpsa.projects.projectbroker', 'local_search', 'REQUEST_SEARCH');
                    $atstat = midcom_services_at_interface::register(time(), 'org.openpsa.projects', 'background_search_resources', $args);
                    if (!$atstat)
                    {
                        // error handling ?
                    }
                }
                // TODO: better way to prevent this being done on each update but also have reseek possible ??
                $this->resource_seek_type = 'none';
                break;
            case 'organization':
                $local_state = $this->get_parameter('org.openpsa.projects.projectbroker', 'local_search');
                if ($local_state != 'SEARCH_IN_PROGRESS')
                {
                    // Background local seek with group limiter set to owner_group
                    $args = array
                    (
                        'task' => $this->guid,
                        'membership_filter' => array('owner_group'),
                    );
                    $this->set_parameter('org.openpsa.projects.projectbroker', 'local_search', 'REQUEST_SEARCH');
                    $atstat = midcom_services_at_interface::register(time(), 'org.openpsa.projects', 'background_seach_resources', $args);
                    if (!$atstat)
                    {
                        // error handling ?
                    }
                }
                // TODO: better way to prevent this being done on each update but also have reseek possible ??
                $this->resource_seek_type = 'none';
                break;
            case 'none':
                // TODO: unset other search requests if any are pending ??
                // Fall-trough intentional
            default:
                break;
        }
        // Store seek type
        $this->set_parameter('org.openpsa.projects', 'resource_seek_type', $this->resource_seek_type);
    }

    function _on_updated()
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        // Sync the object's ACL properties into MidCOM ACL system
        if (   !$this->_skip_acl_refresh
            && $this->orgOpenpsaAccesstype
            && $this->orgOpenpsaOwnerWg)
        {
            debug_add("Synchronizing task ACLs to MidCOM");
            $sync = new org_openpsa_core_acl_synchronizer();
            $sync->write_acls($this, $this->orgOpenpsaOwnerWg, $this->orgOpenpsaAccesstype);

            // Synchronize also the news topic
            if ($this->newsTopic)
            {
                $news_topic = new midcom_baseclasses_database_topic($this->newsTopic);
                $sync->write_acls($news_topic, $this->orgOpenpsaOwnerWg, $this->orgOpenpsaAccesstype);
            }
            if ($this->forumTopic)
            {
                $forum_topic = new midcom_baseclasses_database_topic($this->forumTopic);
                $sync->write_acls($forum_topic, $this->orgOpenpsaOwnerWg, $this->orgOpenpsaAccesstype);
            }
        }

        // Ensure resources can read regardless of if this is a vgroup
        debug_add("Ensuring resources can read the object");

        if (!is_array($this->resources))
        {
            $this->get_members();
        }

        foreach ($this->resources as $pid => $bool)
        {
            $oldPerson = $this->_pid_to_obj($pid);

            debug_add("Setting 'midgard:read' for {$pid}");
            $this->set_privilege('midgard:read', $oldPerson->id, MIDCOM_PRIVILEGE_ALLOW);

            if ($this->orgOpenpsaObtype == ORG_OPENPSA_OBTYPE_TASK)
            {
                // Resources must be permitted to create hour/expense reports into tasks
                $this->set_privilege('midgard:create', $oldPerson->id, MIDCOM_PRIVILEGE_ALLOW);
                //For declines etc they also need update...
                $this->set_privilege('midgard:update', $oldPerson->id, MIDCOM_PRIVILEGE_ALLOW);
            }
        }
        //Ensure manager can do stuff regardless of vgroup
        if ($this->manager)
        {
            $manager_person = $this->_pid_to_obj($this->manager);
            $this->set_privilege('midgard:read', $manager_person->id, MIDCOM_PRIVILEGE_ALLOW);
            $this->set_privilege('midgard:create', $manager_person->id, MIDCOM_PRIVILEGE_ALLOW);
            $this->set_privilege('midgard:delete', $manager_person->id, MIDCOM_PRIVILEGE_ALLOW);
            $this->set_privilege('midgard:update', $manager_person->id, MIDCOM_PRIVILEGE_ALLOW);
        }
        debug_pop();
        $this->_workflow_checks('updated');
        $this->_update_parent();

        $this->_locale_restore();
    }

    function _on_deleting()
    {
        $this->update_cache(false);
        if ($this->hourCache > 0)
        {
            $_MIDCOM->uimessages->add($_MIDCOM->i18n->get_string('org.openpsa.projects', 'org.openpsa.projects'), $_MIDCOM->i18n->get_string('task deletion now allowed because of hour reports', 'org.openpsa.projects'), 'warn');
            return false;
        }

        return parent::_on_deleting();
    }

    /**
     * Generate a user-readable label for the task using the task/project hierarchy
     */
    function get_label()
    {
        $label = '';
        $label_elements = array($this->title);
        $task = $this;
        while (   !is_null($task)
               && $task = $task->get_parent())
        {
            if (   $task
                && $task->guid
                && isset($task->title))
            {
                $label_elements[] = $task->title;
            }
        }

        $label_elements = array_reverse($label_elements);
        foreach ($label_elements as $element)
        {
            $label .= "/ {$element} ";
        }

        return trim($label);
    }

    /**
     * Populates contacts as resources lists
     */
    function get_members()
    {
        if (!$this->id)
        {
            return false;
        }

	if (!is_array($this->contacts))
	{
	    $this->contacts = array();
	}
	if (!is_array($this->resources))
	{
	    $this->resources = array();
	}

        $mc = org_openpsa_projects_task_resource_dba::new_collector('task', $this->id);
        $mc->add_value_property('orgOpenpsaObtype');
        $mc->add_value_property('person');
        $mc->add_constraint('orgOpenpsaObtype', '<>', ORG_OPENPSA_OBTYPE_PROJECTPROSPECT);
        $mc->execute();
        $ret = $mc->list_keys();

        if (   is_array($ret)
            && count($ret) > 0)
        {
            foreach ($ret as $guid => $empty)
            {
                switch ($mc->get_subkey($guid, 'orgOpenpsaObtype'))
                {
                    case ORG_OPENPSA_OBTYPE_PROJECTCONTACT:
                        $varName = 'contacts';
                        break;
                    default:
                        //fall-trough intentional
                    case ORG_OPENPSA_OBTYPE_PROJECTRESOURCE:
                        $varName = 'resources';
                        break;
                }
                $this->{$varName}[$mc->get_subkey($guid, 'person')] = true;
            }
        }
        return true;
    }


    function _prepare_save()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        //Make sure we have start
        if (!$this->start)
        {
            $this->start = time();
        }
        //Make sure we have end
        if (!$this->end)
        {
            $this->end = time();
        }

        //Reset start and end to start/end of day
        $this->start = mktime(  0,
                                0,
                                0,
                                date('n', $this->start),
                                date('j', $this->start),
                                date('Y', $this->start));
        $this->end = mktime(23,
                            59,
                            59,
                            date('n', $this->end),
                            date('j', $this->end),
                            date('Y', $this->end));

        if ($this->start > $this->end)
        {
            debug_add("start ({$this->start}) is greater than end ({$this->end}), aborting", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }

        $this->orgOpenpsaWgtype = ORG_OPENPSA_WGTYPE_NONE;

        if ($this->agreement)
        {
            // Get customer company into cache from agreement's sales project
            $agreement = new org_openpsa_sales_salesproject_deliverable_dba($this->agreement);
            if ($agreement)
            {
                $salesproject = new org_openpsa_sales_salesproject_dba($agreement->salesproject);
                $this->customer = $salesproject->customer;
            }
            $this->hoursInvoiceableDefault = true;
        }
        else
        {
            // No agreement, we can't be invoiceable
            $this->hoursInvoiceableDefault = false;
        }

        // Update hour caches in this and agreement
        $this->update_cache(false);

        debug_pop();
        return true;
    }

    private function _locale_set()
    {
        $this->_locale_backup = setlocale(LC_NUMERIC, '0');
        setlocale(LC_NUMERIC, 'C');
    }

    private function _locale_restore()
    {
        setlocale(LC_NUMERIC, $this->_locale_backup);
    }

    /**
     * Update hour report caches
     */
    function update_cache($update = true)
    {
        if (!$this->id)
        {
            return false;
        }

        $hours = $this->list_hours();
        $stat = true;

        $this->hourCache = $hours['reported'];
        $this->agreement = (int) $this->agreement;

        if ($this->agreement)
        {
            // Copy this task's hours as base
            $agreement_hours = $hours;

            // List hours from other tasks of the same agreement too
            $qb = org_openpsa_projects_task_dba::new_query_builder();
            $qb->add_constraint('agreement', '=', $this->agreement);
            $qb->add_constraint('id', '<>', $this->id);
            $other_tasks = $qb->execute_unchecked();
            foreach ($other_tasks as $task)
            {
                $task_hours = $task->list_hours();
                foreach ($task_hours as $type => $hours)
                {
                    // Add the hours of the task to agreement's totals
                    $agreement_hours[$type] += $hours;
                }
            }

            // Update units on the agreement with invoiceable hours
            // list_hours does the needed checks on hour types
            $agreement = new org_openpsa_sales_salesproject_deliverable_dba($this->agreement);
            if ($agreement)
            {
                $agreement->units = $agreement_hours['invoiceable'];
                $agreement->uninvoiceableUnits = $agreement_hours['reported'] - ($agreement_hours['invoiceable'] + $agreement_hours['invoiced']);
                $stat = $agreement->update();
            }
        }

        if ($update)
        {
            $stat = $this->update();
        }
        return $stat;
    }

    function list_hours()
    {
        $hours = Array
        (
            'reported'    => 0,
            'approved'    => 0,
            'invoiced'    => 0,
            'invoiceable' => 0,
        );

        // Check agreement for invoceability rules
        $invoice_approved = false;
        $invoice_enable = false;
        if ($this->agreement)
        {
            $agreement = new org_openpsa_sales_salesproject_deliverable_dba($this->agreement);
            if ($agreement)
            {
                $invoice_enable = true;
                if ($agreement->invoiceApprovedOnly)
                {
                    $invoice_approved = true;
                }
            }
        }

        $report_qb = org_openpsa_projects_hour_report_dba::new_query_builder();
        $report_qb->add_constraint('task', '=', $this->id);
        $reports = $report_qb->execute();
        foreach ($reports as $report)
        {
            $hours['reported'] += $report->hours;

            if ($report->is_approved)
            {
                $hours['approved'] += $report->hours;
            }

            if (   $report->invoiced != '0000-00-00 00:00:00'
                && $report->invoiced != '0000-00-00 00:00:00+0000'
                && $report->invoiced)
            {
                $hours['invoiced'] += $report->hours;
            }
            elseif ($report->invoiceable)
            {
                // Check agreement for invoceability rules
                if ($invoice_enable)
                {
                    if ($invoice_approved)
                    {
                        // Count only uninvoiced approved hours as invoiceable
                        if ($report->is_approved)
                        {
                            $hours['invoiceable'] += $report->hours;
                        }
                    }
                    else
                    {
                        // Count all uninvoiced invoiceable hours as invoiceable regardless of approval status
                        $hours['invoiceable'] += $report->hours;
                    }
                }
            }
        }

        return $hours;
    }

    function _update_parent()
    {
        if ($this->_skip_parent_refresh)
        {
            return true;
        }
        $project = $this->get_parent();
        if (isset($project->orgOpenpsaObtype)
            && $project->orgOpenpsaObtype == ORG_OPENPSA_OBTYPE_PROJECT)
        {
            //Make sure the parent is initialized in correct class
            if (!is_a($project, 'org_openpsa_projects_project'))
            {
                $project = new org_openpsa_projects_project($project->id);
            }
            $project->_refresh_from_tasks();
        }
        return true;
    }

    function _pid_to_obj($pid)
    {
        return $_MIDCOM->auth->get_user($pid);
    }

    /**
     * Shortcut for creating status object
     */
    function _create_status($status_type, $target_person = 0, $comment = '')
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $status = new org_openpsa_projects_task_status_dba();
        if ($target_person != 0)
        {
            $status->targetPerson = $target_person;
        }
        $status->task = $this->id;
        $status->type = $status_type;
        //This shouldn't be needed
        $status->timestamp = org_openpsa_projects_task_status_dba::gmtime();
        $status->comment = $comment;
        debug_add("about to create status\n===\n" . org_openpsa_helpers::sprint_r($status) . "===\n");

        $ret = $status->create();

        debug_add("got ret \"{$ret}\", errstr: " . mgd_errstr());
        debug_pop();
        return $ret;
   }

    /**
     * Queries status objects and sets correct value to $this->status
     */
    function _get_status($set_comment = false, $set_time = true)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        //Simplistic approach
        $qb = org_openpsa_projects_task_status_dba::new_query_builder();
        $qb->add_constraint('task', '=', $this->id);
        if ($this->status > ORG_OPENPSA_TASKSTATUS_PROPOSED)
        {
            //Only get proposed status objects here if are not over that phase
            $qb->add_constraint('type', '<>', ORG_OPENPSA_TASKSTATUS_PROPOSED);
        }
        if (count($this->resources) > 0)
        {
            //Do not ever set status to declined if we still have resources left
            $qb->add_constraint('type', '<>', ORG_OPENPSA_TASKSTATUS_DECLINED);
        }
        $qb->add_order('timestamp', 'DESC');
        $qb->add_order('type', 'DESC'); //Our timestamps are not accurate enough so if we have multiple with same timestamp suppose highest type is latest
        $qb->set_limit(1);

        $main_ret = $qb->execute();

        if (   !is_array($main_ret)
            || count($main_ret) == 0)
        {
            //Failure to get status object
            if (!$this->status)
            {
                //Default to proposed if nothing else is available
                debug_add('Could find any status objects and previous status was not saved, defaulting to proposed');
                debug_pop();
                return ORG_OPENPSA_TASKSTATUS_PROPOSED;
            }
            //Default to last status if available
            debug_add('Could not find any status objects, defaulting to previous status');
            debug_pop();
            return $this->status;
        }

        //TODO: Check various combinations of accept/decline etc etc

        if ($set_comment)
        {
            $this->status_comment = $main_ret[0]->comment;
        }
        if ($set_time)
        {
            $this->status_time = $main_ret[0]->timestamp;
        }


        switch ($main_ret[0]->type)
        {
            case ORG_OPENPSA_TASKSTATUS_REJECTED:
                $this->status_type = 'rejected';
                break;
            case ORG_OPENPSA_TASKSTATUS_PROPOSED:
            case ORG_OPENPSA_TASKSTATUS_DECLINED:
            case ORG_OPENPSA_TASKSTATUS_ACCEPTED:
                $this->status_type = 'not_started';
                break;
            case ORG_OPENPSA_TASKSTATUS_STARTED:
            case ORG_OPENPSA_TASKSTATUS_REOPENED:
                $this->status_type = 'ongoing';
                break;
            case ORG_OPENPSA_TASKSTATUS_COMPLETED:
            case ORG_OPENPSA_TASKSTATUS_APPROVED:
            case ORG_OPENPSA_TASKSTATUS_CLOSED:
                $this->status_type = 'closed';
                break;
            case ORG_OPENPSA_TASKSTATUS_REJECTED:
            case ORG_OPENPSA_TASKSTATUS_ONHOLD:
            default:
                $this->status_type = 'on_hold';
                break;
        }

        debug_pop();
        return $main_ret[0]->type;
    }

    private function _propose_to_resources()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $this->get_members();
        $propose_to = $this->resources;

        //Remove those who already have a proposal from the list to propose to
        $qb = org_openpsa_projects_task_status_dba::new_query_builder();
        
        $qb->add_constraint('task', '=', $this->id);
        $qb->add_constraint('type', '=', ORG_OPENPSA_TASKSTATUS_PROPOSED);
        $qb->add_constraint('targetPerson', 'IN', array_keys($propose_to));

        $proposals_ret = $qb->execute();

        if (   is_array($proposals_ret)
            && count($proposals_ret)>0)
        {
            foreach ($proposals_ret as $proposal)
            {
                if (isset($propose_to[$proposal->targetPerson]))
                {
                    unset($propose_to[$proposal->targetPerson]);
                }
            }
        }

        //Go trough the remaining resources and set proposal
        foreach ($propose_to as $pid => $bool)
        {
            //PONDER: Check for previous status to avoid proposing to those who already declined ? (declined persons are removed from resources list)
            debug_add("saving proposed status for person {$pid}");
            $this->_create_status(ORG_OPENPSA_TASKSTATUS_PROPOSED, $pid);
            //If creator is in resources he would naturally accept his own proposal...
            if ($pid == $this->creator)
            {
                $this->accept();
            }
        }
        $stat = $this->_get_status();
        if ($stat != $this->status)
        {
            debug_add("doublechecked status {$stat} does not match current status {$this->status}, updating");
            //PONDER: should this be somehow set directly trough mgdschema ??
            $this->status = $stat;
            debug_pop();
            $this->_skip_acl_refresh = true;
            return $this->update();
        }
        debug_pop();
        return true;
    }

    /**
     * Accept the proposal
     */
    function accept($comment = '')
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("task->accept() called with user #{$_MIDGARD['user']}");
        $this->_create_status(ORG_OPENPSA_TASKSTATUS_ACCEPTED, 0, $comment);
        switch ($this->acceptanceType)
        {
            case ORG_OPENPSA_TASKACCEPTANCE_ALLACCEPT:
                debug_add('Acceptance mode not implemented', MIDCOM_LOG_ERROR);
                debug_pop();
                return false;
                break;
            case ORG_OPENPSA_TASKACCEPTANCE_ONEACCEPTDROP:
                debug_add('Acceptance mode not implemented', MIDCOM_LOG_ERROR);
                debug_pop();
                return false;
                break;
            default:
            case ORG_OPENPSA_TASKACCEPTANCE_ONEACCEPT:
                //PONDER: should this be somehow set directly trough mgdschema ??
                //PONDER: Should this be superseded by generic method for querying the status objects to set the latest status ??
                debug_add("Required accept received, setting task status to accepted");
                $this->status = ORG_OPENPSA_TASKSTATUS_ACCEPTED;
                debug_pop();
                $this->_skip_acl_refresh = true;
                return $this->update();
                break;
        }
        //We should not fall trough this far
        debug_pop();
        return false;
    }

    /**
     * Decline the proposal
     */
    function decline($comment = '')
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("task->decline() called with user  #{$_MIDGARD['user']}");
        $ret = $this->_create_status(ORG_OPENPSA_TASKSTATUS_DECLINED, 0, $comment);
        if (!$ret)
        {
            debug_add('failed to create status object, errstr: ' . mgd_errstr());
            debug_pop();
            return false;
        }
        $resource_removed = false;
        debug_add("task->resources: \n===\n" . org_openpsa_helpers::sprint_r($this->resources) . "===\n");
        if (isset($this->resources[$_MIDGARD['user']]))
        {
            debug_add("removing user #{$_MIDGARD['user']} from resources");
            unset($this->resources[$_MIDGARD['user']]);
            $resource_removed = true;
        }
        $stat = $this->_get_status(true);
        if (   $stat != $this->status
            || $resource_removed)
        {
            debug_add("doublechecked status {$stat} does not match current status {$this->status} OR we have removed resource(s), updating");
            //PONDER: should this be somehow set directly trough mgdschema ??
            $this->status = $stat;
            debug_pop();
            $this->_skip_acl_refresh = true;
            return $this->update();
        }
        debug_pop();
        return true;
    }

    /**
     * Mark task as started (in case it's not already done)
     */
    function start($started_by = 0)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("task->start() called with user #{$_MIDGARD['user']}");
        //PONDER: Check actual status objects for more accurate logic ?
        if (   $this->status >= ORG_OPENPSA_TASKSTATUS_STARTED
            && $this->status <= ORG_OPENPSA_TASKSTATUS_APPROVED)
        {
            //We already have started status
            debug_add('Task has already been started');
            debug_pop();
            return true;
        }
        $ret = $this->_create_status(ORG_OPENPSA_TASKSTATUS_STARTED, $started_by);
        if (!$ret)
        {
            debug_add('failed to create status object, errstr: ' . mgd_errstr());
            debug_pop();
            return false;
        }
        //PONDER: should this be somehow set directly trough mgdschema ??
        //PONDER: Should this be superseded by generic method for querying the status objects to set the latest status ??
        //PONDER: If we add to closed task shouldn't we reopen or something....
        $this->status = ORG_OPENPSA_TASKSTATUS_STARTED;
        debug_pop();
        $this->_skip_acl_refresh = true;
        return $this->update();
    }

    /**
     * Mark task as completed
     */
    function complete($comment = '')
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("task->complete() called with user #{$_MIDGARD['user']}");
        //TODO: Check deliverables
        $ret = $this->_create_status(ORG_OPENPSA_TASKSTATUS_COMPLETED, 0, $comment);
        if (!$ret)
        {
            debug_add('failed to create status object, errstr: ' . mgd_errstr());
            debug_pop();
            return false;
        }
        //PONDER: Check ACL in stead ?
        if ($_MIDGARD['user'] == $this->manager)
        {
            //Manager marking task completed also approves it at the same time
            debug_add('We\'re the manager of this task, approving straight away');
            $this->_skip_parent_refresh = true;
            $ret = $this->approve();
            $this->_skip_parent_refresh = false;
            debug_add("approve returned '{$ret}', errstr: " . mgd_errstr());
            debug_pop();
            return $ret;
        }
        $stat = $this->_get_status(true);
        if ($stat != $this->status)
        {
            debug_add("doublechecked status {$stat} does not match current status {$this->status}, updating");
            //PONDER: should this be somehow set directly trough mgdschema ??
            $this->status = $stat;
            debug_pop();
            $this->_skip_acl_refresh = true;
            return $this->update();
        }
        debug_pop();
        return $ret;
    }

    /**
     * Drops a completed task to started status
     */
    function remove_complete($comment = '')
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("task->remove_complete() called with user #{$_MIDGARD['user']}");
        if ($this->status != ORG_OPENPSA_TASKSTATUS_COMPLETED)
        {
            //Status is not completed, we can't remove that status.
            debug_add('status != completed, aborting');
            debug_pop();
            return false;
        }
        debug_pop();
        return $this->_drop_to_started($comment);
    }

    /**
     * Drops tasks status to started
     */
    private function _drop_to_started($comment = '')
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        if ($this->status <= ORG_OPENPSA_TASKSTATUS_STARTED)
        {
            //Task has not even been started, abort
            debug_add('Task has not been started, aborting');
            debug_pop();
            return false;
        }
        $ret = $this->_create_status(ORG_OPENPSA_TASKSTATUS_STARTED, 0, $comment);
        if (!$ret)
        {
            debug_add('failed to create status object, errstr: ' . mgd_errstr());
            debug_pop();
            return false;
        }
        $stat = $this->_get_status(true);
        if ($stat != $this->status)
        {
            debug_add("doublechecked status {$stat} does not match current status {$this->status}, updating");
            //PONDER: should this be somehow set directly trough mgdschema ??
            $this->status = $stat;
            debug_pop();
            $this->_skip_acl_refresh = true;
            return $this->update();
        }
        debug_pop();
        return $ret;
    }

    /**
     * Mark task as approved
     */
    function approve($comment = '')
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("task->approve() called with user #{$_MIDGARD['user']}");
        //TODO: Check deliverables / Require to be completed first
        //PONDER: Check ACL in stead ?
        if ($_MIDGARD['user'] != $this->manager)
        {
            debug_add("Current user #{$_MIDGARD['user']} is not manager of task, thus cannot approve", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        $ret = $this->_create_status(ORG_OPENPSA_TASKSTATUS_APPROVED, 0, $comment);
        if (!$ret)
        {
            debug_add('failed to create status object, errstr: ' . mgd_errstr());
            debug_pop();
            return false;
        }
        debug_add('approved tasks get closed at the same time, calling this->close()');
        $this->_skip_parent_refresh = true;
        $ret = $this->close();
        $this->_skip_parent_refresh = false;
        debug_add("close returned '{$ret}', errstr: " . mgd_errstr());
        $stat = $this->_get_status(true);
        if ($stat != $this->status)
        {
            //PONDER: should this be somehow set directly trough mgdschema ??
            debug_add("doublechecked status {$stat} does not match current status {$this->status}, updating");
            $this->status = $stat;
            debug_pop();
            $this->_skip_acl_refresh = true;
            return $this->update();
        }
        debug_pop();
        /*
        echo "DEBUG: approve method end reached, ret: {$ret}";
        exit();
        */
        return $ret;
    }

    function reject($comment = '')
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("task->reject() called with user #{$_MIDGARD['user']}");
        //TODO: Check deliverables / Require to be completed first
        //PONDER: Check ACL in stead ?
        if ($_MIDGARD['user'] != $this->manager)
        {
            debug_add("Current user #{$_MIDGARD['user']} is not manager of task, thus cannot reject", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        $ret = $this->_create_status(ORG_OPENPSA_TASKSTATUS_REJECTED, 0, $comment);
        if (!$ret)
        {
            debug_add('failed to create status object, errstr: ' . mgd_errstr());
            debug_pop();
            return false;
        }
        $stat = $this->_get_status(true);
        if ($stat != $this->status)
        {
            debug_add("doublechecked status {$stat} does not match current status {$this->status}, updating");
            //PONDER: should this be somehow set directly trough mgdschema ??
            $this->status = $stat;
            debug_pop();
            $this->_skip_acl_refresh = true;
            return $this->update();
        }
        debug_pop();
        return $ret;
    }

    /**
     * Drops an approved task to started status
     */
    function remove_approve($comment = '')
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("task->remove_approve() called with user #{$_MIDGARD['user']}");
        if ($this->status != ORG_OPENPSA_TASKSTATUS_APPROVED)
        {
            debug_add('Task is not approved, aborting');
            debug_pop();
            return false;
        }
        debug_pop();
        return $this->_drop_to_started($comment);
    }

    /**
     * Mark task as closed
     */
    function close($comment = '')
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("task->close() called with user #{$_MIDGARD['user']}");
        //TODO: Check deliverables / require to be approved first
        //PONDER: Check ACL in stead ?
        if ($_MIDGARD['user'] != $this->manager)
        {
            debug_add("Current user #{$_MIDGARD['user']} is not manager of task, thus cannot close", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        $ret = $this->_create_status(ORG_OPENPSA_TASKSTATUS_CLOSED, 0, $comment);
        if (!$ret)
        {
            debug_add('failed to create status object, errstr: ' . mgd_errstr());
            debug_pop();
            return false;
        }
        //PONDER: should this be somehow set directly trough mgdschema ??
        //PONDER: Should this be superseded by generic method for querying the status objects to set the latest status ??
        $this->status = ORG_OPENPSA_TASKSTATUS_CLOSED;
        debug_pop();
        $this->_skip_acl_refresh = true;
        if ($this->update())
        {
            $_MIDCOM->uimessages->add($_MIDCOM->i18n->get_string('org.openpsa.projects', 'org.openpsa.projects'), sprintf($_MIDCOM->i18n->get_string('marked task "%s" closed', 'org.openpsa.projects'), $this->title), 'ok');
            if ($this->agreement)
            {
                $agreement = new org_openpsa_sales_salesproject_deliverable_dba($this->agreement);

                // Set agreement delivered if this is the only open task for it
                $task_qb = org_openpsa_projects_task_dba::new_query_builder();
                $task_qb->add_constraint('agreement', '=', $this->agreement);
                $task_qb->add_constraint('status', '<', ORG_OPENPSA_TASKSTATUS_CLOSED);
                $task_qb->add_constraint('id', '<>', $this->id);
                $task_qb->add_constraint('orgOpenpsaObtype', '=', ORG_OPENPSA_OBTYPE_TASK);
                $tasks = $task_qb->execute();
                if (count($tasks) == 0)
                {
                    // No other open tasks, mark as delivered
                    $agreement->deliver(false);
                }
                else
                {
                    $_MIDCOM->uimessages->add($_MIDCOM->i18n->get_string('org.openpsa.projects', 'org.openpsa.projects'), sprintf($_MIDCOM->i18n->get_string('did not mark deliverable "%s" delivered due to other tasks', 'org.openpsa.sales'), $agreement->title), 'info');
                }
            }
            return true;
        }
        return false;
    }

    /**
     * Reopen a closed task
     */
    function reopen($comment = '')
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("task->reopen() called with user #{$_MIDGARD['user']}");
        if ($this->status != ORG_OPENPSA_TASKSTATUS_CLOSED)
        {
            debug_add('Task is not closed, aborting');
            debug_pop();
            return false;
        }
        $ret = $this->_create_status(ORG_OPENPSA_TASKSTATUS_REOPENED, 0, $comment);
        if (!$ret)
        {
            debug_add('failed to create status object, errstr: ' . mgd_errstr());
            debug_pop();
            return false;
        }
        $stat = $this->_get_status(true);
        if ($stat != $this->status)
        {
            debug_add("doublechecked status {$stat} does not match current status {$this->status}, updating");
            //PONDER: should this be somehow set directly trough mgdschema ??
            $this->status = $stat;
            debug_pop();
            $this->_skip_acl_refresh = true;
            return $this->update();
        }
        debug_pop();
        return $ret;
    }

    /**
     * Connect the task to an invoice
     */
    function mark_invoiced($invoice)
    {
        // Register a relation between the invoice and the task
        $relation = org_openpsa_relatedto_handler::create_relatedto($invoice, 'org.openpsa.invoices', $this, 'org.openpsa.projects');

        // Mark the hour reports invoiced
        $hours_marked = 0;
        $report_qb = org_openpsa_projects_hour_report_dba::new_query_builder();
        $report_qb->add_constraint('task', '=', $this->id);
        $report_qb->add_constraint('invoiced', '=', '0000-00-00 00:00:00');

        // Check how the agreement deals with hour reports
        $check_approvals = false;
        if ($this->agreement)
        {
            $agreement = new org_openpsa_sales_salesproject_deliverable_dba($this->agreement);
            if ($agreement)
            {
                if ($agreement->invoiceApprovedOnly)
                {
                    // The agreement allows invoicing only approved hours, therefore don't mark unapproved
                    $check_approvals = true;
                }
            }
        }

        $reports = $report_qb->execute();
        foreach ($reports as $report)
        {
            if (   $check_approvals
                && !$report->is_approved)
            {
                // We only invoice approved hours and this isn't. Skip
                continue;
            }

            $invoice_member = new org_openpsa_invoices_invoice_hour();
            $invoice_member->hourReport = $report->id;
            $invoice_member->invoice = $invoice->id;
            if ($invoice_member->create())
            {
                $hours_marked += $report->hours;
            }
        }

        // Update hour caches to agreement
        $this->update_cache(false);

        // Notify user
        $_MIDCOM->uimessages->add($_MIDCOM->i18n->get_string('org.openpsa.projects', 'org.openpsa.projects'), sprintf($_MIDCOM->i18n->get_string('marked %s hours as invoiced in task "%s"', 'org.openpsa.projects'), $hours_marked, $this->title), 'ok');
    }

    /**
     * Analyses current status and changes, then handles proposals etc
     */
    private function _workflow_checks($mode)
    {
        $main_ret = Array();
        debug_push_class(__CLASS__, __FUNCTION__);
        if ($mode == 'created')
        {
            $this->_propose_to_resources();
            debug_pop();
            return true;
        }

        //TODO: The more complex checks...

        //Always make sure we have proposals (DBE kind of follows these) in place (DM goes trough our create mode without any resources...)
        $qb = org_openpsa_projects_task_status_dba::new_query_builder();
        $qb->add_constraint('task', '=', $this->id);
        $qb->add_constraint('type', '=', ORG_OPENPSA_TASKSTATUS_PROPOSED);

        $proposals_ret = $qb->execute();

        if (   !is_array($main_ret)
            || count($main_ret)==0)
        {
            debug_add('We don\'t have any proposed status sets, creating those now');
            $this->_propose_to_resources();
        }

        debug_pop();
        return true;
    }

    /**
     * This function is called from the DM2 schema
     */
    static function get_task_resources()
    {
        $resource_array = array();
        $view_data =& $_MIDCOM->get_custom_context_data('request_data');
        if (!array_key_exists('task', $view_data))
        {
            return $resource_array;
        }

        $qb = org_openpsa_projects_task_resource_dba::new_query_builder();
        $qb->add_constraint('task', '=', $view_data['task']->id);
        $qb->add_constraint('orgOpenpsaObtype', '=', ORG_OPENPSA_OBTYPE_PROJECTRESOURCE);
        $resources = $qb->execute();
        foreach ($resources as $resource)
        {
            $person = new midcom_db_person($resource->person);
            $resource_array[$person->id] = $person->rname;
        }
        return $resource_array;
    }

}
?>