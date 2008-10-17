<?php
/**
 * Collection of list functions for OpenPSA
 *
 * @package org.openpsa.helpers
 * @author Eero af Heurlin, http://www.iki.fi/rambo
 * @version $Id: main.php,v 1.8 2006/06/13 10:50:52 rambo Exp $
 * @copyright Nemein Oy, http://www.nemein.com
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * @package org.openpsa.helpers
 */
class org_openpsa_helpers_list
{
    
    /**
     * Function for listing groups tasks contacts are members of
     */
    static function task_groups(&$task, $mode = 'id')
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
            $company = new org_openpsa_contacts_group_dba($task->customer);
            $_MIDCOM->auth->drop_sudo();
            $seen[$company->id] = true;
            self::task_groups_put($ret, $mode, $company);
        }
        $task->get_members();
        if (   !is_array($task->contacts)
            || count($task->contacts) == 0)
        {
            return $ret;
        }
    
        $mc = new midgard_collector('midcom_db_member', 'uid', $this->contact_details['id']);
        $mc->set_key_property('guid');
        $mc->add_value_property('gid');
        $mc->add_constraint('uid', 'IN', array_keys($task->contacts));
        $mc->execute();

        $memberships = @$mc->list_keys();
        //echo "<pre>DEBUG: got memberships \n===\n" . org_openpsa_helpers::sprint_r($memberships) . "===</pre>\n";
        if (   !is_array($memberships)
            || count($memberships) == 0)
        {
            return $ret;
        }
    
        reset ($memberships);
        foreach ($memberships as $guid => $empty)
        {
            $gid = $mc->get_subkey($guid, 'gid');
            if (isset($seen[$gid])
                && $seen[$gid] == true)
            {
                continue;
            }
            $company = new org_openpsa_contacts_group_dba($gid);
            if (   !is_object($company)
                || !$company->id
                /* Skip magic groups */
                || preg_match('/^__/', $company->name))
            {
                continue;
            }
            $seen[$company->id] = true;
            self::task_groups_put($ret, $mode, $company);
        }
        //echo "<pre>DEBUG: returning \n===\n" . org_openpsa_helpers::sprint_r($ret) . "===</pre>\n";
        reset ($ret);
        return $ret;
    }
    
    static function task_groups_put(&$ret, &$mode, &$company)
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

    /**
     * Helper function for listing tasks user can see
     */
    static function projects($add_all = false, $display_tasks = false, $require_privileges = false)
    {
        //Make sure the class we need exists
        if (!class_exists('org_openpsa_projects_task_dba'))
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
    
            $qb = org_openpsa_projects_task_dba::new_query_builder();
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
            $ret = $qb->execute();
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

    /**
     * Helper function for listing virtual groups of user
     */
    static function workgroups($add_me = 'last', $show_members = false)
    {
        //mgd_debug_start();
        // List user's ACL groups for usage in DM arrays
        $array_name = 'org_openpsa_helpers_workgroups_cache_' . $add_me . '_' . $show_members;
        if (!array_key_exists($array_name, $GLOBALS))
        {
            $GLOBALS[$array_name] = array();
            $my_subscription_groups = array();
            if ($_MIDCOM->auth->user)
            {
                if ($add_me == 'first')
                {
                    //TODO: Localization
                    $GLOBALS[$array_name][$_MIDCOM->auth->user->id] = 'me';
                }
    
                if ($_MIDGARD['admin'])
                {
                    // Admins must see all workgroups, all the time
                    $users_vgroups = $_MIDCOM->auth->get_all_vgroups();
                    $users_groups = $_MIDCOM->auth->user->list_memberships();
                    $users_groups = array_merge($users_vgroups, $users_groups);
                }
                else
                {
                    // Regular people see only their own
                    $users_groups = $_MIDCOM->auth->user->list_memberships();
                }
                foreach ($users_groups as $key => $vgroup)
                {
                    if (is_object($vgroup))
                    {
                        $label = $vgroup->name;
                    }
                    else
                    {
                        $label = $vgroup;
                    }
    
                    if (substr($key, strlen($key) - 11) == 'subscribers')
                    {
                        if ($_MIDGARD['admin'])
                        {
                            debug_add("Not showing subscriber groups to admin");
                        }
                        else
                        {
                            debug_add("This is subscriber group, get the real group instead");
                            $real_group = $_MIDCOM->auth->get_group(substr($key, 0, strlen($key)-11));
                            if ($real_group)
                            {
                                $key = $real_group->id;
                                $label = $real_group->name;
                            }
                            $my_subscription_groups[$key] = $label;
                        }
                    }
                    else
                    {
                        $GLOBALS[$array_name][$key] = $label;
    
                        //TODO: get the vgroup object based on the key or something, this check fails always.
                        if (   $show_members
                            && is_object($vgroup)
                            )
                        {
                            $vgroup_members = $vgroup->list_members();
                            foreach ($vgroup_members as $key2 => $person)
                            {
                                $GLOBALS[$array_name][$key2] = '&nbsp;&nbsp;&nbsp;' . $person->name;
                            }
                        }
                    }
                }
    
    
    
                if ($add_me == 'last')
                {
                    //TODO: Localization
                    $GLOBALS[$array_name][$_MIDCOM->auth->user->id] = 'me';
                }
    
                // Add subscription lists after real ones
                foreach ($my_subscription_groups as $key => $label)
                {
                    if (!array_key_exists($key, $GLOBALS[$array_name]))
                    {
                        $GLOBALS[$array_name][$key] = $label;
                    }
                }
            }
        }
        //mgd_debug_stop();
        return $GLOBALS[$array_name];
    }

    /**
     * Helper function for listing virtual groups of user
     *
     * @return Array List of persons appropriate for the current selection
     */
    static function resources()
    {
        // List members of selected ACL group for usage in DM arrays
        if (!array_key_exists('org_openpsa_helpers_resources', $GLOBALS))
        {
            $GLOBALS['org_openpsa_helpers_resources'] = array();
            //Safety
            if (!isset($GLOBALS['org_openpsa_core_workgroup_filter']))
            {
                $GLOBALS['org_openpsa_core_workgroup_filter'] = 'all';
            }
    
            if (   $GLOBALS['org_openpsa_core_workgroup_filter'] == 'all'
                && $_MIDCOM->auth->user)
            {
                // Populate only the user himself to the list
                $user = $_MIDCOM->auth->user->get_storage();
                $GLOBALS['org_openpsa_helpers_resources'][$user->id] = true;
            }
            else
            {
                $group = & $_MIDCOM->auth->get_group($GLOBALS['org_openpsa_core_workgroup_filter']);
                if ($group)
                {
                    $members = $group->list_members();
                    foreach ($members as $person)
                    {
                        $member = $person->get_storage();
                        $GLOBALS['org_openpsa_helpers_resources'][$member->id] = true;
                    }
                }
            }
        }
        return $GLOBALS['org_openpsa_helpers_resources'];
    }

}

?>