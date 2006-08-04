<?php
/**
 * Function for listing groups tasks contacts are members of
 *
 * @package org.openpsa.helpers
 * @author Eero af Heurlin, http://www.iki.fi/rambo
 * @version $Id: task_groups.php,v 1.1 2006/05/04 11:46:00 rambo Exp $
 * @copyright Nemein Oy, http://www.nemein.com
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */
function org_openpsa_helpers_task_groups(&$task, $mode = 'id')
{
    //TODO: Localize something for the empty choice ?
    $ret = array(0 => '');
    $seen = array();

    if (!$_MIDCOM->componentloader->load_graceful('org.openpsa.contacts'))
    {
        //PONDER: Maybe we should raise a fatal error ??
        return $ret;
    }

    //Make sure the currently selected customer (if any) is listed
    if (   $task->customer
        && !isset($ret[$task->customer]))
    {
        //Make sure we can read the current customer for the name
        $_MIDCOM->auth->request_sudo();
        $company = new org_openpsa_contacts_group($task->customer);
        $_MIDCOM->auth->drop_sudo();
        $seen[$company->id] = true;
        _org_openpsa_helpers_task_groups_put($ret, $mode, $company);
    }

    if (   !is_array($task->contacts)
        || count($task->contacts) == 0)
    {
        return $ret;
    }

    $qb = new MidgardQueryBuilder('midgard_member');
    $qb->begin_group('OR');
    foreach ($task->contacts as $pid => $bool)
    {
        if (!$bool)
        {
            //Safeguard
            continue;
        }
        $qb->add_constraint('uid', '=', $pid);
    }
    $qb->end_group();
    $memberships = @$qb->execute();
    //echo "<pre>DEBUG: got memberships \n===\n" . sprint_r($memberships) . "===</pre>\n";
    if (   !is_array($memberships)
        || count($memberships) == 0)
    {
        return $ret;
    }

    reset ($memberships);
    foreach ($memberships as $member)
    {
        if (isset($seen[$member->gid])
            && $seen[$member->gid] == true)
        {
            continue;
        }
        $company = new org_openpsa_contacts_group($member->gid);
        if (   !is_object($company)
            || !$company->id
            /* Skip magic groups */
            || preg_match('/^__/', $company->name))
        {
            continue;
        }
        $seen[$company->id] = true;
        _org_openpsa_helpers_task_groups_put($ret, $mode, $company);
    }
    //echo "<pre>DEBUG: returning \n===\n" . sprint_r($ret) . "===</pre>\n";
    reset ($ret);
    return $ret;
}

function _org_openpsa_helpers_task_groups_put(&$ret, &$mode, &$company)
{
    if ($company->official)
    {
        $name = $company->official;
    }
    elseif (   !$company->official
            && $company->name)
    {
        $name = $company->name;
    }
    else
    {
        $name = "#{$company->id}";
    }
    switch ($mode)
    {
        case 'id':
            $ret[$company->id] = $name;
            break;
        case 'guid':
            $ret[$company->guid] = $name;
            break;
        default:
            //Mode not supported
            return;
            break;
    }
}

?>