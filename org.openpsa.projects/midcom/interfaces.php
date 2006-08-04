<?php
/**
 * OpenPSA group projects
 * 
 * 
 * @package org.openpsa.projects
 */
class org_openpsa_projects_interface extends midcom_baseclasses_components_interface
{
    
    function org_openpsa_projects_interface()
    {        
        parent::midcom_baseclasses_components_interface();
        $this->_component = 'org.openpsa.projects';
        $this->_autoload_class_definitions = array('midcom_dba_classes.inc');
        $this->_autoload_files = array(
            'admin.php',
            'viewer.php',
            'navigation.php',
            'task_midcomdba.php',              
            'project_midcomdba.php', 
            'resource_midcomdba.php', 
            'hour_report_midcomdba.php', 
            'expense_midcomdba.php', 
            'mileage_midcomdba.php',                        
            'hours_widget.php',
            'deliverables/deliverable_midcomdba.php',
            'deliverables/interface.php',
            'deliverables/plugin_base.php',
            'deliverables/plugin_noop.php',
            'task_status_midcomdba.php',
            'workflow_handler.php',            
        );
        $this->_autoload_libraries = Array( 
            'org.openpsa.core', 
            'org.openpsa.helpers',
            'midcom.helper.datamanager',
            'org.openpsa.contactwidget',
            'org.openpsa.relatedto', 
        );
        
        $this->_fill_virtual_groups();
    }

    function _fill_virtual_groups()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $qb = new MidgardQueryBuilder('org_openpsa_task');
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
        debug_push_class(__CLASS__, __FUNCTION__);
        $type = 'resources';
        if (substr($groupname, strlen($groupname)-11) == 'subscribers')
        {
            debug_add('Listing contacts instead of resources');
            $groupname = substr($groupname, 0, strlen($groupname)-11);
            $type = 'contacts';
        }
        $members = array();
        $project = new org_openpsa_projects_project($groupname);
        if (   !$project
            || !$project->id)
        {
            debug_add("\"{$groupname}\" cannot be loaded as project, returning empty array", MIDCOM_LOG_WARN);
            //PONDER: Should we fail with more vigor here ??
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
        return $members;
    }


    function _on_initialize()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        //With the plentyness of typecasting around any other numeric locale calls for trouble with floats
        setlocale(LC_NUMERIC, 'C');
                
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
        $qb = new MidgardQueryBuilder('org_openpsa_task_resource');
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
            $link = new org_openpsa_relatedto_relatedto();
            org_openpsa_relatedto_suspect::defaults_helper($link, $defaults, $this->_component, $task);
            $to_array['other_obj'] = $task;
            $to_array['link'] = $link;
            
            $links_array[] = $to_array;
        }
        debug_pop();
        return;
    }
    
    /**
     * Used by org_openpsa_relatedto_find_suspects to in case the givcen object is a person
     */
    function _org_openpsa_relatedto_find_suspects_person(&$object, &$defaults, &$links_array)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        //List all projects and tasks given person is involved with
        $qb = new MidgardQueryBuilder('org_openpsa_task_resource');
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
            $link = new org_openpsa_relatedto_relatedto();
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
        if (!is_a($task, 'midcom_org_openpsa_task'))
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
            || !is_a($link, 'org_openpsa_relatedto_relatedto'))
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

}
?>