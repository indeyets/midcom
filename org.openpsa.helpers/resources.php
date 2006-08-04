<?php
/**
 * Helper function for listing virtual groups of user
 * @package org.openpsa.helpers
 * @author Henri Bergius, http://bergie.iki.fi
 * @version $Id: resources.php,v 1.2 2005/10/14 06:59:52 bergius Exp $
 * @copyright Nemein Oy, http://www.nemein.com
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @return Array List of persons appropriate for the current selection
 */
function org_openpsa_helpers_resources()
{
    // List members of selected ACL group for usage in DM arrays
    if (!array_key_exists('org_openpsa_helpers_resources', $GLOBALS))
    {
        $GLOBALS['org_openpsa_helpers_resources'] = array();
        
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
?>