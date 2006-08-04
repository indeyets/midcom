<?php
/**
 * @package org.openpsa.sales
 * @author Nemein Oy, http://www.nemein.com/
 * @version $Id: salesproject.php,v 1.5 2006/05/12 16:50:32 rambo Exp $
 * @copyright Nemein Oy, http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * Midcom wrapped base class, keep logic here
 * 
 * @package org.openpsa.sales
 */
class midcom_org_openpsa_salesproject extends __midcom_org_openpsa_salesproject
{
    var $contacts = array(); //Shorthand access for contact members
    var $old_contacts = array(); //For diffing the ones above
    
    /* These two are filled correctly as arraus with the get_actions method */
    var $prev_action = false; 
    var $next_action = false; 

    function midcom_org_openpsa_salesproject($id = null)
    {
        return parent::__midcom_org_openpsa_salesproject($id);
    }

    /**
     * Fills the next and previous action properties
     * based on the confirmed relatedto links
     *
     * If optional argument is set only considers actions
     * where said person is involved, NOT IMPLEMENTED
     */
    function get_actions($limit_to_person = false)
    {
        //TODO: Implement $limit_to_person support
        debug_push_class(__CLASS__, __FUNCTION__);
        //PONDER: gracefull loads and then later check for class_exists ??
        $_MIDCOM->componentloader->load('org.openpsa.projects');
        $_MIDCOM->componentloader->load('org.openpsa.calendar');
        $default = array(
            'time'  => false,
            'obj'   => false,
            /* valid types are: noaction, task, event */
            'type'  => 'noaction',
        );
        $this->prev_action = $default;
        $this->next_action = $default;
        
        $qb = org_openpsa_relatedto_relatedto::new_query_builder();
        $qb->add_constraint('toGuid', '=', $this->guid);
        //In theory I could limit just by the class but this is more robust in the long run
        $qb->begin_group('OR');
            $qb->begin_group('AND');
                $qb->add_constraint('fromComponent', '=', 'org.openpsa.calendar');
                $qb->add_constraint('fromClass', '=', 'org_openpsa_calendar_event');
            $qb->end_group();
            $qb->begin_group('AND');
                $qb->add_constraint('fromComponent', '=', 'org.openpsa.projects');
                $qb->add_constraint('fromClass', '=', 'org_openpsa_projects_task');
            $qb->end_group();
        $qb->end_group();
        $links = $qb->execute();
        if (   !is_array($links)
            || count($links) == 0)
        {
            debug_pop();
            return;
        }

        $sort_prev = array();
        $sort_next = array();
        foreach($links as $link)
        {
            $to_sort = $default;
            switch ($link->fromClass)
            {
                case 'org_openpsa_projects_task':
                    $task = new org_openpsa_projects_task($link->fromGuid);
                    if (!$task)
                    {
                        continue 2;
                    }
                    $to_sort['obj'] = $task;
                    $to_sort['type'] = 'task';
                    if ($task->status >= ORG_OPENPSA_TASKSTATUS_COMPLETED)
                    {
                        $to_sort['time'] = $task->status_time;
                        $sort_prev[] = $to_sort;
                    }
                    else
                    {
                        $to_sort['time'] = $task->end;
                        if ($task->end < time())
                        {
                            //PONDER: Do something ?
                        }
                        $sort_next[] = $to_sort;
                    }
                    break;
                case 'org_openpsa_calendar_event':
                    $event = new org_openpsa_calendar_event($link->fromGuid);
                    if (!$event)
                    {
                        continue 2;
                    }
                    $to_sort['obj'] = $event;
                    $to_sort['type'] = 'event';
                    if ($event->end < time())
                    {
                        $to_sort['time'] = $event->end;
                        $sort_prev[] = $to_sort;
                    }
                    else
                    {
                        $to_sort['time'] = $event->start;
                        $sort_next[] = $to_sort;
                    }
                    break;
                default:
                    continue 2;
            }
        }
        usort($sort_prev, 'org_openpsa_sales_salesproject_sort_action_by_time_reverse');
        usort($sort_next, 'org_openpsa_sales_salesproject_sort_action_by_time');
        debug_add("sort_next \n===\n" . sprint_r($sort_next) . "===\n");
        debug_add("sort_prev \n===\n" . sprint_r($sort_prev) . "===\n");
        
        if (isset($sort_next[0]))
        {
            $this->next_action = $sort_next[0];
        }
        if (isset($sort_prev[0]))
        {
            $this->prev_action = $sort_prev[0];
        }
        debug_pop();
        return;
    }

    function _on_creating()
    {
        if (!$this->start)
        {
            $this->start = time();
        }
        if (!$this->status)
        {
            $this->status = ORG_OPENPSA_SALESPROJECTSTATUS_ACTIVE;
        }
        if (!$this->orgOpenpsaObtype)
        {
            $this->orgOpenpsaObtype = ORG_OPENPSA_OBTYPE_SALESPROJECT;
        }
        if (!$this->owner)
        {
            $this->owner = $_MIDGARD['user'];
        }
        return true;
    }
    
    function _on_updating()
    {
        if (   $this->status != ORG_OPENPSA_SALESPROJECTSTATUS_ACTIVE
            && !$this->end)
        {
            //Not active anymore and end not set, set it to now
            $this->end = time();
        }
        if (   $this->end
            && $this->status == ORG_OPENPSA_SALESPROJECTSTATUS_ACTIVE)
        {
            //Returned to active status, clear the end marker.
            $this->end = 0;
        }

        $this->get_members(true);
        $this->_update_members();

        return true;
    }

    function _on_loaded()
    {
        $this->get_members(false);
        
        if (empty($this->title))
        {
            $this->title = "salesproject #{$this->id}";
        }
        
        return true;
    }

    function _pid_to_obj($pid)
    {
        return $_MIDCOM->auth->get_user($pid);
    }

    function _on_updated()
    {
        //Ensure owner can do stuff regardless of other ACLs
        if ($this->owner)
        {
            $owner_person = $this->_pid_to_obj($this->manager);
            $this->set_privilege('midgard:read', $owner_person->id, MIDCOM_PRIVILEGE_ALLOW);
            $this->set_privilege('midgard:create', $owner_person->id, MIDCOM_PRIVILEGE_ALLOW);
            $this->set_privilege('midgard:delete', $owner_person->id, MIDCOM_PRIVILEGE_ALLOW);
            $this->set_privilege('midgard:update', $owner_person->id, MIDCOM_PRIVILEGE_ALLOW);
        }
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
        
        $qb = new MidgardQueryBuilder('org_openpsa_salesproject_member');
        $qb->add_constraint('salesproject', '=', $this->id);
        $ret = @$qb->execute();
        if (   is_array($ret)
            && count($ret)>0)
        {
            foreach ($ret as $contact)
            {
                switch ($contact->orgOpenpsaObtype)
                {
                    /*
                    case ORG_OPENPSA_OBTYPE_SALESPROJECT_MEMBER_foo:
                        $varName=$prefix . 'foo';
                        break;
                    */
                    default:
                        //fall-trough intentional
                    case ORG_OPENPSA_OBTYPE_SALESPROJECT_MEMBER:
                        $varName=$prefix . 'contacts';
                        break;                    
                }
                $property = &$this->$varName;
                $property[$contact->person] = true;
            }
        }

        return true;
    }

    function _update_members()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $ret['contacts'] = array();
        $ret['contacts']['added'] = array();
        $ret['contacts']['removed'] = array();
        if (!is_array($this->contacts))
        {
            $this->contacts = array();
        }
        if (!is_array($this->old_contacts))
        {
            $this->old_contacts = array();
        }

        // ** Start with contacts
        $added_contacts = array_diff_assoc($this->contacts, $this->old_contacts);
        $removed_contacts = array_diff_assoc($this->old_contacts, $this->contacts);
        
        foreach ($added_contacts as $resourceId => $bool)
        {
            $resObj = new org_openpsa_sales_salesproject_member();
            $resObj->person = $resourceId;
            $resObj->salesproject = $this->id;
            $resObj->orgOpenpsaObtype = ORG_OPENPSA_OBTYPE_SALESPROJECT_MEMBER;
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
        //Find the correct salesproject_member by person ID
        $finder = new org_openpsa_salesproject_member();
        $finder->salesproject = $this->id;
        $finder->person = $id;
        $finder->find();
        if ($finder->N > 0)
        {
            //There should be only one match in any case
            $finder->fetch();
            $resObj = new org_openpsa_sales_salesproject_member($finder->id);
            return $resObj;
        }
        return false;
    }

    function get_parent_guid_uncached()
    {
        if ($this->up != 0)
        {
            $parent = new midcom_org_openpsa_salesproject($this->up);            
            return $parent;
        }
        else
        {
            return null;
        }
    }
}

/**
 * For sorting arrays in get_actions method (usort doesn't like even static methods)
 */
function org_openpsa_sales_salesproject_sort_action_by_time($a, $b)
{
    $ap = $a['time'];
    $bp = $b['time'];
    if ($ap > $bp)
    {
        return 1;
    }
    if ($ap < $bp)
    {
        return -1;
    }
    return 0;
}

/**
 * For sorting arrays in get_actions method (usort doesn't like even static methods)
 */
function org_openpsa_sales_salesproject_sort_action_by_time_reverse($a, $b)
{
    $ap = $a['time'];
    $bp = $b['time'];
    if ($ap < $bp)
    {
        return 1;
    }
    if ($ap > $bp)
    {
        return -1;
    }
    return 0;
}


/**
 * Wrap the midcom class to component namespace
 * 
 * @package org.openpsa.sales
 */
class org_openpsa_sales_salesproject extends midcom_org_openpsa_salesproject
{
    function org_openpsa_sales_salesproject($identifier=NULL)
    {
        return parent::midcom_org_openpsa_salesproject($identifier); 
    }
}
?>