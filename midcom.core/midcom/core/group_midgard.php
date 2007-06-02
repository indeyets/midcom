<?php
/**
 * @package midcom
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id:group_midgard.php 3765 2006-07-31 08:51:39 +0000 (Mon, 31 Jul 2006) tarjei $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * MidCOM group implementation supporting Midgard Groups.
 *
 * @package midcom
 */
class midcom_core_group_midgard extends midcom_core_group
{
    /**
     * Contains the parent of the current group, cached for repeated accesses.
     *
     * @access private
     * @var midcom_core_group_midgard
     */
    var $_cached_parent_group = null;

    /**
     * The constructor retrieves the group indentified by its name from the database and
     * prepares the object for operation.
     *
     * The class relies on the Midgard Framework to ensure the uniqueness of a group name.
     *
     * @param mixed $id This is a valid identifier for the group to be loaded. Usually this is either
     *     a database ID or GUID for Midgard Groups or a valid complete MidCOM group identifier, which
     *     will work for all subclasses.
     */
    function midcom_core_group_midgard($id = null)
    {
        parent::midcom_core_group($id);
    }

    /**
     * Helper function that will look up a group and assign the object to the $storage
     * member.
     *
     * It will use the Query Builder to retrieve a group by its name and populate the
     * $storage, $name and $id members accordingly.
     *
     * Any error will call midcom_application::generate_error().
     *
     * @param mixed $id This is a valid identifier for the group to be loaded. Usually this is either
     *     a database ID or GUID for Midgard Groups or a valid complete MidCOM group identifier, which
     *     will work for all subclasses.
     * @return bool Indicating success.
     */
    function _load($id)
    {
        if (   is_string($id)
            && substr($id, 0, 6) == 'group:')
        {
            $this->_storage = new midgard_group();
            $id = substr($id, 6);
            if (! $this->_storage->get_by_guid($id))
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("Failed to retreive the group GUID {$id}: " . mgd_errstr(), MIDCOM_LOG_INFO);
                debug_pop();
                return false;
            }
        }
        else if (mgd_is_guid($id))
        {
            $this->_storage = new midgard_group();
            if (! $this->_storage->get_by_guid($id))
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("Failed to retreive the group GUID {$id}: " . mgd_errstr(), MIDCOM_LOG_INFO);
                debug_pop();
                return false;
            }
        }
        else if (is_numeric($id))
        {
            $this->_storage = new midgard_group();
            if (! $this->_storage->get_by_id($id))
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("Failed to retreive the group ID {$id}: " . mgd_errstr(), MIDCOM_LOG_INFO);
                debug_pop();
                return false;
            }
        }
        else if (   is_object($id)
                 && is_a($id, 'midgard_group'))
        {
            $this->_storage = $id;
        }
        else
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('Tried to load a midcom_core_group, but $id was of unknown type.', MIDCOM_LOG_ERROR);
            debug_print_r('Passed argument was:', $id);
            debug_pop();
            return false;
        }

        if ($this->_storage->official != '')
        {
            $this->name = $this->_storage->official;
        }
        else if ($this->_storage->name != '')
        {
            $this->name = $this->_storage->name;
        }
        else
        {
            $this->name = "Group #{$this->_storage->id}";
        }
        $this->id = "group:{$this->_storage->guid}";

        // Determine scope
        $parent = $this->get_parent_group();
        if (is_null($parent))
        {
            $this->scope = MIDCOM_PRIVILEGE_SCOPE_ROOTGROUP;
        }
        else
        {
            $this->scope = $parent->scope + 1;
        }

        return true;
    }
    
    /**
     * Retrieves a list of groups owned by this group.
     *
     * @return Array A list of midcom_core_group_midgard objects in which are owned by the current group, false on failure.
     */
    function list_subordinate_groups()
    {
        $qb = new midgard_query_builder('midgard_group');
        $qb->add_constraint('owner', '=', $this->_storage->id);
        $result = $qb->execute();
        return $result;
    }

    /**
     * Retrieves a list of users for which are a member in this group.
     *
     * @return Array A list of midcom_core_user objects in which are members of the current group, false on failure, indexed by their ID.
     */
    function list_members()
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        $qb = new midgard_query_builder('midgard_member');
        $qb->add_constraint('gid', '=', $this->_storage->id);
        $result = @$qb->execute();
        if (! $result)
        {
            $result = Array();
        }

        $return = Array();
        foreach ($result as $member)
        {
            $user = new midcom_core_user($member->uid);
            if (! $user)
            {
                debug_add("The membership record {$member->id} is invalid, the user {$member->uid} is unknown, skipping it.", MIDCOM_LOG_ERROR);
                debug_add('Last Midgard error was: ' . mgd_errstr());
                debug_print_r('Membership record was:', $member);
                continue;
            }
            $return[$user->id] = $user;
        }

        debug_pop();
        return $return;
    }

    /**
     * This method returns a list of all groups in which the
     * MidCOM user passed is a member.
     *
     * This function is always called statically.
     *
     * @param midcom_core_user $user The user that should be looked-up.
     * @return Array An array of member groups or false on failure, indexed by their ID.
     */
    function list_memberships($user)
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        $qb = new midgard_query_builder('midgard_member');
        $qb->add_constraint('uid', '=', $user->_storage->id);
        $result = @$qb->execute();
        if (empty($result))
        {
            return $result;
        }
        
        $return = Array();
        foreach ($result as $member)
        {
            $group = new midcom_core_group_midgard($member->gid);
            if (! $group)
            {
                debug_add("The membership record {$member->id} is invalid, the group {$member->gid} is unknown, skipping it.", MIDCOM_LOG_ERROR);
                debug_add('Last Midgard error was: ' . mgd_errstr());
                debug_print_r('Membership record was:', $member);
                continue;
            }
            $return[$group->id] = $group;
        }

        debug_pop();
        return $return;
    }

    /**
     * Returns the parent group.
     *
     * You must adhere the reference that is returned, otherwise the internal caching
     * and runtime state strategy will fail.
     *
     * @return midcom_core_group The parent group of the current group or NULL if there is none.
     */
    function get_parent_group()
    {
        if (is_null($this->_cached_parent_group))
        {
            debug_push_class(__CLASS__, __FUNCTION__);

            if ($this->_storage->owner == 0)
            {
                debug_pop();
                return null;
            }

            if ($this->_storage->id == $this->_storage->owner)
            {
                debug_add('WARNING: A group was its own parent, this is critical as it will result in an infinite loop. See debug log for more info.',
                    MIDCOM_LOG_CRIT);
                debug_print_r('Current group', $this);
                debug_pop();
                return null;
            }

            $parent = new midgard_group();
            $parent->get_by_id($this->_storage->owner);

            if (! $parent->id)
            {
                debug_add("Could not load Group ID {$this->_storage->owner} from the database, aborting, this should not happen. See the debug level log for details. ("
                    . mgd_errstr() . ')',
                    MIDCOM_LOG_ERROR);
                debug_print_r('Group that we started from is:', $this->_storage);
                debug_pop();
                return null;
            }

            $this->_cached_parent_group =& $_MIDCOM->auth->get_group($parent);

            debug_pop();
        }
        return $this->_cached_parent_group;
    }

}



?>