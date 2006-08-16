<?php
/**
 * Midcom wrapped access to the MgdSchema class, keep logic here
 * @package org.openpsa.projects
 */
class midcom_org_openpsa_task extends __midcom_org_openpsa_task
{
    var $contacts = array(); //Shorthand access for contact members
    var $resources = array(); // --''--
    var $old_contacts = array(); //For diffing the ones above
    var $old_resources = array(); // --''--
    var $_locale_backup = '';
    var $_skip_acl_refresh = false;
    var $_skip_parent_refresh = false;
    var $status_comment = ''; //Shorthand access for the comment of last status
    var $status_time = false; //Shorthand access for the timestamp of last status
    var $status_type = '';    //Shorthand access to status type in simple format, eg. "ongoing"

    function midcom_org_openpsa_task($id = null)
    {
        return parent::__midcom_org_openpsa_task($id);
    }

    function get_parent_guid_uncached()
    {
        // FIXME: Midgard Core should do this
        if ($this->up != 0)
        {
            $parent = new org_openpsa_projects_task($this->up);
            
            if ($parent->orgOpenpsaObtype == ORG_OPENPSA_OBTYPE_PROJECT)
            {
                // The parent is a project instead
                $parent = new org_openpsa_projects_project($this->up);
            }
            
            return $parent;
        }
        else
        {
            return null;
        }
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
        $this->get_members(true);
        $this->_update_members();
        $this->_locale_restore();
        $this->_workflow_checks('created');
    }

    function _on_loaded()
    {
        $this->get_members();
        /* This in theory can cause confusion if the actual DB status (cache) field is different.
          but it's better to get the correct value late than never, next update will make sure it's correct in DB as well */
        $this->status = $this->_get_status(true);
        
        if ($this->title == "")
        {
            $this->title = "Task #{$this->id}";
        }

        return true;
    }

    function _on_updating()
    {
        $this->_locale_set();
        if ($this->_prepare_save())
        {
            $this->get_members(true);
            $this->_update_members();
            return true;
        }

        //If we return false here then _on_opdated() never gets called
        $this->_locale_restore();
        return false;
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
                $sync = new org_openpsa_core_acl_synchronizer();
                $sync->write_acls($news_topic, $this->orgOpenpsaOwnerWg, $this->orgOpenpsaAccesstype);
            }
            if ($this->forumTopic)
            {
                $forum_topic = new midcom_baseclasses_database_topic($this->forumTopic);
                $sync = new org_openpsa_core_acl_synchronizer();
                $sync->write_acls($forum_topic, $this->orgOpenpsaOwnerWg, $this->orgOpenpsaAccesstype);
            }
        }

        // Ensure resources can read regardless of if this is a vgroup
        debug_add("Ensuring resources can read the object");
        foreach ($this->resources as $pid => $bool)
        {
            if ($pid)
            {
                $oldPerson = $this->_pid_to_obj($pid);
                debug_add("Setting 'midgard:read' for {$oldPerson->id}");
                $this->set_privilege('midgard:read', $oldPerson->id, MIDCOM_PRIVILEGE_ALLOW);
                
                if ($this->orgOpenpsaObtype == ORG_OPENPSA_OBTYPE_TASK)
                {
                    // Resources must be permitted to create hour/expense reports into tasks
                    $this->set_privilege('midgard:create', $oldPerson->id, MIDCOM_PRIVILEGE_ALLOW);
                    //For declines etc they also need update...
                    $this->set_privilege('midgard:update', $oldPerson->id, MIDCOM_PRIVILEGE_ALLOW);
                }
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

    /**
     * Populates contacts as resources lists
     */
    function get_members($old=false)
    {
        if (!$this->id)
        {
            return false;
        }
        
        if ($old)
        {
            $prefix='old_';
        }
        else
        {
            $prefix='';
        }
        
        $qb = new MidgardQueryBuilder('org_openpsa_task_resource');
        $qb->add_constraint('task', '=', $this->id);
        $ret = @$qb->execute();
        if (   is_array($ret)
            && count($ret)>0)
        {
            foreach ($ret as $resource)
            {
                switch ($resource->orgOpenpsaObtype)
                {
                    case ORG_OPENPSA_OBTYPE_PROJECTCONTACT:
                        $varName=$prefix.'contacts';
                        break;
                    default:
                        //fall-trough intentional
                    case ORG_OPENPSA_OBTYPE_PROJECTRESOURCE:
                        $varName=$prefix.'resources';
                        break;                    
                }
                $property = &$this->$varName;
                $property[$resource->person] = true;
            }
        }

        return true;
    }

    function _update_members()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $ret['resources'] = Array();
        $ret['resources']['added'] = Array();
        $ret['resources']['removed'] = Array();
        $ret['contacts'] = Array();
        $ret['contacts']['added'] = Array();
        $ret['contacts']['removed'] = Array();
        if (!is_array($this->resources))
        {
            $this->resources = Array();
        }
        if (!is_array($this->contacts))
        {
            $this->contacts = Array();
        }
        if (!is_array($this->old_resources))
        {
            $this->old_resources = Array();
        }
        if (!is_array($this->old_contacts))
        {
            $this->old_contacts = Array();
        }

        // ** Start with resources
        $added_resources = array_diff_assoc($this->resources, $this->old_resources);
        $removed_resources = array_diff_assoc($this->old_resources, $this->resources);

        foreach ($added_resources as $resourceId => $bool)
        {
            $resObj = new org_openpsa_projects_task_resource();
            $resObj->person = $resourceId;
            $resObj->task = $this->id;
            $resObj->orgOpenpsaObtype = ORG_OPENPSA_OBTYPE_PROJECTRESOURCE;
            $resObj->create();
            $ret['resources']['added'][$resObj->person] = mgd_errstr();
        }

        foreach ($removed_resources as $resourceId => $bool)
        {
            $resObj = $this->_get_member_by_personid($resourceId);
            if (is_object($resObj))
            {
                $resObj->delete();
                $ret['resources']['removed'][$resObj->person] = mgd_errstr();
            }
        }
        // ** Done with resources

        // ** Start with contacts
        $added_contacts = array_diff_assoc($this->contacts, $this->old_contacts);
        $removed_contacts = array_diff_assoc($this->old_contacts, $this->contacts);
        
        foreach ($added_contacts as $resourceId => $bool)
        {
            $resObj = new org_openpsa_projects_task_resource();
            $resObj->person = $resourceId;
            $resObj->task = $this->id;
            $resObj->orgOpenpsaObtype = ORG_OPENPSA_OBTYPE_PROJECTCONTACT;
            $resObj->create();
            $ret['contacts']['added'][$resObj->person] = mgd_errstr();
        }

        foreach ($removed_contacts as $resourceId => $bool)
        {
            $resObj = $this->_get_member_by_personid($resourceId);
            if (is_object($resObj))
            {
                $resObj->delete();
                $ret['contacts']['removed'][$resObj->person] = mgd_errstr();
            }
        }
        // ** Done with contacts
        debug_add("returning status array: \n===\n" . sprint_r($ret) . "===\n");
        debug_pop();
        return $ret;
    }

    function _get_member_by_personid($id)
    {
        //Find the correct eventmember by person ID
        $finder = new org_openpsa_task_resource();
        $finder->task = $this->id;
        $finder->person = $id;
        $finder->find();
        if ($finder->N > 0)
        {
            //There should be only one match in any case
            $finder->fetch();
            $resObj = new org_openpsa_projects_task_resource($finder->id);
            return $resObj;
        }
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
            $agreement = new org_openpsa_sales_salesproject_deliverable($this->agreement);
            if ($agreement)
            {
                $salesproject = new org_openpsa_sales_salesproject($agreement->salesproject);
                $this->customer = $salesproject->customer;
            }
        }
        
        // Update hour caches in this and agreement
        $this->update_cache(false);

        debug_pop();
        return true;
    }

    function _locale_set()
    {
        $this->_locale_backup = setlocale(LC_NUMERIC, '0');
        setlocale(LC_NUMERIC, 'C');
    }

    function _locale_restore()
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
        
        $reported_hours = 0;
        $approved_hours = 0;
        $invoiced_hours = 0;
        $invoiceable_hours = 0;
        
        $report_qb = org_openpsa_projects_hour_report::new_query_builder();
        $report_qb->add_constraint('task', '=', $this->id);
        $reports = $report_qb->execute();
        foreach ($reports as $report)
        {
            $reported_hours += $report->hours;
            
            if ($report->approved)
            {
                $approved_hours += $report->hours;
            }
            
            //if ($report->invoiced)
            //{
            //    $invoiced_hours += $report->hours;
            //}
            if ($report->invoiceable)
            {
                $invoiceable_hours += $report->hours;
            }
        }
        
        $this->hourCache = $reported_hours;
        
        if ($this->agreement)
        {
            $agreement = new org_openpsa_sales_salesproject_deliverable($this->agreement);
            if ($agreement)
            {
                $agreement->units = $invoiceable_hours;
            }
            $agreement->update();
        }
        
        if ($update)
        {
            $this->update();
        }
    }
    
    function _update_parent()
    {
        if ($this->_skip_parent_refresh)
        {
            return true;
        }
        $project = $this->get_parent();
        if ($project->orgOpenpsaObtype == ORG_OPENPSA_OBTYPE_PROJECT)
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
        $status = new org_openpsa_projects_task_status();
        if ($target_person != 0)
        {
            $status->targetPerson = $target_person;
        }
        $status->task = $this->id;
        $status->type = $status_type;
        //This shouldn't be needed
        $status->timestamp = org_openpsa_projects_task_status::gmtime();
        $status->comment = $comment;
        debug_add("about to create status\n===\n" . sprint_r($status) . "===\n");
        //mgd_debug_start();
        $ret = $status->create();
        //mgd_debug_stop();
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
        $qb = $_MIDCOM->dbfactory->new_query_builder('org_openpsa_projects_task_status');
        $qb->add_constraint('task', '=', $this->id);
        if ($this->status > ORG_OPENPSA_TASKSTATUS_PROPOSED)
        {
            //Only get proposed status objects here if are not over that phase
            $qb->add_constraint('type', '<>', ORG_OPENPSA_TASKSTATUS_PROPOSED); 
        }
        if (count($this->resources)>0)
        {
            //Do not ever set status to declined if we still have resources left
            $qb->add_constraint('type', '<>', ORG_OPENPSA_TASKSTATUS_DECLINED); 
        }
        $qb->add_order('timestamp', 'DESC');
        $qb->add_order('type', 'DESC'); //Our timestamps are not accurate enough so if we have multiple with same timestamp suppose highest type is latest
        $qb->set_limit(1);
        //mgd_debug_start();
        $main_ret = $_MIDCOM->dbfactory->exec_query_builder($qb);
        //mgd_debug_stop();
        debug_add("got main_ret\n===\n" . sprint_r($main_ret) . "===\n");
        if (   !is_array($main_ret)
            || count($main_ret)==0)
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
    
    function _propose_to_resources()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $propose_to = $this->resources;
        
        //Remove those who already have a proposal from the list to propose to
        $qb = $_MIDCOM->dbfactory->new_query_builder('org_openpsa_projects_task_status');
        $qb->add_constraint('task', '=', $this->id);
        $qb->add_constraint('type', '=', ORG_OPENPSA_TASKSTATUS_PROPOSED); 
        //TODO: Make in when reliably supported
        $qb->begin_group('OR');
            foreach ($propose_to as $pid => $bool)
            {
                $qb->add_constraint('targetPerson', '=', $pid);
            }
        $qb->end_group();
        //mgd_debug_start();
        $proposals_ret = $_MIDCOM->dbfactory->exec_query_builder($qb);
        //mgd_debug_stop();
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
            debug_add("saving propsed status for person {$pid}");
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
                //PONDER: Should this be superseded by generic method for queriying the status objects to set the latest status ??
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
        debug_add("task->resources: \n===\n" . sprint_r($this->resources) . "===\n");
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
    function start()
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
        $ret = $this->_create_status(ORG_OPENPSA_TASKSTATUS_STARTED);
        if (!$ret)
        {
            debug_add('failed to create status object, errstr: ' . mgd_errstr());
            debug_pop();
            return false;
        }
        //PONDER: should this be somehow set directly trough mgdschema ??
        //PONDER: Should this be superseded by generic method for queriying the status objects to set the latest status ??
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
    function _drop_to_started($comment = '')
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
        //PONDER: Should this be superseded by generic method for queriying the status objects to set the latest status ??
        $this->status = ORG_OPENPSA_TASKSTATUS_CLOSED;
        debug_pop();
        $this->_skip_acl_refresh = true;
        if ($this->update())
        {
            $_MIDCOM->uimessages->add($_MIDCOM->i18n->get_string('org.openpsa.projects', 'org.openpsa.projects'), sprintf($_MIDCOM->i18n->get_string('marked task "%s" closed', 'org.openpsa.projects'), $this->title), 'ok');
            if ($this->agreement)
            {
                $agreement = new org_openpsa_sales_salesproject_deliverable($this->agreement);
                
                // Set agreement delivered if this is the only open task for it
                $task_qb = org_openpsa_projects_task::new_query_builder();
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
     * Analyses current status and changes, then handles proposals etc
     */
    function _workflow_checks($mode)
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
        $qb = $_MIDCOM->dbfactory->new_query_builder('org_openpsa_projects_task_status');
        $qb->add_constraint('task', '=', $this->id);
        $qb->add_constraint('type', '=', ORG_OPENPSA_TASKSTATUS_PROPOSED); 
        //mgd_debug_start();
        $proposals_ret = $_MIDCOM->dbfactory->exec_query_builder($qb);
        //mgd_debug_stop();
        if (   !is_array($main_ret)
            || count($main_ret)==0)
        {
            debug_add('We don\'t have any proposed status sets, creating those now');
            $this->_propose_to_resources();
        }
        
        debug_pop();
        return true;
    }

}

/**
 * Another wrap level to get to component namespace
 * @package org.openpsa.projects
 */
class org_openpsa_projects_task extends midcom_org_openpsa_task
{
    function org_openpsa_projects_task($identifier = NULL)
    {
        return parent::midcom_org_openpsa_task($identifier);
    }
}
?>