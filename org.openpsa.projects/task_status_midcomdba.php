<?php
/**
 * @package org.openpsa.projects
 * @author Nemein Oy http://www.nemein.com/
 * @version $Id: task_status_midcomdba.php,v 1.5 2006/05/13 11:12:40 rambo Exp $
 * @copyright Nemein Oy http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * Midcom wrapped access to the MgdSchema class, keep logic here
 * @package org.openpsa.projects
 */
class midcom_org_openpsa_task_status extends __midcom_org_openpsa_task_status
{
    function midcom_org_openpsa_task_status($id = null)
    {
        $ret = parent::__midcom_org_openpsa_task_status($id);
        if (!$this->id)
        {
            $this->timestamp = $this->gmtime();
        }
        return $ret;
    }

    function get_parent_guid_uncached()
    {
        if ($this->task != 0)
        {
            $parent = new org_openpsa_projects_task($this->task);            
            return $parent;
        }
        else
        {
            return null;
        }
    }    
    
    function _on_creating()
    {
        //Make sure we have timestamp
        if ($this->timestamp == 0)
        {
            $this->timestamp = $this->gmtime();
        }
        
        //Check for duplicate(s) (for some reason at times the automagic actions in task object try to create duplicate statuses)
        $qb = new MidgardQueryBuilder('org_openpsa_task_status');
        $qb->add_constraint('task', '=', 'task');
        $qb->add_constraint('type', '=', 'type');
        $qb->add_constraint('timestamp', '=', 'timestamp');
        $qb->add_constraint('comment', '=', 'comment');
        if ($this->targetPerson)
        {
            $qb->add_constraint('targetPerson', '=', 'targetPerson');
        }
        $qbret = @$qb->execute();
        if (   is_array($qbret)
            && count($qbret) > 0)
        {
            debug_add('Duplicate statuses found, aborting create', MIDCOM_LOG_WARN);
            debug_add("List of duplicate status objects \n===\n" . sprint_r($qbret) . "===\n");
            return false;
        }
        
        return true;
    }

    function gmtime()
    {
        return gmmktime(date('G'), date('i'), date('s'), date('n'), date('j'), date('Y'));
    }
}

/**
 * Another wrap level to get to component namespace
 * @package org.openpsa.projects
 */
class org_openpsa_projects_task_status extends midcom_org_openpsa_task_status
{

    function org_openpsa_projects_task_status($identifier=NULL)
    {
        return parent::__midcom_org_openpsa_task_status($identifier); 
    }
    
}

?>