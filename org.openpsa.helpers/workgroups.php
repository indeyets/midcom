<?php
/**
 * Helper function for listing virtual groups of user
 * @package org.openpsa.helpers
 * @author Henri Bergius, http://bergie.iki.fi
 * @version $Id: workgroups.php,v 1.10 2005/12/21 16:44:08 rambo Exp $
 * @copyright Nemein Oy, http://www.nemein.com
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

function org_openpsa_helpers_workgroups($add_me = 'last', $show_members = false)
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

                if (substr($key, strlen($key)-11) == 'subscribers')
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
?>