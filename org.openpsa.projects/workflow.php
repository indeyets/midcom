<?php
/**
 * @package org.openpsa.projects
 * @author Nemein Oy http://www.nemein.com/
 * @version $Id: viewer.php,v 1.22 2006/05/13 11:36:45 rambo Exp $
 * @copyright Nemein Oy http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * org.openpsa.projects site interface class.
 *
 * @package org.openpsa.projects
 */
class org_openpsa_projects_workflow
{

    /**
     * Accept the proposal
     */
    static function accept(&$task, $comment = '')
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("task->accept() called with user #{$_MIDGARD['user']}");
        $task->_create_status(ORG_OPENPSA_TASKSTATUS_ACCEPTED, 0, $comment);
        switch ($task->acceptanceType)
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
                $task->status = ORG_OPENPSA_TASKSTATUS_ACCEPTED;
                debug_pop();
                $task->_skip_acl_refresh = true;
                return $task->update();
                break;
        }
        //We should not fall trough this far
        debug_pop();
        return false;
    }

    /**
     * Decline the proposal
     */
    static function decline(&$task, $comment = '')
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("task->decline() called with user  #{$_MIDGARD['user']}");
        $ret = $task->_create_status(ORG_OPENPSA_TASKSTATUS_DECLINED, 0, $comment);
        if (!$ret)
        {
            debug_add('failed to create status object, errstr: ' . mgd_errstr());
            debug_pop();
            return false;
        }
        $resource_removed = false;
        debug_add("task->resources: \n===\n" . org_openpsa_helpers::sprint_r($task->resources) . "===\n");
        if (isset($task->resources[$_MIDGARD['user']]))
        {
            debug_add("removing user #{$_MIDGARD['user']} from resources");
            unset($task->resources[$_MIDGARD['user']]);
            $resource_removed = true;
        }
        if ($resource_removed)
        {
            $task->update();
        }
        debug_pop();

        return self::doublecheck_status($task);
    }

    /**
     * Mark task as started (in case it's not already done)
     */
    static function start(&$task, $started_by = 0)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("task->start() called with user #{$_MIDGARD['user']}");
        //PONDER: Check actual status objects for more accurate logic ?
        if (   $task->status >= ORG_OPENPSA_TASKSTATUS_STARTED
            && $task->status <= ORG_OPENPSA_TASKSTATUS_APPROVED)
        {
            //We already have started status
            debug_add('Task has already been started');
            debug_pop();
            return true;
        }
        $ret = $task->_create_status(ORG_OPENPSA_TASKSTATUS_STARTED, $started_by);
        if (!$ret)
        {
            debug_add('failed to create status object, errstr: ' . mgd_errstr());
            debug_pop();
            return false;
        }
        //PONDER: should this be somehow set directly trough mgdschema ??
        //PONDER: Should this be superseded by generic method for querying the status objects to set the latest status ??
        //PONDER: If we add to closed task shouldn't we reopen or something....
        $task->status = ORG_OPENPSA_TASKSTATUS_STARTED;
        debug_pop();
        $task->_skip_acl_refresh = true;
        return $task->update();
    }

    /**
     * Mark task as completed
     */
    static function complete(&$task, $comment = '')
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("task->complete() called with user #{$_MIDGARD['user']}");
        //TODO: Check deliverables
        if(!$task->_create_status(ORG_OPENPSA_TASKSTATUS_COMPLETED, 0, $comment))
        {
            return false;
        }
        //PONDER: Check ACL in stead ?
        if ($_MIDGARD['user'] == $task->manager)
        {
            //Manager marking task completed also approves it at the same time
            debug_add('We\'re the manager of this task, approving straight away');
            $task->_skip_parent_refresh = true;
            $ret = self::approve($task);
            $task->_skip_parent_refresh = false;
            debug_add("approve returned '{$ret}', errstr: " . mgd_errstr());
            debug_pop();
            return $ret;
        }
        debug_pop();

        return self::doublecheck_status($task);
    }

    /**
     * Drops a completed task to started status
     */
    static function remove_complete(&$task, $comment = '')
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("task->remove_complete() called with user #{$_MIDGARD['user']}");
        if ($task->status != ORG_OPENPSA_TASKSTATUS_COMPLETED)
        {
            //Status is not completed, we can't remove that status.
            debug_add('status != completed, aborting');
            debug_pop();
            return false;
        }
        debug_pop();
        return self::_drop_to_started($task, $comment);
    }

    /**
     * Drops tasks status to started
     */
    private static function _drop_to_started(&$task, $comment = '')
    {
        if ($task->status <= ORG_OPENPSA_TASKSTATUS_STARTED)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('Task has not been started, aborting');
            debug_pop();
            return false;
        }
        if (!$task->_create_status(ORG_OPENPSA_TASKSTATUS_STARTED, 0, $comment))
        {
        	return false;
        }

        return self::doublecheck_status($task);
    }
     
    /**
     * Mark task as approved
     */
    static function approve(&$task, $comment = '')
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("task->approve() called with user #{$_MIDGARD['user']}");
        //TODO: Check deliverables / Require to be completed first
        //PONDER: Check ACL in stead ?
        if ($_MIDGARD['user'] != $task->manager)
        {
            debug_add("Current user #{$_MIDGARD['user']} is not manager of task, thus cannot approve", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        
        if (!$task->_create_status(ORG_OPENPSA_TASKSTATUS_APPROVED, 0, $comment))
        {
            return false;
        }
        debug_add('approved tasks get closed at the same time, calling this->close()');
        $task->_skip_parent_refresh = true;
        $ret = self::close($task);
        $task->_skip_parent_refresh = false;
        debug_add("close returned '{$ret}', errstr: " . mgd_errstr());

        debug_pop();

        return self::doublecheck_status($task);
    }

    static function reject(&$task, $comment = '')
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("task->reject() called with user #{$_MIDGARD['user']}");
        //TODO: Check deliverables / Require to be completed first
        //PONDER: Check ACL in stead ?
        if ($_MIDGARD['user'] != $task->manager)
        {
            debug_add("Current user #{$_MIDGARD['user']} is not manager of task, thus cannot reject", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        if(!$task->_create_status(ORG_OPENPSA_TASKSTATUS_REJECTED, 0, $comment))
        {
            return false;
        }

        debug_pop();

        return self::doublecheck_status($task);
    }

    /**
     * Drops an approved task to started status
     */
    static function remove_approve(&$task, $comment = '')
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("task->remove_approve() called with user #{$_MIDGARD['user']}");
        if ($task->status != ORG_OPENPSA_TASKSTATUS_APPROVED)
        {
            debug_add('Task is not approved, aborting');
            debug_pop();
            return false;
        }
        debug_pop();
        return self::_drop_to_started($comment);
    }
    
    /**
     * Mark task as closed
     */
    static function close(&$task, $comment = '')
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("task->close() called with user #{$_MIDGARD['user']}");
        //TODO: Check deliverables / require to be approved first
        //PONDER: Check ACL in stead ?
        if ($_MIDGARD['user'] != $task->manager)
        {
            debug_add("Current user #{$_MIDGARD['user']} is not manager of task, thus cannot close", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }

        if(!$task->_create_status(ORG_OPENPSA_TASKSTATUS_CLOSED, 0, $comment))
        {
            return false;
        }
        //PONDER: should this be somehow set directly trough mgdschema ??
        //PONDER: Should this be superseded by generic method for querying the status objects to set the latest status ??
        $task->status = ORG_OPENPSA_TASKSTATUS_CLOSED;
        debug_pop();
        $task->_skip_acl_refresh = true;
        if ($task->update())
        {
            $_MIDCOM->uimessages->add($_MIDCOM->i18n->get_string('org.openpsa.projects', 'org.openpsa.projects'), sprintf($_MIDCOM->i18n->get_string('marked task "%s" closed', 'org.openpsa.projects'), $task->title), 'ok');
            if ($task->agreement)
            {
                $agreement = new org_openpsa_sales_salesproject_deliverable_dba($task->agreement);

                // Set agreement delivered if this is the only open task for it
                $task_qb = org_openpsa_projects_task_dba::new_query_builder();
                $task_qb->add_constraint('agreement', '=', $task->agreement);
                $task_qb->add_constraint('status', '<', ORG_OPENPSA_TASKSTATUS_CLOSED);
                $task_qb->add_constraint('id', '<>', $task->id);
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
    static function reopen(&$task, $comment = '')
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("task->reopen() called with user #{$_MIDGARD['user']}");
        if ($task->status != ORG_OPENPSA_TASKSTATUS_CLOSED)
        {
            debug_add('Task is not closed, aborting');
            debug_pop();
            return false;
        }
        if(!$task->_create_status(ORG_OPENPSA_TASKSTATUS_REOPENED, 0, $comment))
        {
            return false;
        }
        debug_pop();

        return self::doublecheck_status($task);
    }

    /**
     * Connect the task to an invoice
     */
    static function mark_invoiced(&$task, &$invoice)
    {
        // Register a relation between the invoice and the task
        $relation = org_openpsa_relatedto_handler::create_relatedto($invoice, 'org.openpsa.invoices', $task, 'org.openpsa.projects');

        // Mark the hour reports invoiced
        $hours_marked = 0;
        $report_qb = org_openpsa_projects_hour_report_dba::new_query_builder();
        $report_qb->add_constraint('task', '=', $task->id);
        $report_qb->add_constraint('invoiced', '=', '0000-00-00 00:00:00');

        // Check how the agreement deals with hour reports
        $check_approvals = false;
        if ($task->agreement)
        {
            $agreement = new org_openpsa_sales_salesproject_deliverable_dba($task->agreement);
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

            $invoice_member = new org_openpsa_invoices_invoice_hour_dba();
            $invoice_member->hourReport = $report->id;
            $invoice_member->invoice = $invoice->id;
            if ($invoice_member->create())
            {
                $hours_marked += $report->hours;
            }
        }

        // Update hour caches to agreement
        $task->update_cache(false);

        // Notify user
        $_MIDCOM->uimessages->add($_MIDCOM->i18n->get_string('org.openpsa.projects', 'org.openpsa.projects'), sprintf($_MIDCOM->i18n->get_string('marked %s hours as invoiced in task "%s"', 'org.openpsa.projects'), $hours_marked, $task->title), 'ok');
    }

    /**
     * doublechecks status
     * 
     * @todo Why is this necessary?
     */
     private static function doublecheck_status(&$task)
     {
        $stat = $task->_get_status();
        if ($stat != $task->status)
        {
            debug_add("doublechecked status {$stat} does not match current status {$task->status}, updating");
            //PONDER: should this be somehow set directly trough mgdschema ??
            $task->status = $stat;
            debug_pop();
            $task->_skip_acl_refresh = true;
            return $task->update();
        }
        return true;
     }

    /**
     * Analyzes current status and changes, then handles proposals etc
     */
    static function workflow_checks(&$task, $mode)
    {
        $main_ret = Array();
        debug_push_class(__CLASS__, __FUNCTION__);
        if ($mode == 'created')
        {
            self::_propose_to_resources($task);
            debug_pop();
            return true;
        }

        //TODO: The more complex checks...

        //Always make sure we have proposals (DBE kind of follows these) in place (DM goes trough our create mode without any resources...)
        $qb = org_openpsa_projects_task_status_dba::new_query_builder();
        $qb->add_constraint('task', '=', $task->id);
        $qb->add_constraint('type', '=', ORG_OPENPSA_TASKSTATUS_PROPOSED);

        $proposals_ret = $qb->execute();

        if (   !is_array($main_ret)
            || count($main_ret)==0)
        {
            debug_add('We don\'t have any proposed status sets, creating those now');
            self::_propose_to_resources($task);
        }

        debug_pop();
        return true;
    }
    
        private static function _propose_to_resources(&$task)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $task->get_members();
        $propose_to = $task->resources;

        //Remove those who already have a proposal from the list to propose to
        $qb = org_openpsa_projects_task_status_dba::new_query_builder();
        
        $qb->add_constraint('task', '=', $task->id);
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
            $task->_create_status(ORG_OPENPSA_TASKSTATUS_PROPOSED, $pid);
            //If creator is in resources he would naturally accept his own proposal...
            if ($pid == $task->creator)
            {
                self::accept($task);
            }
        }
        
        debug_pop();
        return self::doublecheck_status($task);
    }
    
}
?>