<?php
/**
 * Midcom wrapped access to the MgdSchema class, keep logic here
 * @package org.openpsa.projects
 */
class midcom_org_openpsa_task_resource extends __midcom_org_openpsa_task_resource
{
    var $_personobject;

    function midcom_org_openpsa_task_resource($id = null)
    {
        return parent::__midcom_org_openpsa_task_resource($id);
    }

    
    function get_parent_guid_uncached()
    {
        if ($this->task != 0)
        {
            $parent = new org_openpsa_projects_task($this->task);
            
            if ($parent->orgOpenpsaObtype == ORG_OPENPSA_OBTYPE_PROJECT)
            {
                // The parent is a project instead
                $parent = new org_openpsa_projects_project($this->task);
            }
            
            return $parent;
        }
        else
        {
            return null;
        }
    }
    
    function _add_to_buddylist_of($account)
    {
        $account = new midcom_db_person($account);
        $qb = org_openpsa_contacts_buddy::new_query_builder();
        $user =& $_MIDCOM->auth->user->get_storage();
        $qb->add_constraint('account', '=', $account->guid);
        $qb->add_constraint('buddy', '=', $this->_personobject->guid);
        $qb->add_constraint('blacklisted', '=', false);
        $buddies = $qb->execute();
        
        if (count($buddies) == 0)
        {
            // Cache the association to buddy list of the sales project owner
            $buddy = new org_openpsa_contacts_buddy();
            $buddy->account = $account->guid;
            $buddy->buddy = $this->_personobject->guid;
            $buddy->isapproved = false;
            return $buddy->create();
        }
    }
    
    function _on_created()
    {
        if ($this->person)
        {
            $this->_personobject = $this->_pid_to_obj($this->person);
            $this->set_privilege('midgard:read', $this->_personobject->id, MIDCOM_PRIVILEGE_ALLOW);
            $this->set_privilege('midgard:delete', $this->_personobject->id, MIDCOM_PRIVILEGE_ALLOW);
            $this->set_privilege('midgard:update', $this->_personobject->id, MIDCOM_PRIVILEGE_ALLOW);
            
            // Add resource to manager's buddy list
            $task = new org_openpsa_projects_task($this->task);
            $this->_add_to_buddylist_of($task->manager);
            
            // Add resource to other resources' buddy lists
            $qb = org_openpsa_projects_task_resource::new_query_builder();
            $qb->add_constraint('task', '=', $this->task);
            $qb->add_constraint('orgOpenpsaObtype', '=', ORG_OPENPSA_OBTYPE_PROJECTRESOURCE);
            $qb->add_constraint('id', '<>', $this->id);
            $resources = $qb->execute();
            foreach ($resources as $resource)
            {
                $this->_add_to_buddylist_of($resource->person);
            }
        }
        return true;
    }     

    function _pid_to_obj($pid)
    {
        return $_MIDCOM->auth->get_user($pid);
    }
}

/**
 * Another wrap level to get to component namespace
 * @package org.openpsa.projects
 */
class org_openpsa_projects_task_resource extends midcom_org_openpsa_task_resource
{
    function org_openpsa_projects_task_resource($identifier=NULL)
    {
        return parent::midcom_org_openpsa_task_resource($identifier); 
    }
}
?>