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
class org_openpsa_projects_task_resource_dba extends __org_openpsa_projects_task_resource_dba
{
    var $_personobject;

    function __construct($id = null)
    {
        return parent::__construct($id);
    }


    function get_parent_guid_uncached()
    {
        if ($this->task != 0)
        {
            $parent = new org_openpsa_projects_task_dba($this->task);

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
        if (!$_MIDCOM->auth->user)
        {
            return false;
        }
        $account = new midcom_db_person($account);
        $qb = org_openpsa_contacts_buddy_dba::new_query_builder();
        $user = $_MIDCOM->auth->user->get_storage();
        $qb->add_constraint('account', '=', (string)$account->guid);
        $qb->add_constraint('buddy', '=', (string)$this->_personobject->guid);
        $qb->add_constraint('blacklisted', '=', false);
        $buddies = $qb->execute();

        if (count($buddies) == 0)
        {
            // Cache the association to buddy list of the sales project owner
            $buddy = new org_openpsa_contacts_buddy_dba();
            $buddy->account = $account->guid;
            $buddy->buddy = $this->_personobject->guid;
            $buddy->isapproved = false;
            return $buddy->create();
        }
    }

    function _find_duplicates()
    {
        $qb = org_openpsa_projects_task_resource_dba::new_query_builder();
        $qb->add_constraint('person', '=', (int)$this->person);
        $qb->add_constraint('task', '=', (int)$this->task);
        $qb->add_constraint('orgOpenpsaObtype', '=', (int)$this->orgOpenpsaObtype);

        if ($this->id)
        {
            $qb->add_constraint('id', '<>', (int)$this->id);
        }

        $dupes = $qb->execute();
        if (count($dupes) > 0)
        {
            return true;
        }
        return false;
    }

    function _on_creating()
    {
        if ($this->_find_duplicates())
        {
            return false;
        }

        return parent::_on_creating();
    }

    function _on_created()
    {
        if ($this->person)
        {
            $this->_personobject = $this->_pid_to_obj($this->person);
            $this->set_privilege('midgard:read', $this->_personobject->id, MIDCOM_PRIVILEGE_ALLOW);
            $this->set_privilege('midgard:delete', $this->_personobject->id, MIDCOM_PRIVILEGE_ALLOW);
            $this->set_privilege('midgard:update', $this->_personobject->id, MIDCOM_PRIVILEGE_ALLOW);
            $this->set_privilege('midgard:read', $this->_person, MIDCOM_PRIVILEGE_ALLOW);

            // Add resource to manager's buddy list
            $task = new org_openpsa_projects_task_dba($this->task);
            $this->_add_to_buddylist_of($task->manager);

            // Add resource to other resources' buddy lists
            $qb = org_openpsa_projects_task_resource_dba::new_query_builder();
            $qb->add_constraint('task', '=', (int)$this->task);
            $qb->add_constraint('orgOpenpsaObtype', '=', ORG_OPENPSA_OBTYPE_PROJECTRESOURCE);
            $qb->add_constraint('id', '<>', (int)$this->id);
            $resources = $qb->execute();
            foreach ($resources as $resource)
            {
                $this->_add_to_buddylist_of($resource->person);
            }
        }
        return true;
    }

    function _on_updating()
    {
        if ($this->_find_duplicates())
        {
            return false;
        }

        return parent::_on_updating();
    }

    function _pid_to_obj($pid)
    {
        return $_MIDCOM->auth->get_user($pid);
    }

    static function get_resource_tasks($key = 'id', $list_finished = false)
    {
        $task_array = array();
        if (!$_MIDCOM->auth->user)
        {
            return $task_array;
        }

        $mc = org_openpsa_projects_task_resource_dba::new_collector('person', (int) $_MIDGARD['user']);
        $mc->add_value_property('task');
        $mc->add_constraint('orgOpenpsaObtype', '=', ORG_OPENPSA_OBTYPE_PROJECTRESOURCE);
        $mc->add_constraint('task.orgOpenpsaObtype', '<>', ORG_OPENPSA_OBTYPE_PROJECT);
        $mc->add_constraint('task.start', '<=', time());

        if (!$list_finished)
        {
            $mc->add_constraint( 'task.status', '<', ORG_OPENPSA_TASKSTATUS_COMPLETED);
        }
        $mc->execute();

        $resources = $mc->list_keys();
        $i = 0;
        foreach ($resources as $resource => $task_id)
        {
            $task = new org_openpsa_projects_task_dba($mc->get_subkey($resource, 'task'));
            $i++;
            if (!$task)
            {
                continue;
            }

            $task_array[$task->$key] = $task->get_label();
        }

        return $task_array;
    }
}
?>