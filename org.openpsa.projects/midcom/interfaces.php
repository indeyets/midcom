<?php
/**
 * @package org.openpsa.projects
 * @author Nemein Oy http://www.nemein.com/
 * @copyright Nemein Oy http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * OpenPSA group projects
 *
 *
 * @package org.openpsa.projects
 */
class org_openpsa_projects_interface extends midcom_baseclasses_components_interface
{

    function __construct()
    {
        parent::__construct();
        $this->_component = 'org.openpsa.projects';
        $this->_autoload_class_definitions = array('midcom_dba_classes.inc');
        $this->_autoload_files = array
        (
            'deliverables/deliverable_midcomdba.php',
            /* 
             * @todo The code using this is commented out (handler/task/view.php)
             * 
            'deliverables/interface.php',
            'deliverables/plugin_base.php',
            'deliverables/plugin_noop.php',
            */
            'workflow_handler.php',
        );
        $this->_autoload_libraries = Array
        (
            'org.openpsa.core',
            'org.openpsa.helpers',
            'midcom.helper.datamanager',
            'org.openpsa.contactwidget',
            'org.openpsa.calendarwidget',
        );

        $this->_fill_virtual_groups();
    }

    function _fill_virtual_groups()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $qb = new midgard_query_builder('org_openpsa_task');
        //$qb = $_MIDCOM->dbfactory->new_query_builder('org_openpsa_projects_project');
        // FIXME: Constant ORG_OPENPSA_WGTYPE_ACTIVE is not set yet
        $qb->add_constraint('orgOpenpsaWgtype', '=', 3);
        $ret = @$qb->execute();
        //$ret = $_MIDCOM->dbfactory->exec_query_builder($qb);
        if (   is_array($ret)
            && count($ret) > 0)
        {
            foreach($ret as $wg)
            {
                debug_add('Adding workgroup: '.$wg->title.' (guid: '.$wg->guid.')');
                $this->_virtual_groups[$wg->guid] = $wg->title;
                $this->_virtual_groups[$wg->guid.'subscribers'] = $wg->title.' contacts';
            }
        }
        debug_pop();
        return true;
    }

    function _on_retrieve_vgroup_members($groupname)
    {
        if (!class_exists('org_openpsa_projects_task_resource'))
        {
            return null;
        }

        static $vgroup_members = Array();

        debug_push_class(__CLASS__, __FUNCTION__);
        $type = 'resources';
        if (substr($groupname, strlen($groupname)-11) == 'subscribers')
        {
            debug_add('Listing contacts instead of resources');
            $groupname = substr($groupname, 0, strlen($groupname)-11);
            $type = 'contacts';
        }
        
        if (isset($vgroup_members[$groupname]))
        {
            return $vgroup_members[$groupname];
        }
      
        $members = array();
        $project = new org_openpsa_projects_project($groupname);
        if (   !$project
            || !$project->id)
        {
            debug_add("\"{$groupname}\" cannot be loaded as project, returning empty array", MIDCOM_LOG_WARN);
            //PONDER: Should we fail with more vigor here ??
            $vgroup_members[$groupname] = $members;
            return $members;
        }

        $qb = $_MIDCOM->dbfactory->new_query_builder('org_openpsa_projects_task_resource');
        $qb->add_constraint('task', '=', $project->id);
        if ($type == 'contacts')
        {
            $qb->add_constraint('orgOpenpsaObtype', '=', ORG_OPENPSA_OBTYPE_PROJECTCONTACT);
        }
        else
        {
            $qb->add_constraint('orgOpenpsaObtype', '=', ORG_OPENPSA_OBTYPE_PROJECTRESOURCE);
        }
        $ret = $_MIDCOM->dbfactory->exec_query_builder($qb);
        if (   is_array($ret)
            && count($ret) > 0)
        {
            foreach($ret as $member)
            {
                if ($member->person)
                {
                    debug_add('Adding person: '.$member->person);
                    $members[] = $member->person;
                }
            }
        }
        debug_pop();
        $vgroup_members[$groupname] = $members;
        return $members;
    }


    function _on_initialize()
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        $_MIDCOM->componentloader->load_graceful('org.openpsa.sales');

        //With the plentyness of typecasting around any other numeric locale calls for trouble with floats
        setlocale(LC_NUMERIC, 'C');

        //org.openpsa.projects object types
        define('ORG_OPENPSA_OBTYPE_PROJECT', 6000);
        define('ORG_OPENPSA_OBTYPE_PROCESS', 6001);
        define('ORG_OPENPSA_OBTYPE_TASK', 6002);
        define('ORG_OPENPSA_OBTYPE_HOUR_REPORT', 6003);
        define('ORG_OPENPSA_OBTYPE_EXPENSE', 6004);
        define('ORG_OPENPSA_OBTYPE_MILEAGE', 6005);
        define('ORG_OPENPSA_OBTYPE_PROJECTRESOURCE', 6006);
        define('ORG_OPENPSA_OBTYPE_PROJECTCONTACT', 6007);
        define('ORG_OPENPSA_OBTYPE_PROJECTPROSPECT', 6008);
        //org.openpsa.projects status types
        //Templates/Drafts
        define('ORG_OPENPSA_TASKSTATUS_DRAFT', 6450);
        define('ORG_OPENPSA_TASKSTATUS_TEMPLATE', 6451);
        define('ORG_OPENPSA_TASKSTATUS_PROPOSED', 6500);
        define('ORG_OPENPSA_TASKSTATUS_DECLINED', 6510);
        define('ORG_OPENPSA_TASKSTATUS_ACCEPTED', 6520);
        define('ORG_OPENPSA_TASKSTATUS_ONHOLD', 6530);
        define('ORG_OPENPSA_TASKSTATUS_STARTED', 6540);
        define('ORG_OPENPSA_TASKSTATUS_REJECTED', 6545);
        define('ORG_OPENPSA_TASKSTATUS_REOPENED', 6550);
        define('ORG_OPENPSA_TASKSTATUS_COMPLETED', 6560);
        define('ORG_OPENPSA_TASKSTATUS_APPROVED', 6570);
        define('ORG_OPENPSA_TASKSTATUS_CLOSED', 6580);
        //org.openpsa.projects acceptance negotiation types
        define('ORG_OPENPSA_TASKACCEPTANCE_ALLACCEPT', 6700);
        define('ORG_OPENPSA_TASKACCEPTANCE_ONEACCEPT', 6701);
        define('ORG_OPENPSA_TASKACCEPTANCE_ONEACCEPTDROP', 6702);

        debug_pop();
        return true;
    }

    function _on_resolve_permalink($topic, $config, $guid)
    {
        $task = new org_openpsa_projects_task($guid);
        if (!$task)
        {
            return null;
        }

        if ($task->orgOpenpsaObtype == ORG_OPENPSA_OBTYPE_PROJECT)
        {
            return "project/{$task->guid}/";
        }
        else
        {
            return "task/{$task->guid}/";
        }
    }

    /**
     * Used by org_openpsa_relatedto_suspect::find_links_object to find "related to" information
     *
     * Currently handles persons
     */
    function org_openpsa_relatedto_find_suspects($object, $defaults, &$links_array)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        if (   !is_array($links_array)
            || !is_object($object))
        {
            debug_add('$links_array is not array or $object is not object, make sure you call this correctly', MIDCOM_LOG_ERROR);
            debug_pop();
            return;
        }

        switch(true)
        {
            case is_a($object, 'midcom_baseclasses_database_person'):
            case is_a($object, 'midcom_org_openpsa_person'):
                $this->_org_openpsa_relatedto_find_suspects_person($object, $defaults, $links_array);
                break;
            case is_a($object, 'midcom_baseclasses_database_event'):
            case is_a($object, 'midcom_org_openpsa_event'):
                $this->_org_openpsa_relatedto_find_suspects_event($object, $defaults, $links_array);
                break;


                //TODO: groups ? other objects ?
        }
        debug_pop();
        return;
    }

    /**
     * Used by org_openpsa_relatedto_find_suspects to in case the givcen object is a person
     *
     * Current rule: all participants of event must be either manager,contact or resource in task
     * that overlaps in time with the event.
     */
    function _org_openpsa_relatedto_find_suspects_event(&$object, &$defaults, &$links_array)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        if (   !is_array($object->participants)
            || count($object->participants) < 1)
        {
            //We have invalid list or zero participants, abort
            debug_pop();
            return;
        }
        $qb = org_openpsa_projects_task_resource::new_query_builder();
        //Target task starts or ends inside given events window or starts before and ends after
        $qb->begin_group('OR');
            $qb->begin_group('AND');
                $qb->add_constraint('task.start', '>=', $object->start);
                $qb->add_constraint('task.start', '<=', $object->end);
            $qb->end_group();
            $qb->begin_group('AND');
                $qb->add_constraint('task.end', '<=', $object->end);
                $qb->add_constraint('task.end', '>=', $object->start);
            $qb->end_group();
            $qb->begin_group('AND');
                $qb->add_constraint('task.start', '<=', $object->start);
                $qb->add_constraint('task.end', '>=', $object->end);
            $qb->end_group();
        $qb->end_group();
        //Target task is active
        $qb->add_constraint('task.status', '<', ORG_OPENPSA_TASKSTATUS_COMPLETED);
        $qb->add_constraint('task.status', '<>', ORG_OPENPSA_TASKSTATUS_DECLINED);
        //Each event participant is either manager or member (resource/contact) in task
        foreach ($object->participants as $pid => $bool)
        {
            $qb->begin_group('OR');
                $qb->add_constraint('task.manager', '=', $pid);
                $qb->add_constraint('person', '=', $pid);
            $qb->end_group();
        }
        $qbret = @$qb->execute();
        if (!is_array($qbret))
        {
            debug_add('QB returned with error, aborting, errstr: ' . mgd_errstr(), MIDCOM_LOG_ERROR);
            debug_pop();
            return;
        }
        $seen_tasks = array();
        foreach ($qbret as $resource)
        {
            debug_add("processing resource #{$resource->id}");
            if (isset($seen_tasks[$resource->task]))
            {
                //Only process one task once (someone might be both resource and contact for example)
                continue;
            }
            $seen_tasks[$resource->task] = true;
            $to_array = array('other_obj' => false, 'link' => false);
            $task = new org_openpsa_projects_task($resource->task);
            $link = new org_openpsa_relatedto_relatedto_dba();
            org_openpsa_relatedto_suspect::defaults_helper($link, $defaults, $this->_component, $task);
            $to_array['other_obj'] = $task;
            $to_array['link'] = $link;

            $links_array[] = $to_array;
        }
        debug_pop();
        return;
    }

    /**
     * Used by org_openpsa_relatedto_find_suspects to in case the given object is a person
     */
    function _org_openpsa_relatedto_find_suspects_person(&$object, &$defaults, &$links_array)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        //List all projects and tasks given person is involved with
        $qb = org_openpsa_projects_task_resource::new_query_builder();
        $qb->add_constraint('person', '=', $object->id);
        /* This could reduce clutter somewhat with a minor risk of missing a link
        $qb->add_constraint('orgOpenpsaObtype', '=', ORG_OPENPSA_OBTYPE_PROJECTRESOURCE);
        */
        $qb->add_constraint('task.status', '<', ORG_OPENPSA_TASKSTATUS_COMPLETED);
        $qb->add_constraint('task.status', '<>', ORG_OPENPSA_TASKSTATUS_DECLINED);
        $qbret = @$qb->execute();
        if (!is_array($qbret))
        {
            debug_add('QB returned with error, aborting, errstr: ' . mgd_errstr(), MIDCOM_LOG_ERROR);
            debug_pop();
            return;
        }
        $seen_tasks = array();
        foreach ($qbret as $resource)
        {
            debug_add("processing resource #{$resource->id}");
            if (isset($seen_tasks[$resource->task]))
            {
                //Only process one task once (someone might be both resource and contact for example)
                continue;
            }
            $seen_tasks[$resource->task] = true;
            $to_array = array('other_obj' => false, 'link' => false);
            $task = new org_openpsa_projects_task($resource->task);
            $link = new org_openpsa_relatedto_relatedto_dba();
            org_openpsa_relatedto_suspect::defaults_helper($link, $defaults, $this->_component, $task);
            $to_array['other_obj'] = $task;
            $to_array['link'] = $link;

            $links_array[] = $to_array;
        }
        debug_pop();
        return;
    }

    function create_hour_report(&$task, $person_id, &$from_object, $from_component)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        if (!is_a($task, 'org_openpsa_projects_task'))
        {
            debug_add('given task is not really a task', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        if (empty($person_id))
        {
            debug_add('person_id is "empty"', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }

        // Set these so that the automatic owner/creator/etc assignments work properly
        $GLOBALS['midgard_user_backup'] = $_MIDGARD['user'];
        $_MIDGARD['user'] = $person_id;
        $GLOBALS['midcom_user_backup'] = $_MIDCOM->auth->user;
        $_MIDCOM->auth->user = $_MIDCOM->auth->get_user($_MIDGARD['user']);

        $hr = new org_openpsa_projects_hour_report();
        $hr->task = $task->id;
        $hr->person = $person_id;
        $hr->invoiceable = $task->hoursInvoiceableDefault;

        switch (true)
        {
            case is_a($from_object, 'midcom_org_openpsa_event'):
                $event =& $from_object;
                $hr->date = $event->start;
                $hr->hours = round((($event->end - $event->start)/3600),2);
                // TODO: Localize ? better indicator that this is indeed from event ??
                $hr->description = "event: {$event->title} " . $event->format_timeframe() . ", {$event->location}\n";
                $hr->description .= "\n{$event->description}\n";
                break;
            default:
                debug_add("class '" . get_class($from_object) . "' not supported", MIDCOM_LOG_ERROR);
                debug_pop();
                return false;
        }
        debug_add("about to create hour_report\n===\n" . sprint_r($hr) . "===\n");

        $stat = $hr->create();
        if (!$stat)
        {
            debug_add("failed to create hour_report to task #{$task->id} for person #{$person_id}", MIDCOM_LOG_ERROR);
            debug_pop();
            // Return correct user
            $_MIDGARD['user'] = $GLOBALS['midgard_user_backup'];
            $_MIDCOM->auth->user = $GLOBALS['midcom_user_backup'];
            return false;
        }
        debug_add("created hour_report #{$hr->id}");

        // Create a relatedtolink from hour_report to the object it was created from
        $link = org_openpsa_relatedto_handler::create_relatedto($hr, 'org.openpsa.projects', $from_object, $from_component);
        if (   !is_object($link)
            || !is_a($link, 'org_openpsa_relatedto_relatedto_dba'))
        {
            debug_add("failed to create link from hour_report #{$hr->id} to " . get_class($from_object) . " {$from_object->guid}, errstr: " . mgd_errstr(), MIDCOM_LOG_WARN);
        }
        debug_add("created link #{$link->id}");

        // Return correct user
        $_MIDGARD['user'] = $GLOBALS['midgard_user_backup'];
        $_MIDCOM->auth->user = $GLOBALS['midcom_user_backup'];
        debug_pop();
        return true;
    }

    /**
     * Support for contacts person merge
     */
    function org_openpsa_contacts_duplicates_merge_person(&$person1, &$person2, $mode)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        switch($mode)
        {
            case 'all':
                break;
            /* In theory we could have future things (like resource/manager ships), but now we don't support that mode, we just exit */
            case 'future':
                return true;
                break;
            default:
                // Mode not implemented
                debug_add("mode {$mode} not implemented", MIDCOM_LOG_ERROR);
                debug_pop();
                return false;
                break;
        }

        // Transfer links from classes we drive
        // ** resources **
        $qb_member = org_openpsa_projects_task_resource::new_query_builder();
        $qb_member->add_constraint('sitegroup', '=', $_MIDGARD['sitegroup']);
        $qb_member->add_constraint('person', '=', $person2->id);
        $members = $qb_member->execute();
        if ($members === false)
        {
            // Some error with QB
            debug_add('QB Error', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        // Transfer memberships
        $membership_map = array();
        foreach ($members as $member)
        {
            // TODO: figure out duplicate memberships and delete unneeded ones
            $member->person = $person1->id;
            debug_add("Transferred task resource #{$member->id} to person #{$person1->id} (from #{$member->person})", MIDCOM_LOG_INFO);
            if (!$member->update())
            {
                debug_add("Failed to update task resource #{$member->id}, errstr: " . mgd_errstr(), MIDCOM_LOG_ERROR);
                debug_pop();
                return false;
            }
        }

        // ** task statuses **
        $qb_receipt = org_openpsa_projects_task_status::new_query_builder();
        $qb_receipt->add_constraint('sitegroup', '=', $_MIDGARD['sitegroup']);
        $qb_receipt->add_constraint('targetPerson', '=', $person2->id);
        $receipts = $qb_receipt->execute();
        if ($receipts === false)
        {
            // Some error with QB
            debug_add('QB Error / status', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        foreach($receipts as $receipt)
        {
            debug_add("Transferred task_status #{$receipt->id} to person #{$person1->id} (from #{$receipt->person})", MIDCOM_LOG_INFO);
            $receipt->targetPerson = $person1->id;
            if (!$receipt->update())
            {
                // Error updating
                debug_add("Failed to update status #{$receipt->id}, errstr: " . mgd_errstr(), MIDCOM_LOG_ERROR);
                debug_pop();
                return false;
            }
        }

        // ** hour reports **
        $qb_log = org_openpsa_projects_hour_report::new_query_builder();
        $qb_log->add_constraint('sitegroup', '=', $_MIDGARD['sitegroup']);
        $qb_log->add_constraint('person', '=', $person2->id);
        $logs = $qb_log->execute();
        if ($logs === false)
        {
            // Some error with QB
            debug_add('QB Error / hours', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        foreach($logs as $log)
        {
            debug_add("Transferred hour_report #{$log->id} to person #{$person1->id} (from #{$log->person})", MIDCOM_LOG_INFO);
            $log->person = $person1->id;
            if (!$log->update())
            {
                // Error updating
                debug_add("Failed to update hour_report #{$log->id}, errstr: " . mgd_errstr(), MIDCOM_LOG_ERROR);
                debug_pop();
                return false;
            }
        }

        // ** Task managers **
        $qb_task = org_openpsa_projects_task::new_query_builder();
        $qb_task->add_constraint('sitegroup', '=', $_MIDGARD['sitegroup']);
        $qb_task->add_constraint('manager', '=', $person2->id);
        $tasks = $qb_task->execute();
        if ($tasks === false)
        {
            // Some error with QB
            debug_add('QB Error / tasks', MIDCOM_task_ERROR);
            debug_pop();
            return false;
        }
        foreach($tasks as $task)
        {
            debug_add("Transferred task #{$task->id} to person #{$person1->id} (from #{$task->person})", MIDCOM_task_INFO);
            $task->manager = $person1->id;
            if (!$task->update())
            {
                // Error updating
                debug_add("Failed to update task #{$task->id}, errstr: " . mgd_errstr(), MIDCOM_task_ERROR);
                debug_pop();
                return false;
            }
        }

        // ** expense reports **
        $qb_expense = org_openpsa_projects_expense::new_query_builder();
        $qb_expense->add_constraint('sitegroup', '=', $_MIDGARD['sitegroup']);
        $qb_expense->add_constraint('person', '=', $person2->id);
        $expenses = $qb_expense->execute();
        if ($expenses === false)
        {
            // Some error with QB
            debug_add('QB Error / expenses', MIDCOM_expense_ERROR);
            debug_pop();
            return false;
        }
        foreach($expenses as $expense)
        {
            debug_add("Transferred expense #{$expense->id} to person #{$person1->id} (from #{$expense->person})", MIDCOM_expense_INFO);
            $expense->person = $person1->id;
            if (!$expense->update())
            {
                // Error updating
                debug_add("Failed to update expense #{$expense->id}, errstr: " . mgd_errstr(), MIDCOM_expense_ERROR);
                debug_pop();
                return false;
            }
        }


        // Transfer metadata dependencies from classes that we drive
        $classes = array(
            'org_openpsa_projects_task_resource',
            'org_openpsa_projects_task_status',
            'org_openpsa_projects_task',
            'org_openpsa_projects_hour_report',
            'org_openpsa_projects_expense',
        );
        foreach($classes as $class)
        {
            if ($version_not_18 = true)
            {
                switch($class)
                {
                    default:
                        $metadata_fields = array(
                            'creator' => 'id',
                            'revisor' => 'id' // Though this will probably get touched on update we need to check it anyways to avoid invalid links
                        );
                        break;
                }
            }
            else
            {
                // TODO: 1.8 metadata format support
            }
            $ret = org_openpsa_contacts_duplicates_merge::person_metadata_dependencies_helper($class, $person1, $person2, $metadata_fields);
            if (!$ret)
            {
                // Failure updating metadata
                debug_add("Failed to update metadata dependencies in class {$class}, errsrtr: " . mgd_errstr(), MIDCOM_LOG_ERROR);
                debug_pop();
                return false;
            }
        }

        // All done
        return true;
    }

    function background_search_resources($args, &$handler)
    {
        $task = new org_openpsa_projects_task($args['task']);
        if (!is_object($task))
        {
            // TODO: error reporting
            return false;
        }
        $broker = new org_openpsa_projects_projectbroker();
        $broker->membership_filter = $args['membership_filter'];
        return $broker->save_task_prospects($task);
    }
}
?>