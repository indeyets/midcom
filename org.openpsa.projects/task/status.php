<?php
/**
 * @package org.openpsa.projects
 * @author Nemein Oy http://www.nemein.com/
 * @version $Id: task_status_midcomdba.php,v 1.5 2006/05/13 11:12:40 rambo Exp $
 * @copyright Nemein Oy http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * MidCOM wrapped access to the MgdSchema class, keep logic here
 * @package org.openpsa.projects
 */
class org_openpsa_projects_task_status extends __org_openpsa_projects_task_status
{
    function __construct($id = null)
    {
        $ret = parent::__construct($id);
        if (!$this->id)
        {
            if (is_object($this))
            {
                $this->timestamp = $this->gmtime();
            }
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
        $qb = org_openpsa_projects_task_status::new_query_builder();
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

    function get_status_message()
    {
        switch ($this->type)
        {
            case ORG_OPENPSA_TASKSTATUS_PROPOSED:
                return 'proposed to %s by %s';
            case ORG_OPENPSA_TASKSTATUS_DECLINED:
                return 'declined by %s';
            case ORG_OPENPSA_TASKSTATUS_ACCEPTED:
                return 'accepted by %s';
            case ORG_OPENPSA_TASKSTATUS_ONHOLD:
                return 'put on hold by %s';
            case ORG_OPENPSA_TASKSTATUS_STARTED:
                return 'work started by %s';
            case ORG_OPENPSA_TASKSTATUS_REJECTED:
                return 'rejected by %s';
            case ORG_OPENPSA_TASKSTATUS_REOPENED:
                return 're-opened by %s';
            case ORG_OPENPSA_TASKSTATUS_COMPLETED:
                return 'marked as completed by %s';
            case ORG_OPENPSA_TASKSTATUS_APPROVED:
                return 'approved by %s';
            case ORG_OPENPSA_TASKSTATUS_CLOSED:
                return 'closed by %s';
            case ORG_OPENPSA_TASKSTATUS_DBE_SYNC_OK:
                return 'synchronized with %s by %s';
            default:
                return "{$this->type} by %s";
        }
    }

    function gmtime()
    {
        return gmmktime(date('G'), date('i'), date('s'), date('n'), date('j'), date('Y'));
    }
}

?>