<?php
/**
 * @package org.openpsa.projects
 * @author Nemein Oy http://www.nemein.com/
 * @copyright Nemein Oy http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * special case 'project' of class org_openpsa_projects_task
 * @package org.openpsa.projects
 */
class org_openpsa_projects_project extends org_openpsa_projects_task
{
    function __construct($identifier = NULL)
    {
        return parent::__construct($identifier);
    }

    function _on_creating()
    {
        $stat = parent::_on_creating();
        $this->orgOpenpsaObtype = ORG_OPENPSA_OBTYPE_PROJECT;
        return $stat;
    }

    function _prepare_save()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $stat = parent::_prepare_save();

        debug_pop();
        return $stat;
    }

    function _on_updated()
    {
        $participants_vgroup = new midcom_core_group_virtual("org.openpsa.projects-{$this->guid}");
        $contacts_vgroup = new midcom_core_group_virtual("org.openpsa.projects-{$this->guid}subscribers");
        // Not finished projects are active workgroups, finished ones are inactive (but still workgroups).
        if ($this->status != ORG_OPENPSA_TASKSTATUS_CLOSED)
        {
            $this->orgOpenpsaWgtype = ORG_OPENPSA_WGTYPE_ACTIVE;

            if (!is_object($participants_vgroup))
            {
                // Register workgroups here
                debug_add("Registering workgroup: Project participants");
                $_MIDCOM->auth->request_sudo();
                $_MIDCOM->auth->register_virtual_group('org.openpsa.projects', $this->guid, $this->title);
                $_MIDCOM->auth->drop_sudo();
            }
            else
            {
                //Renaming vgroup is made with delete+register cycle
                debug_add("Deleting previous workgroup: Project participants");
                $_MIDCOM->auth->request_sudo();
                $_MIDCOM->auth->delete_virtual_group($participants_vgroup);
                debug_add("Registering workgroup: Project participants");
                // TODO: localize
                $_MIDCOM->auth->register_virtual_group('org.openpsa.projects', $this->guid, $this->title);
                $_MIDCOM->auth->drop_sudo();
            }

            if (!is_object($contacts_vgroup))
            {
                debug_add("Registering workgroup: Project subscribers");
                // TODO: localize
                $_MIDCOM->auth->request_sudo();
                $_MIDCOM->auth->register_virtual_group('org.openpsa.projects', "{$this->guid}subscribers", "{$this->title} contacts");
                $_MIDCOM->auth->drop_sudo();
            }
            else
            {
                //Renaming vgroup is made with delete+register cycle
                debug_add("Deleting previous workgroup: Project subscribers");
                $_MIDCOM->auth->request_sudo();
                $_MIDCOM->auth->delete_virtual_group($contacts_vgroup);
                debug_add("Registering workgroup: Project subscribers");
                // TODO: localize
                $_MIDCOM->auth->register_virtual_group('org.openpsa.projects', "{$this->guid}subscribers", "{$this->title} contacts");
                $_MIDCOM->auth->drop_sudo();
            }
        }
        else
        {
            debug_add("This is a finished project, no workgroups registered");
            //Remove workgroups
            if (is_object($contacts_vgroup))
            {
                //TODO: This can be a problem for vgroup ACL selector...
                $_MIDCOM->auth->request_sudo();
                $_MIDCOM->auth->delete_virtual_group($contacts_vgroup);
                $_MIDCOM->auth->drop_sudo();
            }
            if (is_object($participants_vgroup))
            {
                //TODO: This can be a problem for vgroup ACL selector...
                $_MIDCOM->auth->request_sudo();
                $_MIDCOM->auth->delete_virtual_group($participants_vgroup);
                $_MIDCOM->auth->drop_sudo();
            }
            $this->orgOpenpsaWgtype = ORG_OPENPSA_WGTYPE_INACTIVE;
        }
        parent::_on_updated();
    }

    function _update_parent()
    {
        // Not needed for now
        // TODO: Update information upwards in project hierarchy?
        return true;
    }

    function _refresh_from_tasks()
    {
        $this->get_members();
        debug_push_class(__CLASS__, __FUNCTION__);
        $update_required = false;

        $qb = org_openpsa_projects_task::new_query_builder();
        $qb->add_constraint('up', '=', $this->id);

        $ret = $qb->execute();
        $task_statuses = Array();
        $task_number = count($ret);

        if (   is_array($ret)
            && count($ret) > 0)
        {
            $this->resources = array();
            $this->contacts = array();
            foreach($ret as $task)
            {
                $task = new org_openpsa_projects_task($task->id);
                $task->get_members();
                if ($task->start < $this->start)
                {
                    $this->start = $task->start;
                    $update_required = true;
                }
                if ($task->end > $this->end)
                {
                    $this->end = $task->end;
                    $update_required = true;
                }
                foreach ($task->resources as $pid => $bool)
                {
                    if (   $pid
                        && !array_key_exists($pid, $this->resources))
                    {
                        $this->resources[$pid] = true;
                        $update_required = true;
                    }
                }
                foreach ($task->contacts as $pid => $bool)
                {
                    if (   $pid
                        && !array_key_exists($pid, $this->contacts))
                    {
                        $this->contacts[$pid] = true;
                        $update_required = true;
                    }
                }

                //Simple way to handle accepted and various "under work" statuses
                if (!array_key_exists($task->status, $task_statuses))
                {
                    $task_statuses[$task->status] = 0;
                }
                $task_statuses[$task->status]++;

            }

            //TODO: Some way to check if all tasks of project are completed (or above) and set to lowest common.
            $orig_status = null;
            $new_status = null;
            foreach ($task_statuses as $status => $tasks)
            {
                if ($tasks == $task_number)
                {
                    // If all tasks are of the same type, that is the type to use then
                    $new_status = $status;
                }
                else
                {
                    if (   $status > $this->status
                        && $status < ORG_OPENPSA_TASKSTATUS_COMPLETED)
                    {
                        $new_status = $status;
                    }
                }
            }

            if (   !is_null($new_status)
                && $this->status != $new_status)
            {
                $this->_create_status($new_status);
            }
        }
        if ($update_required)
        {
            debug_add("Some project information needs to be updated, skipping ACL refresh");
            $this->_skip_acl_refresh = true;
            debug_pop();
            return $this->update();
        }
        else
        {
            debug_add("All project information is up-to-date");
            debug_pop();
            return true;
        }
    }

}
?>