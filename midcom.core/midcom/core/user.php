<?php
/**
 * @package midcom
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id:user.php 3765 2006-07-31 08:51:39 +0000 (Mon, 31 Jul 2006) tarjei $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * System user, basically encapsulates a MidgardPerson. It does not provide a way to
 * manipulate accounts, instead, this is an abstraction used in the ACL system.
 *
 * You must not create these objects directly. Instead, use the factory method
 * $_MIDCOM->auth->get_user($id), where $id is any valid constructor argument
 * for a midcom_baseclasses_database_person.
 *
 * @package midcom
 */
class midcom_core_user extends midcom_baseclasses_core_object
{
    /**
     * The storage object on which we are based.
     *
     * This is no MidCOM DBA layer object since it must not do any Access Control
     * for the internal system to work. The instance may not be accessed from the outside.
     *
     * Access to this member is restricted to the ACL user/group core. In case you
     * need a real Storage object for this group, call get_storage() instead.
     *
     * @var midgard_person
     * @access protected
     */
    var $_storage = null;

    /**
     * Username of the current user, it is to be considered read-only.
     *
     * @var string
     * @access public
     */
    var $username = null;

    /**
     * The full name of the current user.
     *
     * Built from the first and last name of the user record, falling back
     * to the username if both are unset. It is to be considered read-only.
     *
     * @var string
     * @access public
     */
    var $name = null;

    /**
     * The full reversed name of the current user.
     *
     * Built from the first and last name of the user record, falling back
     * to the username if both are unset. It is to be considered read-only.
     *
     * @var string
     * @access public
     */
    var $rname = null;

    /**
     * Lists all groups in which a user is a member, both directly and indirectly.
     *
     * There is no hierarchy, just a plain listing of midcom_core_group objects.
     * It is to be considered read-only.
     *
     * The array is indexed by the group identifiers, which are used to perform
     * in_group checks.
     *
     * It is loaded on demand.
     *
     * @var Array
     * @access private
     */
    var $_all_groups = null;

    /**
     * Lists all groups in which a user is an immediate member.
     *
     * It is to be considered read-only.
     *
     * The array is indexed by the group identifiers, which are used to perform
     * in_group checks.
     *
     * It is loaded on demand.
     *
     * @var Array
     * @access private
     */
    var $_direct_groups = null;

    /**
     * This array lists all groups the user is a member in ordered by their inheritance
     * chain.
     *
     * The first element in the array is always the top-level group, while the last
     * one is always a member of $_direct_groups. This is therefore a multilevel array and is
     * indexed by the direct group id's (midcom_core_group id's, not Midgard IDs!) of the
     * direct groups. The values are group identifiers as well, which can be resolved by either
     * get_group or using the all_groups listing.
     *
     * This member is populated with $_all_groups.
     *
     * @var Array
     * @access private
     */
    var $_inheritance_chains = null;

    /**
     * List of all privileges assigned to that user. It is to be considered read-only.
     *
     * Array keys are the privilege names, the values are the Privilege states (ALLOW/DENY).
     *
     * It is loaded on demand.
     *
     * @var Array
     * @access private
     */
    var $_privileges = null;

    /**
     * List of all privileges assigned to that user based on the class he is accessing. It is to
     * be considered read-only.
     *
     * This is a multi level array. It holds regular privilege name/state arrays indexed by the
     * name of the class (or subtype thereof) for which they should apply.
     *
     * It is loaded on demand.
     *
     * @var Array
     * @access private
     */
    var $_per_class_privileges = null;

    /**
     * The identification string used to internally identify the user uniquely
     * in the system.
     *
     * This is usually some kind of user:$guid string combination.
     *
     * @var string
     * @access public
     */
    var $id;

    /**
     * The GUID identifying this user, made directly available for easier linking.
     *
     * @var string
     * @access public
     */
    var $guid;

    /**
     * The scope value, which must be set during the _load callback, indicates the
     * "depth" of the group in the inheritance tree.
     *
     * This is used during privilege merging in the content privilege code, which
     * needs a way to determine the proper ordering. All persons currently
     * use the magic value -1.
     *
     * The variable is considered to be read-only.
     *
     * @var integer
     * @access public
     */
    var $scope = MIDCOM_PRIVILEGE_SCOPE_USER;

    /**
     * The constructor retrieves the user identified by its name from the database and
     * prepares the object for operation.
     *
     * The class relies on the Midgard Framework to ensure the uniqueness of a user.
     *
     * The class is only intended to operate with users and groups, and should not be used
     * in normal operations regarding persons.
     *
     * @param mixed $id This is either a Midgard Person ID or GUID, a midcom_user ID or an already instantiated midgard_person.
     * @access protected
     */
    function __construct($id = null)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        parent::__construct();

        if (is_null($id))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'The class midcom_user is not default constructible.');
            // This will exit.
        }

        if (! $this->_load($id))
        {
            debug_pop();
            $x =& $this;
            $x = false;
            return false;
        }
        debug_pop();
    }

    /**
     * Helper function that will look up a user in the Midgard Database and
     * assign the object to the $storage member.
     *
     * @param mixed $id This is either a Midgard Person ID or GUID, a midcom_user ID or an already instantiated midgard_person.
     * @return boolean Indicating success.
     */
    function _load($id)
    {
        if (   is_string($id)
            && substr($id, 0, 5) == 'user:')
        {
            $this->_storage = new midgard_person();
            $id = substr($id, 5);
            if (! $this->_storage->get_by_guid($id))
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("Failed to retrieve the person GUID {$id}: " . mgd_errstr(), MIDCOM_LOG_INFO);
                debug_pop();
                return false;
            }
        }
        else if (mgd_is_guid($id))
        {
            $this->_storage = new midgard_person();
            try
            {
                $this->_storage->get_by_guid($id);
            }
            catch (midgard_error_exception $e)
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("Failed to retrieve the person GUID {$id}: " . $e->getMessage(), MIDCOM_LOG_INFO);
                debug_pop();
                return false;
            }
        }
        else if (is_numeric($id))
        {
            $this->_storage = new midgard_person();

            try {

                $this->_storage->get_by_id($id);

            } catch (midgard_error_exception $e) {

                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("Failed to retrieve the person ID {$id}: " . $e->getMessage(), MIDCOM_LOG_INFO);
                debug_pop();
                return false;
            }
        }
        else if (   is_object($id)
                 && (   is_a($id, 'midgard_person')
                     || is_a($id, 'org_openpsa_person')))
        {
            $this->_storage = $id;
        }
        else
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('Tried to load a midcom_core_user, but $id was of unknown type.', MIDCOM_LOG_ERROR);
            debug_print_r('Passed argument was:', $id);
            debug_pop();
            return false;
        }

        $this->username = $this->_storage->username;
        $this->name = trim("{$this->_storage->firstname} {$this->_storage->lastname}");
        if (empty($this->name))
        {
            $this->name = $this->username;
        }

        $this->rname = trim("{$this->_storage->lastname}, {$this->_storage->firstname}");
        if ($this->name == '')
        {
            $this->name = $this->username;
            $this->rname = $this->username;
        }
        $this->id = "user:{$this->_storage->guid}";
        $this->guid = $this->_storage->guid;

        return true;
    }

    /**
     * Retrieves a list of groups for which this user is an immediate member.
     *
     * @return Array A list of midcom_core_group objects in which the current user is a member, or false on failure.
     */
    function list_memberships()
    {
        if (is_null($this->_direct_groups))
        {
            $this->_load_direct_groups();
        }
        return $this->_direct_groups;
    }

    /**
     * Retrieves a list of groups for which this user is a member, both directly and indirectly.
     *
     * There is no hierarchy in the resultset, it is just a plain listing.
     *
     * @return Array A list of midcom_core_group objects in which the current user is a member, or false on failure.
     */
    function list_all_memberships()
    {
        if (is_null($this->_all_groups))
        {
            $this->_load_all_groups();
        }
        return $this->_all_groups;
    }

    /**
     * Return a list of privileges assigned directly to the user.
     *
     * @return Array A list of midcom_core_privilege records.
     */
    function _get_privileges()
    {
        return midcom_core_privilege::get_self_privileges($this->guid);
    }

    /**
     * Returns the complete privilege set assigned to this user, taking all
     * parent groups into account.
     *
     * @return Array Array keys are the privilege names, the values are the Privilege states (ALLOW/DENY).
     */
    function get_privileges()
    {
        if (is_null($this->_privileges))
        {
            $this->_load_privileges();
        }
        return $this->_privileges;
    }

    /**
     * Returns the specific per class global privilege set assigned to this user, taking all
     * parent groups into account.
     *
     * If the class specified is unknown, an empty array is returned.
     *
     * @param object &$object The object for which we should look up privileges for. This is passed by-reference.
     * @return Array Array keys are the privilege names, the values are the Privilege states (ALLOW/DENY).
     */
    function get_per_class_privileges(&$object)
    {
        if (is_null($this->_per_class_privileges))
        {
            $this->_load_privileges();
        }
        $result = Array();
        foreach ($this->_per_class_privileges as $class => $privileges)
        {
            if (is_a($object, $class))
            {
                $result = array_merge($result, $privileges);
            }
        }
        return $result;
    }

    /**
     * Loads all groups the user is a direct member and assigns them to the _direct_groups member.
     *
     * @access protected
     */
    function _load_direct_groups()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_pop();

        $this->_direct_groups = array_merge
        (
            midcom_core_group_midgard::list_memberships($this),
            midcom_core_group_virtual::list_memberships($this)
        );
    }

    /**
     * Loads the complete group hierarchy the user is a member in.
     *
     * @access protected
     */
    function _load_all_groups()
    {
        if (is_null($this->_direct_groups))
        {
            $this->_load_direct_groups();
        }

        $this->_all_groups = Array();
        $this->_inheritance_chains = Array();

        foreach ($this->_direct_groups as $id => $group)
        {
            $this->_all_groups[$id] =& $this->_direct_groups[$id];
            $inheritance_chain = Array($group->id);
            /**
             * FIXME: Parent group members should inherit permissions from
             * the child groups, not the other way around!!!
            $parent = $group->get_parent_group();
            while (! is_null($parent))
            {
                $this->_all_groups[$parent->id] = $parent;
                array_unshift($inheritance_chain, $parent->id);

                $parent = $parent->get_parent_group();
            }
            */
            $this->_inheritance_chains[$id] = $inheritance_chain;
        }
    }

    /**
     * Load the privileges from the database.
     *
     * This uses the inheritance chains
     * loaded by _load_all_groups().
     *
     * @access private
     */
    function _load_privileges()
    {
        static $cache = Array();

        if (! array_key_exists($this->id, $cache))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Loading privileges for user {$this->name} ({$this->id})");
            debug_pop();

            if (is_null($this->_all_groups))
            {
                $this->_load_all_groups();
            }

            $this->_privileges = Array();
            $this->_per_class_privileges = Array();

            foreach ($this->_inheritance_chains as $direct_group_id => $inheritance_chain)
            {
                // Compute permissions based on this group line.
                foreach ($inheritance_chain as $group_id)
                {
                    $this->_merge_privileges($this->_all_groups[$group_id]->get_privileges());
                }
            }

            // Finally, apply our own privilege set to the one we got from the group
            $this->_merge_privileges($this->_get_privileges());
            $cache[$this->id]['direct'] = $this->_privileges;
            $cache[$this->id]['class'] = $this->_per_class_privileges;
        }
        else
        {
            $this->_privileges = $cache[$this->id]['direct'];
            $this->_per_class_privileges = $cache[$this->id]['class'];
        }
    }

    /**
     * Merge privileges helper.
     *
     * It loads the privileges of the given object and
     * loads all "SELF" assignee privileges into the class.
     *
     * @param Array $privileges A list of privilege records, see mRFC 15 for details.
     */
    function _merge_privileges($privileges)
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        debug_print_r("Got these Privileges:", $privileges);

        foreach ($privileges as $privilege)
        {
            debug_print_r("Checking this privilege:", $privilege);

            if ($privilege->classname != '')
            {
                switch ($privilege->value)
                {
                    case MIDCOM_PRIVILEGE_ALLOW:
                        debug_add("Grant {$privilege->name} for class hierarchy {$privilege->classname}.");
                        $this->_per_class_privileges[$privilege->classname][$privilege->name] = $privilege->value;
                        break;

                    case MIDCOM_PRIVILEGE_DENY:
                        debug_add("Revoke {$privilege->name} for class hierarchy {$privilege->classname}.");
                        $this->_per_class_privileges[$privilege->classname][$privilege->name] = $privilege->value;
                        break;

                    default:
                        debug_add("Inheriting {$privilege->name} for class hierarchy {$privilege->classname}.");
                        break;
                }
            }
            else
            {
                switch ($privilege->value)
                {
                    case MIDCOM_PRIVILEGE_ALLOW:
                        debug_add("Grant {$privilege->name}.");
                        $this->_privileges[$privilege->name] = $privilege->value;
                        break;

                    case MIDCOM_PRIVILEGE_DENY:
                        debug_add("Revoke {$privilege->name}.");
                        $this->_privileges[$privilege->name] = $privilege->value;
                        break;

                    default:
                        debug_add("Inheriting {$privilege->name}.");
                        break;
                }
            }
        }

        debug_pop();
    }

    /**
     * Checks whether a user is a member of the given group.
     *
     * The group argument may be one of the following (checked in this order of precedence):
     *
     * 1. A valid group object (subclass of midcom_core_group)
     * 2. A group or vgroup string identifier, matching the regex ^v?group:
     * 3. A valid midcom group name
     *
     * @param mixed $group Group to check against, this can be either a midcom_core_group object or a group string identifier.
     * @return boolean Indicating membership state.
     */
    function is_in_group($group)
    {
        if (is_null($this->_all_groups))
        {
            $this->_load_all_groups();
        }

        // Process
        if (is_a($group, 'midcom_core_group'))
        {
            return array_key_exists($group->id, $this->_all_groups);
        }
        else if (preg_match('/^v?group:/', $group))
        {
            return array_key_exists($group, $this->_all_groups);
        }
        else
        {
            // We scan through our groups looking for a midgard group with the right name
            foreach ($this->_all_groups as $id => $group_object)
            {
                if (   is_a($group_object, 'midcom_core_group_midgard')
                    && $group_object->_storage->name == $group)
                {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * This function will return a MidCOM DBA level storage object for the current user.
     * Be aware that depending on ACL information, the retrieval of the user may fail.
     *
     * @return midcom_db_person The user which is associated with this record or false if the object cannot be accessed.
     */
    function get_storage()
    {
        return new midcom_db_person($this->_storage);
    }

    /**
     * Updates the password to the new one. This requires update privileges on the
     * person storage object.
     *
     * It changes the internal storage object. If you want to make further modifications
     * to the account, you <i>must call get_storage</i> again after the execution of this
     * call, otherwise your subsequent updates will overwrite the password again.
     *
     * @param string $new The new clear text password to set.
     * @param boolean $crypted Set this to true if you want to store a crypted password (the default),
     *     false if you want to use a plain text password.
     * @return boolean Indicating success.
     */
    function update_password($new, $crypted = true)
    {
        $person = $this->get_storage();
        if (   ! $person
            || ! $_MIDCOM->auth->can_do('midgard:update', $person))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('Cannot update password, insufficient privileges.', MIDCOM_LOG_INFO);
            debug_pop();
            return false;
        }

        if ($crypted)
        {
            $salt = chr(rand(32,125)).chr(rand(32,125));
            $password = crypt($new, $salt);
        }
        else
        {
            $password = "**{$new}";
        }

        $person->password = $password;
        if (! $person->update())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('Cannot update password, failed to update the record: ' . mgd_errstr(), MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }

        $_MIDCOM->auth->sessionmgr->_update_user_password($this, $new);

        return true;
    }

    /**
     * Updates the username to the new one. This requires update privileges on the
     * person storage object.
     *
     * It changes the internal storage object. If you want to make further modifications
     * to the account, you <i>must call get_storage</i> again after the execution of this
     * call, otherwise your subsequent updates will overwrite the password again.
     *
     * @param string $new The new username to set.
     * @return boolean Indicating success.
     */
    function update_username($new)
    {
        $person = $this->get_storage();
        if (   ! $person
            || ! $_MIDCOM->auth->can_do('midgard:update', $person)
            || ! $_MIDCOM->auth->can_do('midgard:parameters', $person))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('Cannot update username, insufficient privileges.', MIDCOM_LOG_INFO);
            debug_pop();
            return false;
        }

        // First, update the username and login session, keep track of the old
        // name for a later update of the username history.
        $old_name = $person->username;
        $person->username = $new;
        if (! $person->update())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('Cannot update username, failed to update the record: ' . mgd_errstr(), MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }

        $_MIDCOM->auth->sessionmgr->_update_user_username($this, $new);

        // Update the history, ignore failed unserialization, this means that no
        // history is present yet. Thus we should be fine as we handle that case
        // with a fresh history.
        $history = @unserialize($person->get_parameter('midcom', 'username_history'));
        if (! $history)
        {
            $history = Array();
        }
        $history[time()] = Array('old' => $old_name, 'new' => $new);
        $person->set_parameter('midcom', 'username_history', serialize($history));

        return true;
    }

    /**
     * This is a shortcut for the method midcom_services_auth_sessionmgr::is_user_online().
     * The documentation at that function takes priority over the copy here.
     *
     * Checks the online state of the user. You require the privilege midcom:isonline
     * for the storage object you are going to check. The privilege is not granted by default,
     * to allow users full control over their privacy.
     *
     * 'unknown' is returned in cases where you have insufficient permissions.
     *
     * @return string One of 'online', 'offline' or 'unknown', indicating the current online
     *     state.
     * @see midcom_services_auth_sessionmgr::is_user_online()
     */
    function is_online()
    {
        return $_MIDCOM->auth->sessionmgr->is_user_online($this);
    }

    /**
     * Returns the last login of the given user.
     *
     * You require the privilege midcom:isonline for the storage object you are
     * going to check. The privilege is not granted by default, to allow users
     * full control over their privacy.
     *
     * null is returned in cases where you have insufficient permissions.
     *
     * @return mixed The time of the last login, or null in case of insufficient privileges. If
     *     there is no known last login time, numeric zero is returned.
     */
    function get_last_login()
    {
        if (! $_MIDCOM->auth->can_do('midcom:isonline', $this->_storage))
        {
            return null;
        }

        return (int) $this->_storage->parameter('midcom', 'last_login');
    }


    /**
     * Returns the first login time of the user, if available.
     *
     * In contrast to get_last_login and is_online this query does not require
     * the isonline privilege, as it is usually used to determine the "age"
     * of a user account in a community.
     *
     * @return int The time of the first login, or zero in case of users which have never
     *     logged in.
     */
    function get_first_login()
    {
        return (int) $this->_storage->parameter('midcom', 'first_login');
    }

    /**
     * Deletes the current user account.
     *
     * This will cleanup all information associated with
     * the user that is managed by the core (like login sessions and privilege records).
     *
     * This call requires the delete privilege on the storage object, this is enforced using
     * require_do.
     *
     * @return boolean Indicating success.
     */
    function delete()
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        $person = $this->get_storage();
        if (! $person)
        {
            debug_add('Failed to delete the storage object, last Midgard error was: ' . mgd_errstr, MIDCOM_LOG_INFO);
            debug_pop();
            return false;
        }

        $_MIDCOM->auth->require_do('midgard:delete', $person);

        if (! $person->delete())
        {
            debug_add('Failed to delete the storage object, last Midgard error was: ' . mgd_errstr, MIDCOM_LOG_INFO);
            debug_pop();
            return false;
        }

        // Delete login sessions
        $qb = new midgard_query_builder('midcom_core_login_session_db');
        $qb->add_constraint('userid', '=', $this->id);
        $result = @$qb->execute();
        if ($result)
        {
            foreach ($result as $entry)
            {
                debug_add("Deleting login session ID {$entry->id} for user {$entry->username} with timestamp {$entry->timestamp}");
                $entry->delete();
            }
        }

        // Delete all ACL records which have the user as assignee
        $qb = new midgard_query_builder('midcom_core_privilege_db');
        $qb->add_constraint('assignee', '=', $this->id);
        $result = @$qb->execute();
        if ($result)
        {
            foreach ($result as $entry)
            {
                debug_add("Deleting privilege {$entry->name} ID {$entry->id} on {$entry->objectguid}");
                $entry->delete();
            }
        }

        debug_pop();
        return true;
    }

}



?>