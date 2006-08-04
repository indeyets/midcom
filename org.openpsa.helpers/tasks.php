<?php
/**
 * Helper function for listing tasks user can see
 * @package org.openpsa.helpers
 * @author Henri Bergius, http://bergie.iki.fi
 * @version $Id: tasks.php,v 1.4 2005/10/27 10:54:42 rambo Exp $
 * @copyright Nemein Oy, http://www.nemein.com
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */
function org_openpsa_helpers_projects($add_all = false, $display_tasks = false, $require_privileges = false)
{
    //Make sure the class we need exists
    if (!class_exists('org_openpsa_projects_task'))
    {
        $_MIDCOM->componentloader->load('org.openpsa.projects');
    }
    //Only query once pper request
    if (!array_key_exists('org_openpsa_helpers_tasks', $GLOBALS))
    {
        $GLOBALS['org_openpsa_helpers_tasks'] = array();
        if ($add_all)
        {
            //TODO: Localization
            $GLOBALS['org_openpsa_helpers_tasks']['all'] = 'all';
        }
        
        $qb = $_MIDCOM->dbfactory->new_query_builder('org_openpsa_projects_task');
        /*
         * Display those that are active or finished less than two weeks ago
         * FIXME: Swithc to new task architecture
        $qb->begin_group('OR');
            $qb->add_constraint('finished', '>', time()-(3600*24*14));
            $qb->add_constraint('status', '=', 0);
        $qb->end_group();*/
        
        // Workgroup filtering
        if ($GLOBALS['org_openpsa_core_workgroup_filter'] != 'all')
        {
            $qb->add_constraint('orgOpenpsaOwnerWg', '=', $GLOBALS['org_openpsa_core_workgroup_filter']);
        }
        
        //Object type filtering
        $qb->begin_group('OR');
            $qb->add_constraint('orgOpenpsaObtype', '=', ORG_OPENPSA_OBTYPE_PROJECT);
            $qb->add_constraint('orgOpenpsaObtype', '=', ORG_OPENPSA_OBTYPE_PROCESS);
            if ($display_tasks)
            {
                $qb->add_constraint('orgOpenpsaObtype', '=', ORG_OPENPSA_OBTYPE_TASK);
            }
        $qb->end_group();

        //Execute
        $ret = $_MIDCOM->dbfactory->exec_query_builder($qb);
        if (   is_array($ret)
            && count($ret)>0)
        {
            foreach ($ret as $task)
            {
                if ($require_privileges)
                {
                    //TODO: check via ACL.
                }
                $GLOBALS['org_openpsa_helpers_tasks'][$task->guid()] = $task->title;
            }
        }
    }
    return $GLOBALS['org_openpsa_helpers_tasks'];
}
?>