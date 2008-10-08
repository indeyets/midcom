<?php
/**
 * @package midcom
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id:privilege.php 3765 2006-07-31 08:51:39 +0000 (Mon, 31 Jul 2006) tarjei $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Privilege class, used to interact with the privilege system. It encapsulates the actual
 * Database Level Object. As usual with MidCOM DBA, you <i>must never access the DB layer
 * object.</i>
 *
 * The main area of expertise of this class is privilege IO (loading and storing), their
 * validation and privilege merging.
 *
 * It is important to understand that you must never load privilege records directly, or
 * access them by their IDs. Instead, use the DBA level interface functions to locate
 * existing privilege sets. The only time where you use this class directly is when
 * creating new privilege, using the default constructor of this class (although the
 * create_new_privilege_object DBA member methods are the preferred way of doing this).
 *
 * <b>Caching:</b>
 *
 * This class uses the memcache cache module to speed up ACL accesses. It caches the ACL
 * objects retrieved from the database, not any merged privilege set (at this time, that is).
 * This should speed up regular operations quite a bit (along with the parent guid cache,
 * which is a second important key).
 *
 * <b>Developer's Note:</b>
 *
 * Originally, Privileges were represented by simple Arrays. While they should have been
 * full objects powered by MgdSchema from the beginning, I was not used to this possibility
 * from the beginning and fell into the old habit of parametrizing everything.
 *
 * For compatibility reasons with code of the OpenPSA 2 beta, this class still provides a
 * way to gain one of those original arrays. This functionality is deprecated already, and will
 * be removed before the final 2.6 release. It has been done to ease the immediate transition.
 *
 * Essentially, this class will supersede the original privileges_io.php helpers.
 *
 * Torben (2005-07-29)
 *
 * @package midcom
 */
class midcom_core_privilege extends midcom_core_privilege_db
{
    protected $__guid = '';
    protected $__id = 0;

    /**
     * The GUID of the content object this privilege is valid for.
     *
     * Note, that you should operate on this using get_object() and set_object(), they
     * operate cached and are more efficient.
     *
     * Direct changes to this variable will obviously not invalidate the object cached.
     *
     * @var string
     */
    var $objectguid = '';

    /**
     * The name of the privilege.
     *
     * @var string
     */
    var $name = '';

    /**
     * The Assignee identifier.
     *
     * @var string
     */
    var $assignee = '';

    /**
     * The class name to limit SELF privileges to.
     *
     * @var string
     */
    var $classname = '';

    /**
     * The privilege value.
     *
     * @var int
     */
    var $value = MIDCOM_PRIVILEGE_INHERIT;

    /** @ignore */
    var $__table__ = 'midcom_core_privilege';

    /**
     * Cached content object, based on $objectguid.
     *
     * @access private
     * @var object
     */
    var $_cached_object = null;

    /**
     * The Default constructor creates an empty privilege, if you specify
     * another privilege object in the constructor, a copy is constructed.
     *
     * @param midcom_core_privilege_db $src Object to copy from.
     */
    function __construct($src = null)
    {
        if (! is_null($src))
        {
            // Explicity manual listing for performance reasons.
            $this->__guid = $src['guid'];
            $this->__id = $src['id'];
            $this->objectguid = $src['objectguid'];
            $this->name = $src['name'];
            $this->assignee = $src['assignee'];
            $this->classname = $src['classname'];
            $this->value = $src['value'];
        }
    }

    /**
     * A copy of the object referenced by the guid value of this privilege.
     *
     * @return object The DBA object to which this privileges is assigned or false on failure (f.x. missing access permissions).
     */
    function get_object()
    {
        if (is_null($this->_cached_object))
        {
            $this->_cached_object = $_MIDCOM->dbfactory->get_object_by_guid($this->objectguid);
            if (!is_object($this->_cached_object))
            {
                return false;
            }
        }
        return $this->_cached_object;
    }

    /**
     * Set a privilege to a given content object.
     *
     * @param object $object A MidCOM DBA level object to which this privilege should be assigned to.
     */
    function set_object($object)
    {
        $this->_cached_object = $object;
        $this->objectguid = $object->guid;
    }

    /**
     * If the assignee has an object representation (at this time, only users and groups have), this call
     * will return a reference to the assignee object held by the authentication service.
     *
     * Use is_magic_assignee to determine if you have an assignee object.
     *
     * @see midcom_services_auth::get_assignee()
     * @return mixed A midcom_core_user or midcom_core_group object reference as returned by the auth service,
     *     returns false on failure.
     */
    function & get_assignee()
    {
        if ($this->is_magic_assignee())
        {
            return false;
        }
        return $_MIDCOM->auth->get_assignee($this->assignee);
    }

    /**
     * Checks whether the current assignee is a magic assignee or an object identifier.
     *
     * @return boolean True, if it is a magic assignee, false otherwise.
     */
    function is_magic_assignee()
    {
        return ($this->_is_magic_assignee($this->assignee));
    }

    /**
     * Internal helper, encapsulating the check whether an assignee string is a known
     * magic assignee.
     *
     * @return boolean True, if it is a magic assignee, false otherwise.
     */
    function _is_magic_assignee($assignee)
    {
        switch($assignee)
        {
            case 'SELF':
            case 'EVERYONE':
            case 'USERS':
            case 'ANONYMOUS':
            case 'OWNER':
                return true;

            default:
                return false;
        }
    }

    /**
     * This call sets the assignee member string to the correct value to represent the
     * object passed, in general, this resolves users and groups to their strings and
     * leaves magic assignees intact.
     *
     * Possible argument types:
     *
     * - Any one of the magic assignees SELF, EVERYONE, ANONYMOUS, USERS.
     * - Any midcom_core_user or midcom_core_group object or subtype thereof.
     * - Any string identifier which can be resolved using midcom_services_auth::get_assignee().
     *
     * @param mixed &$assignee An assignee representation as outlined above.
     * @return boolean indicating success.
     */
    function set_assignee(&$assignee)
    {
        if (   is_a($assignee, 'midcom_core_user')
            || is_a($assignee, 'midcom_core_group'))
        {
            $this->assignee = $assignee->id;
        }
        else if (is_string($assignee))
        {
            if ($this->_is_magic_assignee($assignee))
            {
                $this->assignee = $assignee;
            }
            else
            {
                $tmp =& $_MIDCOM->auth->get_assignee($assignee);
                if (! $tmp)
                {
                    debug_push_class(__CLASS__, __FUNCTION__);
                    debug_add("Could not resolve the assignee string '{$assignee}', see above for more information.", MIDCOM_LOG_INFO);
                    debug_pop();
                    return false;
                }
                $this->assignee = $tmp->id;
            }
        }
        else
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('Unknown type passed, aborting.', MIDCOM_LOG_INFO);
            debug_print_r('Argument was:', $assignee);
            debug_pop();
            return false;
        }

        return true;
    }

    /**
     * This call validates the privilege for correctness of all set options. This includes:
     *
     * - A check against the list of registered privileges to ensure the existence of the
     *   privilege itself.
     * - A check for a valid and existing assignee, this includes a class existence check for classname restrictions
     *   for SELF privileges.
     * - A check for an existing content object GUID (this implicitly checks for midgard:read as well).
     * - Enough privileges of the current user to update the object's privileges (the user
     *   must have midgard:update and midgard:privileges for this to succeed).
     * - A valid privilege value.
     */
    function validate()
    {
        // 1. Privilege name
        if (! $_MIDCOM->auth->privilege_exists($this->name))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("The privilege name '{$this->name}' is unknown to the system. Perhaps the corresponding component is not loaded?",
                MIDCOM_LOG_INFO);
            debug_print_r('Available privileges:', $_MIDCOM->auth->_default_privileges);
            debug_pop();
            return false;
        }

        // 2. Assignee
        if (   ! $this->is_magic_assignee()
            && ! $this->get_assignee())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("The assignee identifier '{$this->assignee}' is invalid.", MIDCOM_LOG_INFO);
            debug_pop();
            return false;
        }

        if (   $this->assignee == 'SELF'
            && $this->classname != ''
            && ! class_exists($this->classname))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("The class '{$this->classname}' is not loaded, the SELF magic assignee with class restriction is invalid therefore.", MIDCOM_LOG_INFO);
            debug_pop();
            return false;
        }

        if (   $this->assignee != 'SELF'
            && $this->classname != '')
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("The classname parameter was specified without having the magic assignee SELF set, this is invalid.", MIDCOM_LOG_INFO);
            debug_pop();
            return false;
        }

        // Prevent owner assignments to owners
        if (   $this->assignee == 'OWNER'
            && $this->name == 'midgard:owner')
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Tried to assign midgard:owner to the OWNER magic assignee, this is invalid.", MIDCOM_LOG_INFO);
            debug_pop();
            return false;
        }

        $object = $this->get_object();
        if (!is_object($object))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Could not retrieve the content object with the GUID '{$this->objectguid}'; see the debug level log for more information.",
                MIDCOM_LOG_INFO);
            debug_pop();
            return false;
        }
        if (   ! $_MIDCOM->auth->can_do('midgard:update', $object)
            || ! $_MIDCOM->auth->can_do('midgard:privileges', $object))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Insufficient privileges on the content object with the GUID '{$this->__guid}', midgard:update and midgard:privileges required.",
                MIDCOM_LOG_INFO);
            debug_pop();
            return false;
        }

        switch ($this->value)
        {
            case MIDCOM_PRIVILEGE_ALLOW:
            case MIDCOM_PRIVILEGE_DENY:
            case MIDCOM_PRIVILEGE_INHERIT:
                break;

            default:
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("Invalid privilege value '{$this->value}'.", MIDCOM_LOG_INFO);
                debug_pop();
                return false;
        }

        return true;
    }

    /**
     * This is a static helper function which lists all content privileges
     * assigned to a given object. Essentially, this will exclude all SELF style assignees.
     *
     * This function is for use in the authentication framework only and may only
     * be called statically.
     *
     * @access protected
     * @param object $the_object A reference to the object or GUID to query.
     * @return Array A list of midcom_core_privilege instances.
     * @static
     */
    function get_content_privileges($guid)
    {
        $all_privileges = midcom_core_privilege::get_all_privileges($guid);

        $return = Array();
        foreach ($all_privileges as $privilege)
        {
            if ($privilege->assignee != 'SELF')
            {
                $return[] = $privilege;
            }
        }

        return $return;
    }

    /**
     * This is a static helper function which lists all privileges assigned
     * directly to a user or group. These are all SELF privileges.
     *
     * This function is for use in the authentication framework only and may only
     * be called statically.
     *
     * @access protected
     * @param object $the_object A reference to the object or GUID to query.
     * @return Array A list of midcom_core_privilege instances.
     * @static
     */
    function get_self_privileges($guid)
    {
        $all_privileges = midcom_core_privilege::get_all_privileges($guid);

        $return = Array();
        foreach ($all_privileges as $privilege)
        {
            if ($privilege->assignee == 'SELF')
            {
                $return[] = $privilege;
            }
        }

        return $return;
    }

    /**
     * This is a static helper function which lists all privileges assigned
     * an object unfiltered.
     *
     * This function is for use in the authentication framework only and may only
     * be called statically.
     *
     * @access protected
     * @param string GUID the GUID of the object for which we should look up privileges.
     * @return Array A list of midcom_core_privilege instances.
     * @static
     */
    function get_all_privileges($guid)
    {
        static $cache = Array();

        if (array_key_exists($guid, $cache))
        {
            $return = $cache[$guid];
        }
        else
        {
            // FIXME: Re-enable once unserialize works again with MgdSchema objects
            $return = $_MIDCOM->cache->memcache->get('ACL', $guid);
            //$return = null;
            if (! is_array($return))
            {
                $return = midcom_core_privilege::_query_all_privileges($guid);
                $_MIDCOM->cache->memcache->put('ACL', $guid, $return);
            }

            $cache[$guid] = $return;
        }

        return $return;
    }

    /**
     * This is an internal helper function, which may only be called statically.
     *
     * It is used by get_all_privileges in case that there is no cache hit. It will query the
     * database and construct all necessary objects out of it.
     *
     * @access protected
     * @param string $guid The GUID of the object for which to query ACL data.
     * @return Array A list of midcom_core_privilege instances.
     * @static
     */
    function _query_all_privileges($guid)
    {
        $result = array();

        $mc = new midgard_collector('midcom_core_privilege_db', 'objectguid', $guid);
        $mc->add_constraint('value', '<>', MIDCOM_PRIVILEGE_INHERIT);
        //FIXME:
        $mc->set_key_property('guid');
        $mc->add_value_property('id');
        $mc->add_value_property('name');
        $mc->add_value_property('assignee');
        $mc->add_value_property('classname');
        $mc->add_value_property('value');
        $mc->execute();
        $privileges = $mc->list_keys();

        if (!$privileges)
        {
            if (mgd_errstr() != 'MGD_ERR_OK')
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("Failed to retrieve all privileges for the Object GUID {$guid}: " . mgd_errstr(), MIDCOM_LOG_INFO);
                debug_print_r('Result was:', $result);
                if (isset($php_errormsg))
                {
                    debug_add("Error message was: {$php_errormsg}", MIDCOM_LOG_ERROR);
                }
                debug_pop();
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                    'The query builder failed to execute, see the log file for more information.');
                // This will exit.
            }

            return $result;
        }

        foreach ($privileges as $privilege_guid => $value)
        {
            $privilege = $mc->get($privilege_guid);
            $privilege['objectguid'] = $guid;
            $privilege['guid'] = $privilege_guid;
            $return[] = new midcom_core_privilege($privilege);
        }

        //$mc->destroy();

        return $return;
    }

    /**
     * This is a static helper function which retrieves a single given privilege
     * at a content object, identified by the combination of assignee and privilege
     * name.
     *
     * This call will return an object even if the privilege is set to INHERITED at
     * the given object (i.e. does not exist) for consistency reasons. Errors are
     * thrown for example on database inconsistencies.
     *
     * This function is for use in the authentication framework only and may only
     * be called statically.
     *
     * @access protected
     * @param object &$object A reference to the object to query.
     * @param string $name The name of the privilege to query
     * @param string $assignee The identifier of the assignee to query.
     * @param string $classname The optional classname required only for class-limited SELF privileges.
     * @return midcom_core_privilege The privilege matching the constraints.
     * @static
     */
    function get_privilege(&$object, $name, $assignee, $classname = '')
    {
        $qb = new midgard_query_builder('midcom_core_privilege_db');
        $qb->add_constraint('objectguid', '=', $object->guid);
        $qb->add_constraint('name', '=', $name);
        $qb->add_constraint('assignee', '=', $assignee);
        $qb->add_constraint('classname', '=', $classname);
        $result = @$qb->execute();

        if (! $result)
        {
            $result = Array();
        }

        if (count($result) > 1)
        {
            $_MIDCOM->auth->request_sudo('midcom.core');
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('A DB inconsistency has been detected. There is more then one record for privilege specified. Deleting all excess records after the first one!',
                MIDCOM_LOG_ERROR);
            debug_print_r('Content Object:', $object);
            debug_add("Privilege {$name} for assignee {$assignee} with classname {$classname} was queried.", MIDCOM_LOG_INFO);
            debug_print_r('Resultset was:', $result);
            while (count($result) > 1)
            {
                $privilege = array_pop($result);
                $privilege->delete();
            }
            $_MIDCOM->auth->drop_sudo();
        }
        else if (count($result) == 0)
        {
            $privilege = new midcom_core_privilege();
            $privilege->set_object($object);
            $privilege->set_assignee($assignee);
            $privilege->name = $name;
            if (! is_null($classname))
            {
                $privilege->classname = $classname;
            }
            $privilege->value = MIDCOM_PRIVILEGE_INHERIT;
            return $privilege;
        }

        $priv = array
        (
            'guid' => $result[0]->guid,
            'id' => $result[0]->id,
            'objectguid' => $result[0]->objectguid,
            'name' => $result[0]->name,
            'assignee' => $result[0]->assignee,
            'classname' => $result[0]->classname,
            'value' => $result[0]->value,
        );

        return new midcom_core_privilege($priv);
    }

    /**
     * Collects all privileges applicable for a user over a content object.
     * It takes the complete content object tree (using the get_parent methods)
     * into account and merges the privileges according to mRFC 15. It will
     * only look at privileges that are applicable to the current user, with
     * user privileges taking precedence over group privileges.
     *
     * <i>Known Issue:</i> Currently, group privileges are merged in no particular
     * order, in reality they should at least take the group tree into account when
     * merging. They do take the "depth" into account correctly though when looking
     * at a single group chain.
     *
     * This function is for use in the authentication framework only and may only
     * be called statically.
     *
     * This function tries to operate on GUIDs whenever possible, to keep runtime up,
     * only in the case of nonpersistent objects (no valid GUID yet), it will revert to
     * regular object usage.
     *
     * @access private
     * @param mixed &$arg A reference to the GUID or the full object instance for which we should load privileges.
     * @param midcom_core_user $user The MidCOM user for which we should collect the privileges, null uses the currently authenticated user.
     * @return Array An array of privilege_name => privilege_value pairs valid for the given user.
     * @static
     */
    function collect_content_privileges(&$arg, $user = null)
    {
        static $cached_collected_privileges = array();

        if (is_null($user))
        {
            $user = $_MIDCOM->auth->user;
        }

        /*if ($user == 'EVERYONE')
        {
            $user = null;
        }*/

        if (mgd_is_guid($arg))
        {
            $object = null;
            $guid = $arg;

            if (array_key_exists($guid, $cached_collected_privileges))
            {
                return $cached_collected_privileges[$guid];
            }
        }
        else if (is_object($arg))
        {
            $object =& $arg;
            if (   isset($object->__guid)
                && mgd_is_guid($object->__guid)
                && !$object->guid)
            {
                $guid = $object->__guid;
            }
            elseif (mgd_is_guid($object->guid))
            {
                $guid = $object->guid;
            }
            else
            {
                $guid = null;
            }
        }

        if (is_null($guid))
        {
            return array();
        }

        // If we have a parent, we recurse.
        //
        // We look up the parent guid in internal sudo mode, as we could run into a fully fledged loop
        // otherwise. (get_parent_guid calling get_object_by_guid calling can_do ...)
        $previous_sudo = $_MIDCOM->auth->_internal_sudo;
        $_MIDCOM->auth->_internal_sudo = true;

        $parent_guid = null;
        // We need to be careful here in case we have non-persistent objects.
        if ($guid === null)
        {
            $tmp = $object->get_parent();
            if (   $tmp
                && $tmp->guid)
            {
                $parent_guid = $tmp->guid;
            }
        }
        else
        {
            if ($object)
            {
                $parent_class = $object->get_dba_parent_class();
                $parent_guid = $_MIDCOM->dbfactory->get_parent_guid($guid, get_class($object));
            }
            else
            {
                $parent_class = null;
                $parent_guid = $_MIDCOM->dbfactory->get_parent_guid($guid);
            }
        }

        $_MIDCOM->auth->_internal_sudo = $previous_sudo;

        if (   $parent_guid !== null
            && $parent_guid != $guid)
        {
            if ($parent_class !== null)
            {
                $parent_dummy_object = new $parent_class();
                $parent_dummy_object->__guid = $parent_guid;
                $base_privileges = midcom_core_privilege::collect_content_privileges($parent_dummy_object, $user);
            }
            else
            {
                $base_privileges = midcom_core_privilege::collect_content_privileges($parent_guid, $user);
            }
        }
        else
        {
            $base_privileges = Array();
        }

        // We need to be careful here again in case we have non-persistent objects.
        // This case is a bit different then the above one, though. Non-persistent
        // objects can't have any privileges assigned whatsoever, so we skip the call
        // entirely.
        if ($guid === null)
        {
            $object_privileges = Array();
        }
        else
        {
            $object_privileges = midcom_core_privilege::get_content_privileges($guid);
        }

        // Determine parent ownership
        $is_owner = false;
        $last_owner_scope = -1;
        if (   array_key_exists('midgard:owner', $base_privileges)
            && $base_privileges['midgard:owner'] == MIDCOM_PRIVILEGE_ALLOW)
        {
            $is_owner = true;
        }

        // This array holds values in the schema
        // $valid_privs[scope][priv_name] = priv_value
        // where scope is an integer determining the scope, with 0 being everyone and
        // larger values indicating smaller scopes (1 = top level group, 2 = its sub-group
        // etc. up to the magic value -1 which is equal to the user level privileges)
        // noting it this way is required to ensure proper scoping of several privileges
        // assigned to a single object.
        $valid_privileges = Array();
        $valid_privileges[MIDCOM_PRIVILEGE_SCOPE_OWNER] = $_MIDCOM->auth->get_owner_default_privileges();

        foreach ($object_privileges as $privilege)
        {
            // Check whether we need to take this privilege into account
            if (! midcom_core_privilege::_is_privilege_valid($privilege, $user))
            {
                continue;
            }

            // The privilege applies if we arrive here, so we merge it into the current collection
            // taking its scope into account.
            if ($privilege->assignee == 'EVERYONE')
            {
                $scope = MIDCOM_PRIVILEGE_SCOPE_EVERYONE;
            }
            else if ($privilege->assignee == 'USERS')
            {
                $scope = MIDCOM_PRIVILEGE_SCOPE_USERS;
            }
            else if ($privilege->assignee == 'ANONYMOUS')
            {
                $scope = MIDCOM_PRIVILEGE_SCOPE_ANONYMOUS;
            }
            else if ($privilege->assignee == 'OWNER')
            {
                $scope = MIDCOM_PRIVILEGE_SCOPE_OWNER;
            }
            else
            {
                $assignee =& $privilege->get_assignee();
                if (! $assignee)
                {
                    debug_push_class(__CLASS__, __FUNCTION__);
                    debug_print_r('Could not resolve the assignee of this privilege, skipping it:', $privilege);
                    debug_pop();
                    // Skip broken privileges.
                    continue;
                }
                $scope = $assignee->scope;
            }

            switch ($privilege->value)
            {
                case MIDCOM_PRIVILEGE_ALLOW:
                    $valid_privileges[$scope][$privilege->name] = $privilege->value;
                    if (   $privilege->name == 'midgard:owner'
                        && $scope > $last_owner_scope)
                    {
                        $is_owner = true;
                        $last_owner_scope = $scope;
                    }
                    break;

                case MIDCOM_PRIVILEGE_DENY:
                    $valid_privileges[$scope][$privilege->name] = $privilege->value;
                    if (   $privilege->name == 'midgard:owner'
                        && $scope > $last_owner_scope)
                    {
                        $is_owner = false;
                        $last_owner_scope = $scope;
                    }
                    break;

                default:
                    break;
            }
        }

        ksort($valid_privileges);
        // Process owner privileges
        if (! $is_owner)
        {
            // Drop the owner privileges from the merging chain again, we are not an owner
            unset ($valid_privileges[MIDCOM_PRIVILEGE_SCOPE_OWNER]);
        }

        $collected_privileges = Array();
        // Note, that this merging order does not take the group order into account, it just
        // follows the basic rule that the deeper the group is, the smaller is its scope.
        foreach ($valid_privileges as $scope => $privileges)
        {
            foreach ($privileges as $name => $value)
            {
                $collected_privileges[$name] = $value;
            }
        }

        $final_privileges = array_merge(
            $base_privileges,
            $collected_privileges
        );

        $cached_collected_privileges[$guid] = $final_privileges;

        return $final_privileges;
    }

    /**
     * Copy values of the midcom_core_privilege object to a Midgard-level object
     */
    function _copy_to_object(&$object)
    {
        $object->objectguid = $this->objectguid;
        $object->name = $this->name;
        $object->assignee = $this->assignee;
        $object->classname = $this->classname;
        $object->value = $this->value;
    }

    /**
     * Internal helper function, determines whether a given privilege applies for the given
     * user in content mode. This means, that all SELF privileges are skipped at this point,
     * EVERYONE privileges apply always, and all other privileges are checked against the
     * user.
     *
     * This function may be called statically.
     *
     * @param Array $privilege A valid privilege record as returned by parameter_to_privilege().
     * @param object $object The content object we're checking right now.
     * @param midcom_core_user $user The user in question or null for anonymous access.
     * @return boolean Indicating whether the privilege record applies for the user, or not.
     */
    function _is_privilege_valid($privilege, $user)
    {
        if (   is_null($user)
            || !$user
            || (   is_string($user)
                && $user == 'EVERYONE'))
        {
            if (   $privilege->assignee != 'EVERYONE'
                && $privilege->assignee != 'ANONYMOUS')
            {
                return false;
            }
        }
        else
        {
            if ($privilege->assignee == 'ANONYMOUS')
            {
                return false;
            }
            if (    strstr($privilege->assignee, 'user:') !== false
                && $privilege->assignee != $user->id)
            {
                return false;
            }
            if (strstr($privilege->assignee, 'group:') !== false)
            {
                if (! $user->is_in_group($privilege->assignee))
                {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * @ignore
     */
    function update()
    {
        $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
            'The function midcom_core_privilege::update may not be called directly, use ->store instead.');
        // This will exit.
    }

    /**
     * @ignore
     */
    function create()
    {
        $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
            'The function midcom_core_privilege::create may not be called directly, use ->store instead.');
        // This will exit.
    }

    /**
     * @ignore
     */
    function delete()
    {
        $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
            'The function midcom_core_privilege::delete may not be called directly, use ->set instead.');
        // This will exit.
    }

    /**
     * Store the privilege. This will validate it first and then either
     * update an existing privilege record, or create a new one, depending on the
     * DB state.
     *
     * @return boolean Indicating success.
     */
    function store()
    {

        if (! $this->validate())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('This privilege failed to validate, rejecting it, see the debug log for details.', MIDCOM_LOG_WARN);
            $this->_cached_object = null;
            debug_print_r('Privilege dump (w/o cached object):', $this);
            debug_pop();
            return false;
        }

        if ($this->value == MIDCOM_PRIVILEGE_INHERIT)
        {
            if (   $this->__guid
                || $this->__id)
            {
                // Already a persistent record, drop it.
                if (! $this->drop())
                {
                    return false;
                }
                $this->_invalidate_cache();
                return true;
            }
            else
            {
                // This is a temporary object only, try to load the real object first. If it is not found,
                // exit silently, as this is the desired final state.
                $object = $this->get_object();
                $privilege = $this->get_privilege($object, $this->name, $this->assignee, $this->classname);
                if (!empty($privilege->__guid))
                {
                    if (! $privilege->drop())
                    {
                        return false;
                    }
                    $this->_invalidate_cache();
                    return true;
                }
                else
                {
                    return true;
                }
            }
        }

        if (   $this->__guid
            || $this->__id)
        {
            if ($this->__guid)
            {
                $privilege = new midcom_core_privilege_db($this->__guid);
            }
            else
            {
                $privilege = new midcom_core_privilege_db();
                $privilege->get_by_id($this->__id);
            }
            $this->_copy_to_object($privilege);
            if (!$privilege->update())
            {
                return false;
            }
            $this->_invalidate_cache();
            return true;
        }
        else
        {
            $object = $this->get_object();
            $privilege = $this->get_privilege($object, $this->name, $this->assignee, $this->classname);
            if (!empty($privilege->__guid)
                || $privilege->__id != 0)
            {
                $privilege->value = $this->value;
                if (! $privilege->store())
                {
                    debug_push_class(__CLASS__, __FUNCTION__);
                    debug_add('Update of the existing privilege failed.', MIDCOM_LOG_WARN);
                    debug_pop();
                    return false;
                }
                $this->__guid = $privilege->__guid;
                $this->__id = $privilege->__id;
                $this->objectguid = $privilege->objectguid;
                $this->name = $privilege->name;
                $this->assignee = $privilege->assignee;
                $this->classname = $privilege->classname;
                $this->value = $privilege->value;

                $this->_invalidate_cache();

                debug_pop();
                return true;
            }
            else
            {
                $privilege = new midcom_core_privilege_db();
                $this->_copy_to_object($privilege);
                $result = $privilege->create();
                if ($result)
                {
                    $this->_invalidate_cache();
                }

                debug_pop();
                return $result;
            }
        }
    }

    /**
     * This is an internal helper called after all I/O operation which invalidates the memcache
     * accordingly.
     */
    function _invalidate_cache()
    {
        $_MIDCOM->cache->invalidate($this->objectguid);
    }

    /**
     * Drop the privilege. If we are a known DB record, we delete us, otherwise
     * we return silently.
     *
     * @return boolean Indicating success.
     */
    function drop()
    {
        if (   !$this->__guid
            && !$this->__id)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('We are not stored, ID and GUID are empty. Ignoring silently.');
            debug_pop();
            return true;
        }

        if (! $this->validate())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('This privilege failed to validate, rejecting to drop it, see the debug log for details.', MIDCOM_LOG_WARN);
            debug_print_r('Privilege dump:', $this);
            debug_pop();
            return false;
        }

        if ($this->__guid)
        {
            $privilege = new midcom_core_privilege_db($this->__guid);
        }
        else
        {
            // Unserialized objects have only ID in 8.09
            $privilege = new midcom_core_privilege_db();
            $privilege->get_by_id($this->__id);
        }

        if (!$privilege->delete())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('Failed to delete privilege record, aborting. Error: ' . mgd_errstr());
            debug_pop();
            return false;
        }
        if (method_exists($privilege, 'purge'))
        {
            $privilege->purge();
        }

        $this->_invalidate_cache();
        $this->value = MIDCOM_PRIVILEGE_INHERIT;

        return true;
    }

    /*
    function get_by_id() {}
    function get_by_guid() {}
    */

}


?>
