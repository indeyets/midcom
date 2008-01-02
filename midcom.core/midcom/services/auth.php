<?php
/**
 * @package midcom.services
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id:auth.php 3765 2006-07-31 08:51:39 +0000 (Mon, 31 Jul 2006) tarjei $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Main Authentication/Authorization service class, it provides means to authenticate
 * users and to check for permissions.
 *
 * Unless further qualified, "Midgard Content Objects" can be either pre-mgdschema
 * or mgdschema objects. The only necessary constraint is that a MidCOM base class
 * (midgard_baseclasses_database_*) must be available.
 *
 * This implementation is based on the general idea outlined in mRFC 15
 * ( http://www.midgard-project.org/development/mrfc/0015.html ),
 * MidCOM Authentication and Access Control service. <i>Developers Note:</i> Be aware that
 * the basic requirements for the ACL system undergone a major chance during the implementation,
 * as the DBA layer with full access control even for database I/O was added. The proposals
 * from the mRFC are largely outdated therefore. What is documented on the main MidCOM
 * documentation has to take priority obviously.
 *
 * <b>Privilege definition</b>
 *
 * Privileges are represented by the class midcom_core_privilege and basically consist
 * of three parts: Name, Assignee and Value:
 *
 * The privilege name is a unique identifier for the privilege. The mRFC 15 defines the
 * syntax to be $component:$name, where $component is either the name of the component
 * or one of 'midgard' or 'midcom' for core privileges. Valid privilege names are for
 * example 'net.nehmer.static:do_something' or 'midgard:update'.
 *
 * The assignee is the entity to which the privilege applies, this can be one of several
 * things, depending on where the privilege is taken into effect, I'll explain this below
 * in more detail:
 *
 * On content objects (generally every object in the system used during 'normal operation'):
 *
 * - A Midgard User encapsulated by a midcom_core_user object.
 * - A Midgard Group encapsulated by a midcom_core_group object or subtype thereof.
 * - The magic assignee 'EVERYONE', which applies the privilege to every user unconditionally,
 *   even to unauthenticated users.
 * - The magic assignee 'USERS', which applies to all authenticated users.
 * - The magic assignee 'ANONYMOUS, which applies to all unauthenticated users.
 * - The magic assignee 'OWNER', which applies for all object owners.
 *
 * On users and groups during authentication (when building the basic privilege set for the user,
 * which applies generally):
 *
 * - The magic string 'SELF', which denotes that the privilege is set for the user in general for
 *   every content object. SELF privileges may be restricted to a class by using the classname
 *   property available at both midcom_core_privilege and various DBA interface functions.
 *
 * The value is one of MIDCOM_PRIVILEGE_ALLOW or MIDCOM_PRIVILEGE_DENY, which either grants or
 * revokes a privilege. Be aware, that unsetting a privilege does not set it to MIDCOM_PRIVILEGE_DENY,
 * but clears the entry completely, which means that the privilege value inherited from the parents
 * is now in effect.
 *
 * <b>How are privileges read and merged</b>
 *
 * First, you have to understand, that there are actually three distinct sources where a privilege
 * comes from: The systemwide defaults, the currently authenticated user and the content object
 * which is being operated on. We'll look into this distinction first, before we get on to the order
 * in which they are merged.
 *
 * <i>Systemwide default privileges</i>
 *
 * This is analogous to the MidCOM default configuration, they are taken into account globally to each
 * and every check whether a privilege is granted. Whenever a privilege is defined, there is also a
 * default value (either ALLOW or DENY) assigned to it. They serve as a basis for all privilege sets
 * and ensure, that there is a value set for all privileges.
 *
 * These defaults are defined by the MidCOM core and the components respectively and are very restrictive,
 * basically granting read-only access to all non sensitive information.
 *
 * Currently, there is no way to influence these privileges unless you are a developer and writing new
 * components.
 *
 * <i>Class specific, systemwide default privileges (for magic assignees only)</i>
 *
 * Often you want to have a number of default privileges for certain classes in general. For regular
 * users/groups you can easily assign them to the corresponding users/groups, there is one special
 * case which cannot be covered there at this time: You cannot set defaults applicable for the magic
 * assignees EVERYONE, USERS and ANONYMOUS. This is normally only of interest for component authors,
 * which want to have some special privileges assigned for their objects, where the global defaults
 * do no longer suffice.
 *
 * These privileges are queried using a static callback of the DBA classes in question, see the following
 * example:
 *
 * <code>
 * function get_class_magic_default_privileges()
 * {
 *     return Array (
 *         'EVERYONE' => Array(),
 *         'ANONYMOUS' => Array(),
 *         'USERS' => Array('midcom:create' => MIDCOM_PRIVILEGE_ALLOW)
 *     );
 * }
 * </code>
 *
 * See also the documentation of the $_default_magic_class_privileges member for further details.
 *
 * <i>User / Group specific privileges</i>
 *
 * This kind of privileges are rights, assigned directly to a user. Similar to the systemwide defaults,
 * they too apply to any operation done by the user / group respectively throughout the system. The magic
 * assignee SELF is used to denote such privileges, which can obviously only be assigned to users or
 * groups. These privileges are loaded at the time of user authentication only.
 *
 * You should use these privileges carefully, due to their global nature. If you assign the privilege
 * midgard:delete to a user, this means that the user can now delete all objects he can read, unless
 * there are again restricting privileges set to content objects.
 *
 * To be more flexible in the control over the top level objects, you may add a classname which restricts
 * the validity of the privilege to a class and all of its descendants.
 *
 * <i>Content object privileges</i>
 *
 * This is the kind of privilege that will be used most often. They are associated with any content
 * object in the system, and are read on every access to a content object. As you can see in the
 * introduction, you have the most flexibility here.
 *
 * The basic idea is, that you can assign privileges based on the combination of users/groups and
 * content objects. In other words, you can say the user x has the privilege midgard:update for
 * this object (and its descendants) only. This works with (virtual) groups as well.
 *
 * The possible assignees here are either a user, a group or one of the magic assignees EVERYONE,
 * USERS or ANONYMOuS, as outlined above.
 *
 * Be aware, that Midgard Persons and Groups count as content object when loaded from the database
 * in a tool like net.nemein.personnel, as the groups are not used for authentication but for
 * regular site operation there. Therefore, the SELF privileges mentioned above are not taken into
 * account when determining the content object privileges!
 *
 * <i>Privilege merging</i>
 *
 * This is, where we get to the guts of privileges, as this is not trivial (but nevertheless
 * straight-forward I hope). The general idea is based on the scope of object a privilege applies:
 *
 * System default privileges obviously have the largest scope, they apply to everyone. The next
 * smaller scope are privileges which are assigned to groups in general, followed by privileges
 * assigned directly to a user.
 *
 * From this point on, the privileges of the content objects are next in line, starting at the
 * top-level objects again (for example a root topic). The smallest scope finally then has the
 * object that is being accessed itself.
 *
 * Let us visualize this a bit:
 *
 * <pre>
 * ^ larger scope     System default privileges
 * |                  Class specific magic assignee default privileges
 * |                  Root Midgard group
 * |                  ... more parent Midgard groups ...
 * |                  Direct Midgard group membership
 * |                  Virtual group memberships
 * |                  User
 * |                  SELF privileges limited to a class
 * |                  Root content object
 * |                  ... more parent objects ...
 * v smaller scope    Accessed content object
 * </pre>
 *
 * Privileges assigned to a specific user always override owner privileges; owner privileges are
 * calculated on a per-content-object bases, and are merged just before the final user privileges are
 * merged into the privilege set. It is of no importance from where you get ownership at that point.
 *
 * Implementation notes: Internally, MidCOM separates the "user privilege set" which is everything
 * down to the line User above, and the content object privileges, which constitutes of the rest.
 * This separation has been done for performance reasons, as the user's privileges are loaded
 * immediately upon authentication of the user, and the privileges of the actual content objects
 * are merged into this set then. Normally, this should be of no importance for ACL users, but it
 * explains the more complex graph in the original mRFC.
 *
 * <b>Predefined Privileges</b>
 *
 * The MidCOM core defines a set of core privileges, which fall in two categories:
 *
 * <i>Midgard Core Privileges</i>
 *
 * These privileges are part of the MidCOM Database Abstraction layer (MidCOM DBA) and have been
 * originally proposed by me in a mail to the Midgard developers list. They will move into the
 * core level eventually, but for the time being MidCOM will control them. Unless otherwise noted,
 * all privileges are denied by default and no difference between owner and normal default privileges
 * is made.
 *
 * - <i>midgard:read</i> controls read access to the object, if denied, you cannot load the object
 *   from the database. This privilege is granted by default, and supersedes the current ViewerGroups
 *   implementation.
 * - <i>midgard:update</i> controls updating of objects. Be aware, that you need to be able to read
 *   the object before updating it, it is granted by default only for owners.
 * - <i>midgard:delete</i> controls deletion of objects. Be aware, that you need to be able to read
 *   the object before updating it, it is granted by default only for owners.
 * - <i>midgard:create</i> allows you to create new content objects as children on whatever content
 *   object that you have the create privilege for. This means, you can create an article if and only
 *   if you have create permission for either the parent article (if you create a so-called 'reply
 *   article') or the parent topic, it is granted by default only for owners.
 * - <i>midgard:parameters</i> allows the manipulation of parameters on the current object if and
 *   only if the user also has the midgard:update privilege on the object. This privileges is granted
 *   by default and covers the full set of parameter operations (create, update and delete).
 * - <i>midgard:attachments</i> is analogous to midgard:parameters but covers attachments instead
 *   and is also granted by default.
 * - <i>midgard:autoserve_attachment</i> controls, whether an attachment may be autoserved using
 *   the midcom-serveattachment handler. This is granted by default, allowing every attachment
 *   to be served using the default URL methods. Denying this right allows component authors to
 *   build more sophisticated access control restrictions to attachments.
 * - <i>midgard:privileges</i> allows the user to change the permissions on the objects they are
 *   granted for. You also need midgard:update and midgard:parameters to properly execute these
 *   operations.
 * - <i>midgard:owner</i> indicates that the user who has this privilege set is an owner of the
 *   given content object.
 *
 * <i>MidCOM Core Privileges</i>
 *
 * - <i>midcom:approve</i> grants the user the right to approve or unapprove objects.
 * - <i>midcom:component_config</i> grants the user access to configuration management systems
 *   in AIS. Components implementing these screens must check this privilege manually, while the
 *   midcom_baseclasses_components_request_admin baseclass does this implicitly when accessing
 *   the config screen (you still need to control toolbar links yourself), it is granted by default
 *   only for owners.
 * - <i>midcom:isonline</i> is needed to see the online state of another user. It is not granted
 *   by default.
 * - <i>midcom:vgroup_register</i> allows the user to add virtual groups to the system. This
 *   privilege is granted by default.
 * - <i>midcom:vgroup_delete</i> allows the user to delete virtual groups from the system. This
 *   privilege is granted by default.
 *
 * DEPRECATION NOTE: The vgroup registering / deletion commands will be denied in the long run
 * by default, for security reasons. Exact plan is not yet available though.
 *
 * <b>Assigning Privileges</b>
 *
 * You assign privileges by using the DBA set_privilege method, whose static implementation can
 * be found in midcom_baseclasses_core_dbobject::set_privilege(). Here is a quick overview over
 * that API, which naturally only works on MidCOM DBA level objects:
 *
 * - TODO: Update with the set/unset call variants
 * - TODO: Add get_privilege
 *
 * <pre>
 * Array get_privileges();
 * midcom_core_privilege create_new_privilege_object($name, $assignee = null, $value = MIDCOM_PRIVILEGE_ALLOW)
 * bool set_privilege(midcom_core_privilege $privilege);
 * bool unset_all_privileges();
 * bool unset_privilege(midcom_core_privilege $privilege);
 * </pre>
 *
 * These calls operate only on the privileges of the given object. They do not do any merging
 * whatsoever, this is the job of the auth framework itself (midcom_services_auth).
 *
 * Unsetting a privilege does not deny it, but clears the privilege specification on the current
 * object and sets it to INHERIT internally. As you might have guessed, if you want to clear
 * all privileges on a given object, call unset_all_privileges() on the DBA object in question.
 *
 * See the documentation of the DBA layer for more information on these five calls.
 *
 * <b>Checking Privileges</b>
 *
 * This class overs various methods to verify the privilege state of a user, all of them prefixed
 * with can_* for privileges and is_* for membership checks.
 *
 * Each function is available in a simple check version, which returns true or false, and a
 * require_* prefixed variant, which has no return value. The require variants of these calls
 * instead check if the given condition is met, if yes, they return silently, otherwise they
 * throw an access denied error.
 *
 * <b>Authentication</b>
 *
 * Whenever the system successfully creates a new login session (during auth service startup),
 * it checks whether the key <i>midcom_services_auth_login_success_url</i> is present in the HTTP
 * Request data. If this is the case, it relocates to the URL given in it. This member isn't set
 * by default in the MidCOM core, it is intended for custom authentication forms. The MidCOM
 * relocate function is used to for relocation, thus you can take full advantage of the
 * convenience functions in there. See midcom_application::relocate() for details.
 *
 * @todo Fully document authentication.
 * @package midcom.services
 */
class midcom_services_auth extends midcom_baseclasses_core_object
{
    /**
     * The currently authenticated user or null in case of anonymous access.
     * It is to be considered read-only.
     *
     * @var midcom_core_user
     * @access public
     */
    var $user = null;

    /**
     * Admin user level state. This is true if the currently authenticated user is an
     * Midgard Administrator, false otherwise.
     *
     * This effectively maps to $_MIDGARD['admin']; but it is suggested to use the auth class
     * for consistency reasons nevertheless.
     *
     * @var bool
     * @access public
     */
    var $admin = false;

    /**
     * This is a reference to the login session management system.
     *
     * @var midcom_services_auth_sessionmgr
     * @access public
     */
    var $sessionmgr = null;

    /**
     * Internal listing of all default privileges currently registered in the system. This
     * is a privilege name/value map.
     *
     * @todo This should be cached, as it would require loading all components by default.
     *     The component manifest might help here too.
     *
     * @var array
     * @access private
     */
    var $_default_privileges = Array();

    /**
     * Internal listing of all default owner privileges currently registered in the system.
     * All privileges not set in this list will be inherited. This is a privilege name/value
     * map.
     *
     * @todo This should be cached, as it would require loading all components by default.
     *     The component manifest might help here too.
     *
     * @var array
     * @access private
     */
    var $_owner_default_privileges = Array();

    /**
     * This listing contains all magic privileges assigned to the existing classes. It is a
     * multi-level array, example entry:
     *
     * <pre>
     * 'class_name' => Array
     * (
     *     'EVERYONE' => Array(),
     *     'ANONYMOUS' => Array(),
     *     'USERS' => Array
     *     (
     *         'midcom:create' => MIDCOM_PRIVILEGE_ALLOW,
     *         'midcom:update' => MIDCOM_PRIVILEGE_ALLOW
     *     ),
     * )
     * </pre>
     *
     * @todo This should be cached, as it would require loading all components by default.
     *     The component manifest might help here too.
     *
     * @var array
     * @access private
     */
    var $_default_magic_class_privileges = Array();

    /**
     * Internal listing of all known virtual groups, populated from the
     * MidCOM Virtual Groups registry. It indexes virtual group identifiers suitable for get_group
     * with their clear-text names.
     *
     * @var array
     * @access private
     */
    var $_vgroups = null;

    /**
     * Internal cache of all loaded groups, indexed by their identifiers.
     *
     * @var Array
     * @access private
     */
    var $_group_cache = Array();

    /**
     * Internal cache of all loaded users, indexed by their identifiers.
     *
     * @var Array
     * @access private
     */
    var $_user_cache = Array();

    /**
     * Internal cache of the effective privileges of users on content objects, this is
     * an associative array using a combination of the user identifier and the objects'
     * guid as index. The privileges for the anonymous user use the magic
     * EVERYONE as user identifier.
     *
     * @var Array
     * @access private
     */
    var $_privileges_cache = Array();

    /**
     * This is an internal flag used to override all regular permission checks with a sort-of
     * read-only privilege set. While internal_sudo is enabled, the system automatically
     * grants all privileges except midgard:create, midgard:update, midgard:delete and
     * midgard:privileges, which will always be denied. These checks go after the basic checks
     * for not authenticated users or admin level users.
     *
     * Danger, Will Robinson: You MUST NEVER touch this flag unless you are working with the
     * authentication core itself and now very much exactly what you are doing. Misusage of this
     * flag can result in serious secruity risks.
     *
     * All parts of the core may change this variable (as for example midcom_core_group_virtual
     * does during vgroup member loading).
     *
     * @var bool
     * @access private
     */
    var $_internal_sudo = false;

    /**
     * This flag indicates if sudo mode is active during execution. This will only be the
     * case if the sudo system actually grants this privileges, and only until components
     * release the rights again. This does override the full access control system at this time
     * and essentially give you full admin privileges (though this might change in the future).
     *
     * Note, that this is no bool but an int, otherwise it would be impossible to trace nested
     * sudo invocations, which are quite possible with multiple components calling each others
     * callback. A value of 0 indicates that sudo is inactive. A value greater then zero indicates
     * sudo mode is active, with the count being equal to the depth of the sudo callers.
     *
     * It is thus still safely possible to evaluate this member in a boolean context to check
     * for an enabled sudo mode.
     *
     * @var int
     * @access private
     * @see request_sudo();
     * @see drop_sudo();
     */
    var $_component_sudo = 0;

    /**
     * A reference to the authentication backend we should use by default.
     *
     * @var midcom_services_auth_backend
     * @access private
     */
    var $_auth_backend = null;

    /**
     * A reference to the authentication frontend we should use by default.
     *
     * @var midcom_services_auth_frontend
     * @access private
     */
    var $_auth_frontend = null;

    /**
     * Flag, which is set to true if the system encountered any new login credentials
     * during startup. If this is true, but no user is authenticated, login did fail.
     *
     * The variable is to be considered read-only.
     *
     * @var public
     */
    var $auth_credentials_found = false;

    /**
     * Simple constructor, calls base class and initializes the data members where applicable.
     * The real initialization work is done in initialize.
     */
    function midcom_services_auth()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        parent::midcom_baseclasses_core_object();
        debug_pop();
    }

    /**
     * Initialize the service:
     *
     * - Start up the login session service
     * - Load the core privileges.
     * - Initialize to the Midgard Authentication, then synchronize with the auth
     *   drivers' currently authenticated user overriding the Midgard Auth if
     *   necessary.
     */
    function initialize()
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        $this->sessionmgr = new midcom_services_auth_sessionmgr($this);

        $this->_register_core_privileges();
        $this->_initialize_user_from_midgard();
        $this->_prepare_authentication_drivers();
        if (! $this->_check_for_new_login_session())
        {
            // No new login detected, so we check if there is a running session.
            $this->_check_for_active_login_session();
        }

        debug_pop();
    }

    /**
     * Internal startup helper, checks if the current authentication fronted has new credentials
     * ready. If yes, it processes the login accordingly.
     *
     * @return bool Returns true, if a new login session was created, false if no credentials were found.
     * @access private
     */
    function _check_for_new_login_session()
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        $credentials = $this->_auth_frontend->read_authentication_data();

        if (! $credentials)
        {
            return false;
        }

        $this->auth_credentials_found = true;

        // Try to start up a new session, this will authenticate as well.
        if (! $this->_auth_backend->create_login_session($credentials['username'], $credentials['password']))
        {
            debug_add('The login information passed to the system was invalid.', MIDCOM_LOG_ERROR);
            debug_add("Username was {$credentials['username']}");
            // No password logging for security reasons.
            debug_pop();
            return false;
        }

        debug_add('Authentication was successful, we have a new login session now. Updating timestamps');

        $this->_sync_user_with_backend();
        $this->user->_storage->parameter('midcom', 'last_login', time());
        if (! $this->user->_storage->parameter('midcom', 'first_login'))
        {
            $this->user->_storage->parameter('midcom', 'first_login', time());
        }

        // Now we check whether there is a success-relocate URL given somewhere.
        if (array_key_exists('midcom_services_auth_login_success_url', $_REQUEST))
        {
            if (isset($_MIDCOM))
            {
                $_MIDCOM->relocate($_REQUEST['midcom_services_auth_login_success_url']);
            }
            else
            {
                header("Location: {$_REQUEST['midcom_services_auth_login_success_url']}");
                exit();
            }
            // This will exit.
        }

        debug_pop();
        return true;
    }

    /**
     * Internal helper, synchronizes the main service class with the authentication state
     * of the authentication backend.
     */
    function _sync_user_with_backend()
    {
        $this->user =& $this->_auth_backend->user;

        // This check is a bit fuzzy but will work as long as MidgardAuth is in sync with
        // MidCOM auth.
        if (   $_MIDGARD['admin']
            || $_MIDGARD['root'])
        {
            $this->admin = true;
        }
        else
        {
            $this->admin = false;
        }
    }

    /**
     * Internal startup helper, checks the currently running authentication backend for
     * a running login session.
     *
     * @access private
     */
    function _check_for_active_login_session()
    {
        if (! $this->_auth_backend->read_login_session())
        {
            return;
        }

        if (! $this->sessionmgr->authenticate_session($this->_auth_backend->session_id))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('Failed to re-authenticate a previous login session, not changing credentials.');
            debug_pop();
            return;
        }

        $this->_sync_user_with_backend();
    }

    /**
     * Internal startup helper, synchronizes the authenticated user with the Midgard Authentication
     * for startup. This will be overridden by MidCOM Auth, but is there for compatibility reasons.
     *
     * @access private
     */
    function _initialize_user_from_midgard()
    {
        if ($_MIDGARD['user'])
        {
            $this->user =& $this->get_user($_MIDGARD['user']);
            if (   $_MIDGARD['admin']
                || $_MIDGARD['root'])
            {
                $this->admin = true;
            }
        }
    }

    /**
     * Internal startup helper, loads all configured authentication drivers.
     *
     * @access private
     */
    function _prepare_authentication_drivers()
    {
        require_once (MIDCOM_ROOT . "/midcom/services/auth/backend/{$GLOBALS['midcom_config']['auth_backend']}.php");
        $classname = "midcom_services_auth_backend_{$GLOBALS['midcom_config']['auth_backend']}";
        $this->_auth_backend = new $classname($this);

        require_once (MIDCOM_ROOT . "/midcom/services/auth/frontend/{$GLOBALS['midcom_config']['auth_frontend']}.php");
        $classname = "midcom_services_auth_frontend_{$GLOBALS['midcom_config']['auth_frontend']}";
        $this->_auth_frontend = new $classname();
    }

    /**
     * This internal helper will initialize the default privileges array with all core
     * privileges currently defined.
     *
     * @see $_default_privileges
     * @access protected
     */
    function _register_core_privileges()
    {
        $this->register_default_privileges(
            Array
            (
                // Midgard core level privileges
                'midgard:update' => Array (MIDCOM_PRIVILEGE_DENY, MIDCOM_PRIVILEGE_ALLOW),
                'midgard:delete' => Array (MIDCOM_PRIVILEGE_DENY, MIDCOM_PRIVILEGE_ALLOW),
                'midgard:create' => Array (MIDCOM_PRIVILEGE_DENY, MIDCOM_PRIVILEGE_ALLOW),
                'midgard:read' => Array (MIDCOM_PRIVILEGE_ALLOW, MIDCOM_PRIVILEGE_ALLOW),
                'midgard:parameters' => Array (MIDCOM_PRIVILEGE_ALLOW, MIDCOM_PRIVILEGE_ALLOW),
                'midgard:attachments' => Array (MIDCOM_PRIVILEGE_ALLOW, MIDCOM_PRIVILEGE_ALLOW),
                'midgard:autoserve_attachment' => MIDCOM_PRIVILEGE_ALLOW,
                'midgard:privileges' => Array (MIDCOM_PRIVILEGE_DENY, MIDCOM_PRIVILEGE_ALLOW),
                'midgard:owner' => MIDCOM_PRIVILEGE_DENY,

                // MidCOM core level privileges
                'midcom:approve' => MIDCOM_PRIVILEGE_DENY,
                'midcom:component_config' => Array (MIDCOM_PRIVILEGE_DENY, MIDCOM_PRIVILEGE_ALLOW),
                'midcom:urlname' => MIDCOM_PRIVILEGE_DENY,
                'midcom:isonline' => Array (MIDCOM_PRIVILEGE_DENY, MIDCOM_PRIVILEGE_ALLOW),
                'midcom:vgroup_register' => MIDCOM_PRIVILEGE_ALLOW,
                'midcom:vgroup_delete' => MIDCOM_PRIVILEGE_ALLOW,
                'midcom:ajax' => MIDCOM_PRIVILEGE_DENY,
                'midcom:centralized_toolbar' => MIDCOM_PRIVILEGE_DENY,
            )
        );
    }

    /**
     * This internal helper checks if a privilege is available during internal
     * sudo mode, as outlined in the corresponding variable.
     *
     * @param string $privilege The privilege to check for
     * @return bool True if the privilege has been granted, false otherwise.
     * @access private
     * @see $_internal_sudo
     */
    function _can_do_internal_sudo($privilege)
    {
        switch($privilege)
        {
            case 'midgard:create':
            case 'midgard:update':
            case 'midgard:delete':
            case 'midgard:privileges':
                // We do not allow this, for security reasons.
                return false;

            default:
                // allow everything else.
                return true;
        }
    }

    /**
     * Checks whether a user has a certain privilege on the given content object.
     * Works on the currently authenticated user by default, but can take another
     * user as an optional argument.
     *
     * @param string $privilege The privilege to check for
     * @param MidgardObject $content_object A Midgard Content Object
     * @param midcom_core_user $user The user against which to check the privilege, defaults to the currently authenticated user.
     *     You may specify "EVERYONE" instead of an object to check what an anonymous user can do.
     * @return bool True if the privilege has been granted, false otherwise.
     */
    function can_do($privilege, &$content_object, $user = null)
    {
        if (   $privilege != 'midgard:read'
            && $_MIDGARD['sitegroup'] != 0
            && $content_object->sitegroup != $_MIDGARD['sitegroup'])
        {
            return false;
        }
        return $this->can_do_byguid($privilege, $content_object->guid, get_class($content_object), $user);
    }

    /**
     * Checks whether a user has a certain privilege on the given (via guid and class) content object.
     * Works on the currently authenticated user by default, but can take another
     * user as an optional argument.
     *
     * @param string $privilege The privilege to check for
     * @param string $object_guid A Midgard GUID pointing to an object
     * @param string $object_class Class of the object in question
     * @param midcom_core_user $user The user against which to check the privilege, defaults to the currently authenticated user.
     *     You may specify "EVERYONE" instead of an object to check what an anonymous user can do.
     * @return bool True if the privilege has been granted, false otherwise.
     */
    function can_do_byguid($privilege, $object_guid, $object_class, $user = null)
    {

        if (   is_null($user)
            && ! is_null($this->user)
            && $this->admin)
        {
            // Administrators always have access.
            return true;
        }

        if ($this->_internal_sudo)
        {
            //debug_push_class(__CLASS__, __FUNCTION__);
            //debug_add('INTERNAL SUDO mode is enabled. Generic Read-Only mode set.', MIDCOM_LOG_DEBUG);
            //debug_pop();
            return $this->_can_do_internal_sudo($privilege);
        }

        if ($this->_component_sudo)
        {
            return true;
        }

        // Cache results of ACL checks per session
        static $cached_privileges = array();
        if (!is_null($user))
        {
            $for_user =& $user;
        }
        else
        {
            $for_user =& $this->user;
        }
        if (is_null($for_user))
        {
                $cache_key = "{$object_guid}";
                $privilege_key = "{$cache_key}-{$privilege}";
        }
        else
        {
            if (is_string($for_user))
            {
                $cache_key = "{$for_user}-{$object_guid}";
                $privilege_key = "{$cache_key}-{$privilege}";
            }
            else
            {
                $cache_key = "{$for_user->id}-{$object_guid}";
                $privilege_key = "{$cache_key}-{$privilege}";
            }
        }

        if (!isset($cached_privileges[$privilege_key]))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Cache {$privilege_key} miss, fetching privileges for {$object_guid}");
            debug_pop();
            $full_privileges = $this->get_privileges_byguid($object_guid, $object_class, $for_user);
            foreach ($full_privileges as $priv => $value)
            {
                //echo "{$object_class} {$object_guid} {$priv} {$value}<br />\n";
                if ($value == MIDCOM_PRIVILEGE_ALLOW)
                {
                    $cached_privileges["{$cache_key}-{$priv}"] = true;
                }
                else
                {
                    $cached_privileges["{$cache_key}-{$priv}"] = false;
                }
            }
            if (! array_key_exists($privilege, $full_privileges))
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("The privilege {$privilege} is unknown at this point. Assuming not granted privilege.", MIDCOM_LOG_WARN);
                debug_pop();
                return false;
            }
        }

        return $cached_privileges[$privilege_key];
    }

    /**
     * Checks, whether the given user have the privilege assigned to him in general.
     * Be aware, that this does not take any permissions overridden by content objects
     * into account. Whenever possible, you should user the can_do() variant of this
     * call therefore. can_user_do is only of interest in cases where you do not have
     * any content object available, for example when creating root topics.
     *
     * @param string $privilege The privilege to check for
     * @param midcom_core_user $user The user against which to check the privilege, defaults to the currently authenticated user,
     *     you may specify 'EVERYONE' here to check what an anonymous user can do.
     * @param string $class Optional parameter to set if the check should take type specific permissions into account. The class must be default constructible.
     * @param string $component Component providing the class
     * @return bool True if the privilege has been granted, false otherwise.
     */
    function can_user_do($privilege, $user = null, $class = null, $component = null)
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        if (is_null($user))
        {
            if ($this->admin)
            {
                // Administrators always have access.
                debug_pop();
                return true;
            }
            $user =& $this->user;
        }

        if (   is_string($user)
            && $user == 'EVERYONE')
        {
            $user = null;
        }

        if (!is_null($user))
        {
            debug_add("Querying privilege {$privilege} for user {$user->id} to class {$class}", MIDCOM_LOG_DEBUG);
        }

        if ($this->_internal_sudo)
        {
            debug_add('INTERNAL SUDO mode is enabled. Generic Read-Only mode set.', MIDCOM_LOG_DEBUG);
            debug_pop();
            return $this->_can_do_internal_sudo($privilege);
        }

        if ($this->_component_sudo)
        {
            debug_pop();
            return true;
        }

        // Initialize this one to be sure to have it.
        $default_magic_class_privileges = Array();

        if ($class !== null)
        {
            if (!class_exists($class))
            {
                if (   is_null($component)
                    || !$_MIDCOM->componentloader->load_graceful($component))
                {
                    debug_add("can_user_do check to undefined class '{$class}'.", MIDCOM_LOG_ERROR);
                    debug_pop();
                    return false;
                }
            }
            $tmp_object = new $class();
            $this->_load_class_magic_privileges($tmp_object);
        }
        else
        {
            $tmp_object = null;
        }

        if (is_null($user))
        {
            $user_privileges = Array();
            $user_per_class_privileges = Array();
            if ($tmp_object !== null)
            {
                if (!isset($tmp_object->__new_class_name__))
                {
                    $tmp_class_name = get_class($tmp_object);
                }
                else
                {
                    $tmp_class_name = $tmp_object->__new_class_name__;
                }

                $default_magic_class_privileges = array_merge
                (
                    $this->_default_magic_class_privileges[$tmp_class_name]['EVERYONE'],
                    $this->_default_magic_class_privileges[$tmp_class_name]['ANONYMOUS']
                );
            }
        }
        else
        {
            $user_privileges = $user->get_privileges();
            if ($tmp_object === null)
            {
                $user_per_class_privileges = Array();
            }
            else
            {
                if (!isset($tmp_object->__new_class_name__))
                {
                    $tmp_class_name = get_class($tmp_object);
                }
                else
                {
                    $tmp_class_name = $tmp_object->__new_class_name__;
                }

                $user_per_class_privileges = $user->get_per_class_privileges($tmp_object);
                $default_magic_class_privileges = array_merge
                (
                    $this->_default_magic_class_privileges[$tmp_class_name]['EVERYONE'],
                    $this->_default_magic_class_privileges[$tmp_class_name]['USERS']
                );
            }
        }

        // Remember to synchronize this merging chain with the one in get_privileges();
        $full_privileges = array_merge
        (
            $this->_default_privileges,
            $default_magic_class_privileges,
            $user_privileges,
            $user_per_class_privileges
        );

        // Check for Ownership:
        if ($full_privileges['midgard:owner'] == MIDCOM_PRIVILEGE_ALLOW)
        {
            $full_privileges = array_merge
            (
                $full_privileges,
                $_MIDCOM->auth->get_owner_default_privileges()
            );
        }

        if (! array_key_exists($privilege, $full_privileges))
        {
            debug_add("Warning, the privilege {$privilege} is unknown at this point. Assuming not granted privilege.");
            debug_pop();
            return false;
        }

        debug_pop();
        return ($full_privileges[$privilege] == MIDCOM_PRIVILEGE_ALLOW);
    }

    /**
     * Returns a full listing of all currently known privileges for a certain object/user
     * combination.
     *
     * The information is cached per object-guid during runtime, so that repeated checks
     * to the same object do not cause repeating checks. Be aware that this means, that
     * new privileges set are not guaranteed to take effect until the next request.
     *
     * @param MidgardObject $content_object A Midgard Content Object
     * @param midcom_core_user $user The user against which to check the privilege, defaults to the currently authenticated user.
     *     You may specify "EVERYONE" instead of an object to check what an anonymous user can do.
     * @return Array Associative listing of all privileges and their value.
     */
    function get_privileges(&$content_object, $user = null)
    {
        return $this->get_privileges_byguid($privilege, $content_object->guid, get_class($content_object), $user);
    }

    /**
     * Returns a full listing of all currently known privileges for a certain object/user
     * combination (object is specified by guid/class combination)
     *
     * The information is cached per object-guid during runtime, so that repeated checks
     * to the same object do not cause repeating checks. Be aware that this means, that
     * new privileges set are not guaranteed to take effect until the next request.
     *
     * @param string $object_guid A Midgard GUID pointing to an object
     * @param string $object_class Class of the object in question
     * @param midcom_core_user $user The user against which to check the privilege, defaults to the currently authenticated user.
     *     You may specify "EVERYONE" instead of an object to check what an anonymous user can do.
     * @return Array Associative listing of all privileges and their value.
     */
    function get_privileges_byguid($object_guid, $object_class, $user = null)
    {
        // TODO: Clean if/else shorthands, make sure this works correctly for magic assignees as well
        if (is_null($user))
        {
            $user =& $this->user;
            if (empty($user))
            {
                $cache_user_id = 'anonymous';
            }
            else
            {
                $cache_user_id = $user->id;
            }
        }

        if (is_string($user))
        {
            if ($user != 'EVERYONE'
                && (    mgd_is_guid($user)
                    || is_numeric($user)))
            {
                $user =& $_MIDCOM->auth->get_user($user);
                $cache_user_id = $user->id;
            }
            else
            {
                $cache_user_id = $user;
                $user = null;
            }
        }
        // safety
        if (!isset($cache_user_id))
        {
            if (is_object($user))
            {
                $cache_user_id = $user->id;
            }
            else
            {
                $cache_user_id = $user;
            }
        }
        if (!class_exists($object_class))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "class '{$object_class}' does not exist");
            // This will exit()
        }
        $dummy_object_init = new $object_class();
        if (! $_MIDCOM->dbclassloader->is_midcom_db_object($dummy_object_init))
        {
            $dummy_object = $_MIDCOM->dbfactory->convert_midgard_to_midcom($dummy_object_init);
            if (is_null($dummy_object))
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add('Failed to convert an object, falling back to an empty privilege set for the object in question. See debug level log for details.');
                debug_pop();
                return Array();
            }
        }
        else
        {
            $dummy_object =& $dummy_object_init;
        }

        // Check for a cache Hit.
        $cache_id = "{$cache_user_id}:{$object_guid}";
        if (array_key_exists($cache_id, $this->_privileges_cache))
        {
            $full_privileges = $this->_privileges_cache[$cache_id];
        }
        else
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Cache miss for {$cache_id}");
            debug_pop();
            if (   is_object($user)
                && method_exists($user, 'get_privileges')
                && method_exists($user, 'get_per_class_privileges'))
            {
                $user_privileges = $user->get_privileges();
                $user_per_class_privileges = $user->get_per_class_privileges($dummy_object);
            }
            else
            {
                $user_privileges = Array();
                $user_per_class_privileges = Array();
            }
            $this->_load_class_magic_privileges($dummy_object);
            $dummy_object->guid = $object_guid;

            // Remember to sync this merging chain with can_user_do.
            if ($cache_user_id == 'EVERYONE')
            {
                $collect_user = $cache_user_id;
            }
            else
            {
                $collect_user = null;
            }
            $full_privileges = array_merge
            (
                $this->_default_privileges,
                $this->_default_magic_class_privileges[$dummy_object->__new_class_name__]['EVERYONE'],
                (
                    (is_null($this->user))
                        ? $this->_default_magic_class_privileges[$dummy_object->__new_class_name__]['ANONYMOUS']
                        : $this->_default_magic_class_privileges[$dummy_object->__new_class_name__]['USERS']
                ),
                $user_privileges,
                $user_per_class_privileges,
                midcom_core_privilege::collect_content_privileges($dummy_object, $collect_user)
            );

            $this->_privileges_cache[$cache_id] = $full_privileges;
        }

        return $full_privileges;
    }


    /**
     * Request superuser privileges for the domain passed.
     *
     * STUB IMPLEMENTATION ONLY, WILL ALWAYS GRANT SUDO.
     *
     * You have to call midcom_services_auth::drop_sudo() as soon as you no longer
     * need the elevated privileges, which will reset the authentication data to the
     * initial credentials.
     *
     * @param string $domain The domain to request sudo for. This is a component name.
     * @return bool True if admin privileges were granted, false otherwise.
     */
    function request_sudo ($domain = null)
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        if (! $GLOBALS['midcom_config']['auth_allow_sudo'])
        {
            debug_add("SUDO is not allowed on this website.", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }

        if (is_null($domain))
        {
            $domain = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_COMPONENT);
            debug_add("Domain was not supplied, falling back to '{$domain}' which we got from the current component context.");
        }

        if ($domain == '')
        {
            debug_add("SUDO request for an empty domain, this should not happen. Denying sudo.", MIDCOM_LOG_INFO);
            debug_pop();
            return false;
        }

        $this->_component_sudo++;

        debug_add("Entered SUDO mode for domain {$domain}.", MIDCOM_LOG_INFO);

        debug_pop();
        return true;
    }

    /**
     * Drops previously acquired superuser privileges.
     *
     * @see request_sudo()
     */
    function drop_sudo()
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        if ($this->_component_sudo > 0)
        {
            debug_add('Leaving SUDO mode.');
            $this->_component_sudo--;
        }
        else
        {
            debug_add('Requested to leave SUDO mode, but sudo was already disabled. Ignoring request.', MIDCOM_LOG_INFO);
        }

        debug_pop();
    }

    /**
     * Check, whether a user is member of a given group. By default, the query is run
     * against the currently authenticated user.
     *
     * It always returns TRUE for administrative users.
     *
     * @param mixed $group Group to check against, this can be either a midcom_core_group object or a group string identifier.
     * @param midcom_core_user The user which should be checked, defaults to the current user.
     * @return bool Indicating membership state.
     */
    function is_group_member($group, $user = null)
    {
        // Default parameter
        if (is_null($user))
        {
            if (is_null($this->user))
            {
                // not authenticated
                return false;
            }
            $user =& $this->user;
        }

        if ($this->admin)
        {
            // Administrators always have access.
            return true;
        }

        return $user->is_in_group($group);
    }

    /**
     * Returns true if there is an authenticated user, false otherwise.
     *
     * @return bool True if there is a user logged in.
     */
    function is_valid_user()
    {
        return (! is_null($this->user));
    }

    /**
     * Validates that the current user has the given privilege granted on the
     * content object passed to the function.
     *
     * If this is not the case, an Access Denied error is generated, the message
     * defaulting to the string 'access denied: privilege %s not granted' of the
     * MidCOM main L10n table.
     *
     * The check is always done against the currently authenticated user. If the
     * check is successful, the function returns silently.
     *
     * @param string $privilege The privilege to check for
     * @param MidgardObject $content_object A Midgard Content Object
     * @param string $message The message to show if the privilege has been denied.
     */
    function require_do($privilege, &$content_object, $message = null)
    {
        if (! $this->can_do($privilege, $content_object))
        {
            if (is_null($message))
            {
                $string = $_MIDCOM->i18n->get_string('access denied: privilege %s not granted', 'midcom');
                $message = sprintf($string, $privilege);
            }
            $this->access_denied($message);
            // This will exit.
        }
    }

    /**
     * Validates, whether the given user have the privilege assigned to him in general.
     * Be aware, that this does not take any permissions overridden by content objects
     * into account. Whenever possible, you should user the can_do() variant of this
     * call therefore. can_user_do is only of interest in cases where you do not have
     * any content object available, for example when creating root topics.
     *
     * If this is not the case, an Access Denied error is generated, the message
     * defaulting to the string 'access denied: privilege %s not granted' of the
     * MidCOM main L10n table.
     *
     * The check is always done against the currently authenticated user. If the
     * check is successful, the function returns silently.
     *
     * @param string $privilege The privilege to check for
     * @param string $message The message to show if the privilege has been denied.
     * @param string $class Optional parameter to set if the check should take type specific permissions into account. The class must be default constructible.
     */
    function require_user_do($privilege, $message = null, $class = null)
    {
        if (! $this->can_user_do($privilege, null, $class))
        {
            if (is_null($message))
            {
                $string = $_MIDCOM->i18n->get_string('access denied: privilege %s not granted', 'midcom');
                $message = sprintf($string, $privilege);
            }
            $this->access_denied($message);
            // This will exit.
        }
    }


    /**
     * Validates that the current user is a member of the given group.
     *
     * If this is not the case, an Access Denied error is generated, the message
     * defaulting to the string 'access denied: user is not member of the group %s' of the
     * MidCOM main L10n table.
     *
     * The check is always done against the currently authenticated user. If the
     * check is successful, the function returns silently.
     *
     * @param mixed $group Group to check against, this can be either a midcom_core_group object or a group string identifier.
     * @param string $message The message to show if the user is not member of the given group.
     */
    function require_group_member($group, $message = null)
    {
        if (! $this->is_group_member($group))
        {
            if (is_null($message))
            {
                $string = $_MIDCOM->i18n->get_string('access denied: user is not member of the group %s', 'midcom');
                if (is_object($group))
                {
                    $message = sprintf($string, $group->name);
                }
                else
                {
                    $message = sprintf($string, $group);
                }
            }

            $this->access_denied($message);
            // This will exit.
        }
    }

    /**
     * Validates that we currently have admin level privileges, which can either
     * come from the current user, or from the sudo service.
     *
     * If the check is successful, the function returns silently.
     * @param string $message The message to show if the admin level privileges are missing..
     */
    function require_admin_user($message = null)
    {
        if ($message === null)
        {
            $message = $_MIDCOM->i18n->get_string('access denied: admin level privileges required', 'midcom');
        }
        if (   ! $this->admin
            && ! $this->_component_sudo)
        {
            $this->access_denied($message);
            // This will exit.
        }
    }

    /**
     * Validates that there is an authenticated user.
     *
     * If this is not the case, the regular login page is shown automatically, see
     * show_login_page() for details..
     *
     * If the check is successful, the function returns silently.
     *
     * @param string $method Preferred authentication method: form or basic
     */
    function require_valid_user($method = 'form')
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("require_valid_user called", MIDCOM_LOG_DEBUG);
        debug_print_function_stack("require_valid_user called at this level");
        debug_pop();
        if (! $this->is_valid_user())
        {
            switch ($method)
            {
                case 'basic':
                    $this->_http_basic_auth();
                    break;

                case 'form':
                default:
                    $this->show_login_page();
                    // This will exit.
            }
        }
    }

    /**
     * Handles HTTP Basic authentication
     */
    function _http_basic_auth()
    {
        // TODO: convert to MidCOM DBA API
        if ($_MIDGARD['sitegroup'])
        {
            $sitegroup = mgd_get_sitegroup($_MIDGARD['sitegroup']);
        }
        else
        {
            $sitegroup = mgd_get_sitegroup();
            $sitegroup->name = 'SG0';
        }

        if (!isset($_SERVER['PHP_AUTH_USER']))
        {
            header("WWW-Authenticate: Basic realm=\"{$sitegroup->name}\"");
            header('HTTP/1.0 401 Unauthorized');
            // TODO: more fancy 401 output ?
            echo "<h1>Authorization required</h1>\n";
            $_MIDCOM->finish();
            exit();
        }
        else
        {
            if (!$this->sessionmgr->_do_midgard_auth($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']))
            {
                // Wrong password: Recurse until auth ok or user gives up
                unset($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
                $this->_http_basic_auth();
            }
            // Figure out how to update midcom auth status
            $_MIDCOM->auth->_initialize_user_from_midgard();
        }
    }

    /**
     * Merges a new set of default privileges into the current set.
     * Existing keys will be silently overwritten.
     *
     * This is usually only called by the framework startup and the
     * component loader.
     *
     * If only a single default value is set (type integer), then this value is taken
     * for the default and the owner privilege is unset (meaning INHERIT). If two
     * values (type array of integers) is set, the first privilege value is used for
     * default, the second one for the owner privilege set.
     *
     * @param Array $privileges An associative privilege_name => default_values listing.
     */
    function register_default_privileges ($privileges)
    {
        $default_privileges = Array();
        $owner_default_privileges = Array();

        foreach ($privileges as $name => $values)
        {
            if (! is_array($values))
            {
                $values = Array($values, MIDCOM_PRIVILEGE_INHERIT);
            }

            $default_privileges[$name] = $values[0];
            if ($values[1] != MIDCOM_PRIVILEGE_INHERIT)
            {
                $owner_default_privileges[$name] = $values[1];
            }
        }

        $this->_default_privileges = array_merge($this->_default_privileges, $default_privileges);
        $this->_owner_default_privileges = array_merge($this->_owner_default_privileges, $owner_default_privileges);
    }

    /**
     * Returns a listing of all known(!) virtual groups.
     *
     * @return An associative vgroup_id (including the vgroup: prefix) => vgroup_name listing.
     */
    function get_all_vgroups()
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        if (is_null($this->_vgroups))
        {
            $qb = new midgard_query_builder('midcom_core_group_virtual_db');
            $result = @$qb->execute();
            if (! $result)
            {
                $this->_vgroups = Array();
            }
            else
            {
                foreach ($result as $vgroup_entry)
                {
                    $id = "vgroup:{$vgroup_entry->component}-{$vgroup_entry->identifier}";
                    $this->_vgroups[$id] = $vgroup_entry->name;
                }
            }
        }

        debug_pop();
        return $this->_vgroups;
    }

    /**
     * Factory Method: Resolves any assignee identifier known by the system into an appropriate
     * user/group object.
     *
     * You must adhere the reference that is returned, otherwise the internal caching
     * and runtime state strategy will fail.
     *
     * @param string $id A valid user or group identifier useable as assignee (e.g. the $id member
     *     of any midcom_core_user or midcom_core_group object).
     * @return object A reference to the corresponding object or false on failure.
     */
    function & get_assignee($id)
    {
        $result = null;

        $parts = explode(':', $id);

        switch ($parts[0])
        {
            case 'user':
                $result =& $this->get_user($id);
                break;

            case 'group':
            case 'vgroup':
                $result =& $this->get_group($id);
                break;

            default:
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("The identifier {$id} cannot be resolved into an assignee, it cannot be mapped to a type.", MIDCOM_LOG_WARN);
                debug_pop();
                break;
        }

        return $result;
    }

    /**
     * This is a wrapper for get_user, which allows user retrieval by its name.
     * If the username is unknown, false is returned.
     *
     * @param string $name The name of the user to look up.
     * @return midcom_core_user A reference to the user object matching the username,
     *     or false if the username is unknown.
     */
    function & get_user_by_name($name)
    {
        $qb = new midgard_query_builder('midgard_person');
        $qb->add_constraint('username', '=', $name);
        $result = @$qb->execute();
        if (   ! $result
            || count($result) == 0)
        {
            $result = false;
            return $result;
        }
        return $this->get_user($result[0]);
    }

    /**
     * This is a wrapper for get_user, which allows user retrieval by its email address.
     * If the email is empty or unknown, false is returned.
     *
     * @param string $email The email of the user to look up.
     * @return array/midcom_core_user A reference to the user object matching the email, array if multiple matches
     *     or false if the email is unknown.
     */
    function get_user_by_email($email)
    {
        if (empty($email)) {
            return false;
        }

        $qb = new midgard_query_builder('midgard_person');
        $qb->add_constraint('email', '=', $email);
        $results = @$qb->execute();

        if (   !$results
            || count($results) == 0)
        {
            return false;
        }

        if (count($results) > 1)
        {
            $users = array();
            foreach ($results as $result)
            {
                $users[] = $this->get_user($result);
            }
            return $users;
        }

        return $this->get_user($results[0]);
    }

    /**
     * This is a wrapper for get_group, which allows Midgard Group retrieval by its name.
     * If the group name is unknown, false is returned.
     *
     * In the case that more then one
     * group matches the given name, the first one is returned. Note, that this should not
     * happen as midgard group names should be unique according to the specs.
     *
     * @param string $name The name of the group to look up.
     * @return midcom_core_group A reference to the group object matching the group name,
     *     or false if the group name is unknown.
     */
    function & get_midgard_group_by_name($name,$sg_id=null)
    {
        //$sg_id = $sg_id == null || !is_integer($sg_id) ? $_MIDGARD['sitegroup'] : $sg_id;
        $qb = new midgard_query_builder('midgard_group');
        $qb->add_constraint('name', '=', $name);
        if (is_integer($sg_id))
        {
            $qb->add_constraint('sitegroup', '=', $sg_id);
        }
        $result = @$qb->execute();
        if (   ! $result
            || count($result) == 0)
        {
            $result = false;
            return $result;
        }
        return $this->get_group($result[0]);
    }

    /**
     * Factory Method: Loads a user from the database and returns an object instance.
     *
     * You must adhere the reference that is returned, otherwise the internal caching
     * and runtime state strategy will fail.
     *
     * @param mixed $id A valid identifier for a MidgardPerson: An existing midgard_person class
     *     or subclass thereof, a Person ID or GUID or a midcom_core_user identifier.
     * @return midcom_core_user A reference to the user object matching the identifier or false on failure.
     */
    function & get_user($id)
    {
        $object = null;
        if (is_double($id))
        {
            // This is some crazy workaround for cases where the ID passed is a double
            // (coming from $_MIDGARD['user'] possibly) and is_object($id), again for
            // whatever reason, evaluates to true for that object...
            $id = (int) $id;
        }
        else if (is_object($id))
        {
            if (is_a($id, 'midcom_baseclasses_database_person'))
            {
                $id = $id->id;
                $object = null;
            }
            else if (is_a($id, 'midgard_person'))
            {
                $object = $id;
                $id = $object->id;
            }
            else
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_print_type('The passed argument was an object of an unsupported type:', $id, MIDCOM_LOG_WARN);
                debug_print_r('Complete object dump:', $id);
                debug_pop();
                $result = false;
                return $result;
            }
        }
        else if (   ! is_string($id)
                 && ! is_integer($id))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_print_type('The passed argument was an object of an unsupported type:', $id, MIDCOM_LOG_WARN);
            debug_print_r('Complete object dump:', $id);
            debug_pop();
            $result = false;
            return $result;
        }

        if (! array_key_exists($id, $this->_user_cache))
        {
            if (is_null($object))
            {
                $this->_user_cache[$id] = new midcom_core_user($id);
            }
            else
            {
                $this->_user_cache[$id] = new midcom_core_user($object);
            }
        }

        return $this->_user_cache[$id];
    }

    /**
     * Returns a midcom_core_group instance. Valid arguments are either a valid group identifier
     * (group:... or vgroup:...), any valid identifier for the midcom_core_group
     * constructor or a valid object of that type.
     *
     * You must adhere the reference that is returned, otherwise the internal caching
     * and runtime state strategy will fail.
     *
     * @param mixed $id The identifier of the group as outlined above.
     * @return midcom_core_group A group object instance matching the identifier, or false on failure.
     */
    function & get_group($id)
    {
        $group = false;
        if (   is_object($id)
            && is_a($id, 'midcom_baseclasses_database_group'))
        {
            $object = $id;
            $id = "group:{$id->guid}";
            if (! array_key_exists($id, $this->_group_cache))
            {
                $this->_group_cache[$id] = new midcom_core_group_midgard($object->id);
            }
        }
        else if (   is_object($id)
                 && is_a($id, 'midgard_group'))
        {
            $object = $id;
            $id = "group:{$id->guid}";
            if (! array_key_exists($id, $this->_group_cache))
            {
                $this->_group_cache[$id] = new midcom_core_group_midgard($object);
            }
        }
        else if (is_string($id))
        {
            if (! array_key_exists($id, $this->_group_cache))
            {
                $id_parts = explode(':', $id);
                if (count($id_parts) == 2)
                {
                    // This is a (v)group:... identifier
                    switch ($id_parts[0])
                    {
                        case 'group':
                            $this->_group_cache[$id] = new midcom_core_group_midgard($id_parts[1]);
                            break;

                        case 'vgroup':
                            $this->_group_cache[$id] = new midcom_core_group_virtual($id_parts[1]);
                            break;

                        default:
                            $this->_group_cache[$id] = false;
                            debug_push_class(__CLASS__, __FUNCTION__);
                            debug_add("The group type identifier {$id_parts[0]} is unknown, no group was loaded.", MIDCOM_LOG_WARN);
                            debug_pop();
                            break;
                    }
                }
                else
                {
                    // This must be a group ID, lets hope that the group_midgard constructor
                    // can take it.
                    $tmp = new midcom_core_group_midgard($id);
                    if (! $tmp)
                    {
                        $this->_group_cache[$id] = false;
                        debug_push_class(__CLASS__, __FUNCTION__);
                        debug_add("The group type identifier {$id} is of an invalid type, no group was loaded.", MIDCOM_LOG_WARN);
                        debug_pop();
                    }
                    else
                    {
                        $id = $tmp->id;
                        $this->_group_cache[$id] = $tmp;
                    }
                }
            }
        }
        else if (is_int($id))
        {
            // Looks like an object ID, again we try the midgard group constructor.
            $tmp = new midcom_core_group_midgard($id);
            if (! $tmp)
            {
                $this->_group_cache[$id] = false;
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("The group type identifier {$id} is of an invalid type, no group was loaded.", MIDCOM_LOG_WARN);
                debug_pop();
            }
            else
            {
                $id = $tmp->id;
                $this->_group_cache[$id] = $tmp;
            }
        }
        else
        {
            $this->_group_cache[$id] = false;
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("The group type identifier {$id} is of an invalid type, no group was loaded.", MIDCOM_LOG_WARN);
            debug_pop();
        }

        return $this->_group_cache[$id];
    }

    /**
     * This is a simple helper function which validates whether a given privilege
     * exists by its name. Essentially this checks if a corresponding default privilege
     * has been registered in the system.
     *
     * @todo This call should load the component associated to the privilege on demand.
     * @param string $name The name of the privilege to check.
     * @return bool Indicating whether the privilege does exist.
     */
    function privilege_exists($name)
    {
        return array_key_exists($name, $this->_default_privileges);
    }

    /**
     * Delete a registered virtual group in the system. This requires the privilege
     * midcom:vgroup_delete assigned to the user (there is no content object checked).
     *
     * @param midcom_core_group_virtual $virtual_group The group to drop, loaded by get_group() previously.
     * @return bool Indicating success.
     */
    function delete_virtual_group($virtual_group)
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        $this->require_user_do('midcom:vgroup_delete', 'You need the privilege "midcom:vgroup_register" to register any virtual group.');

        if (   ! is_a($virtual_group, 'midcom_core_group_virtual')
            || is_null($virtual_group->_storage)
            || ! $virtual_group->_storage->id)
        {
            debug_add('The virtual group passed cannot be removed, the object is invalid. See the debug level log for more details.',
                MIDCOM_LOG_ERROR);
            debug_print_r('Passed object was:', $virtual_group);
            debug_pop();
            return false;
        }

        if (! $virtual_group->_storage->delete())
        {
            debug_add("The virtual group {$virtual_group->id} cannot be removed, failed to delete the record: " . mgd_errstr(),
                MIDCOM_LOG_ERROR);
            debug_print_r('Passed object was:', $virtual_group);
            debug_pop();
            return false;
        }
        if (method_exists($virtual_group->_storage, 'purge'))
        {
            $virtual_group->_storage->purge();
        }

        if (array_key_exists($virtual_group->id, $this->_group_cache))
        {
            unset ($this->_group_cache[$virtual_group->id]);
        }

        debug_pop();
        return true;
    }

    /**
     * Register a virtual group in the system. This requires the privilege
     * midcom:vgroup_register assigned to the user (there is no content object checked).
     *
     * The member listing is retrieved by the callback
     * midcom_baseclasses_components_interface::_on_retrieve_vgroup_members().
     *
     * @param string $component The name to register a virtual group for.
     * @param string $identifier The component-local identifier of the virtual group.
     * @param string $name The clear-text name of the virtual group.
     * @return bool Indicating success.
     */
    function register_virtual_group($component, $identifier, $name)
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        $this->require_user_do('midcom:vgroup_register', 'You need the privilege "midcom:vgroup_register" to register any virtual group.');

        // Check whether we have a valid component URL here
        // Note, just trigger loading it is not an option, as this function might be
        // called during component loading itself.
        if (! $_MIDCOM->componentloader->validate_url($component))
        {
            debug_add("Failed to register the vgroup {$component}-{$identifier} ({$name}), the component name is invalid.",
                MIDCOM_LOG_ERROR);
            debug_add('The identifier must match the regular expression ^[a-z0-9]+$.');
            debug_pop();
            return false;
        }

        if (! preg_match('/^[a-z0-9]+$/', $identifier))
        {
            debug_add("Failed to register the vgroup {$component}-{$identifier} ({$name}), the identifier is invalid.",
                MIDCOM_LOG_ERROR);
            debug_add('The identifier must match the regular expression ^[a-z0-9]+$.');
            debug_pop();
            return false;
        }

        // Check if the group does already exist
        $qb = new midgard_query_builder('midcom_core_group_virtual_db');
        $qb->add_constraint('component', '=', $component);
        $qb->add_constraint('identifier', '=', $identifier);
        $tmp = @$qb->execute();

        if (   is_array($tmp)
            && count($tmp) > 0)
        {
            debug_add("Failing silently to register the vgroup {$component}-{$identifier} ({$name}), the group does already exist.",
                MIDCOM_LOG_INFO);
            debug_print_r('Resultset was:', $tmp);
            debug_pop();
            return true;
        }

        $obj = new midcom_core_group_virtual_db();
        $obj->component = $component;
        $obj->identifier = $identifier;
        $obj->name = $name;

        debug_print_r('Trying to create this Virtual Group:', $obj);

        if (   ! $obj->create()
            || ! $obj->id)
        {
            debug_add("Failed to register the vgroup {$component}-{$identifier} ({$name}), could not create the database record: " . mgd_errstr(),
                MIDCOM_LOG_ERROR);
            debug_print_r('Tried to create this record:', $obj);
            debug_pop();
            return false;
        }

        debug_pop();
        return true;
    }

    /**
     * This call tells the backend to log in.
     */
    function login($username, $password)
    {
        return $this->_auth_backend->create_login_session($username, $password);
    }

    /**
     * This call tells the backend to clear any authentication state, then relocates the
     * user to the sites' root URL, so that a new, unauthenticated request is started there.
     * If there was no user authenticated, the relocate is done nevertheless.
     *
     * It is optionally possible to override the destination set.
     *
     * @param string $destination The destination to relocate to after logging out.
     */
    function logout($destination = '')
    {
        $this->drop_login_session();
        $_MIDCOM->relocate($destination);
    }

    /**
     * This is a limited version of logout: It will just drop the current login session, but keep
     * the request running. This means, that the current request will stay authenticated, but
     * any subsequent requests not.
     *
     * Note, that this call will also drop any information in the PHP Session (if exists). This will
     * leave the request in a clean state after calling this function.
     */
    function drop_login_session()
    {
        if (is_null($this->_auth_backend->user))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('The backend has no authenticated user set, so we should be fine, doing the relocate nevertheless though.');
            debug_pop();
        }
        else
        {
            $this->_auth_backend->logout();
        }

        // Kill the session forcibly:
        @session_start();
        $_SESSION = Array();
        session_destroy();
    }

    function _generate_http_response()
    {
        if (headers_sent())
        {
            // We have sent output to browser already, skip setting headers
            return false;
        }

        switch ($GLOBALS['midcom_config']['auth_login_form_httpcode'])
        {
            case 200:
                header('HTTP/1.0 200 OK');
                break;

            case 403:
            default:
                header('HTTP/1.0 403 Forbidden');
                break;
        }
    }

    /**
     * This is called by $_MIDCOM->generate_error(MIDCOM_ERRFORBIDDEN, ...) if and only if
     * the headers have not yet been sent. It will display the error message and appends the
     * login form below it.
     *
     * The function will clear any existing output buffer, and the sent page will have the
     * 403 - Forbidden HTTP Status. The login will relocate to the same URL, so it should
     * be mostly transparent.
     *
     * The login message shown depends on the current state:
     * - If an authentication attempt was done but failed, an appropriated wrong user/password
     *   message is shown.
     * - If the user is authenticated, a note that he might have to switch to a user with more
     *   privileges is shown.
     * - Otherwise, no message is shown.
     *
     * This function will exit() unconditionally.
     *
     * If the style element <i>midcom_services_auth_access_denied</i> is defined, it will be shown
     * instead of the default error page. The following variables will be available in the local
     * scope:
     *
     * $title contains the localized title of the page, based on the 'access denied' string ID of
     * the main MidCOM L10n DB. $message will contain the notification what went wrong and
     * $login_warning will notify the user of a failed login. The latter will either be empty
     * or enclosed in a paragraph with the CSS ID 'login_warning'.
     *
     * @link http://www.midgard-project.org/midcom-permalink-c5e99db3cfbb779f1108eff19d262a7c further information about how to style these elements.
     * @param string $message The message to show to the user.
     */
    function access_denied($message)
    {
        debug_push(__CLASS__, __FUNCTION__);

        debug_print_function_stack("access_denied was called from here:");

        // Determine login message
        $login_warning = '';
        if (! is_null($this->user))
        {
            // The user has insufficient privileges
            $login_warning = $_MIDCOM->i18n->get_string('login message - insufficient privileges', 'midcom');
        }
        else if ($this->auth_credentials_found)
        {
            $login_warning = $_MIDCOM->i18n->get_string('login message - user or password wrong', 'midcom');
        }

        $title = $_MIDCOM->i18n->get_string('access denied', 'midcom');

        // Emergency check, if headers have been sent, kill MidCOM instantly, we cannot output
        // an error page at this point (dynamic_load from site style? Code in Site Style, something
        // like that)
        if (headers_sent())
        {
            debug_add('Cannot render an access denied page, page output has already started. Aborting directly.', MIDCOM_LOG_INFO);
            echo "<br />{$title}: {$login_warning}";
            $_MIDCOM->finish();
            debug_add("Emergency Error Message output finished, exiting now", MIDCOM_LOG_DEBUG);
            exit();
        }

        // Drop any output buffer first, hack this into the content cache.
        while (@ob_end_clean())
            // Empty Loop
        ;
        $_MIDCOM->cache->content->_obrunning = false;

        $this->_generate_http_response();

        $_MIDCOM->cache->content->no_cache();


        if (   function_exists('mgd_is_element_loaded')
            && mgd_is_element_loaded('midcom_services_auth_access_denied'))
        {
            // Pass our local but very useful variables on to the style element
            $GLOBALS['midcom_services_auth_access_denied_message'] = $message;
            $GLOBALS['midcom_services_auth_access_denied_title'] = $title;
            $GLOBALS['midcom_services_auth_access_denied_login_warning'] = $login_warning;
            mgd_show_element('midcom_services_auth_access_denied');
        }
        else
        {
            $_MIDCOM->add_link_head
            (
                array
                (
                    'rel' => 'stylesheet',
                    'type' => 'text/css',
                    'href' => MIDCOM_STATIC_URL.'/midcom.services.auth/style.css',
                )
            );
            echo '<?'.'xml version="1.0" encoding="ISO-8859-1"?'.">\n";
            ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
    <head>
        <title><?php echo $title; ?></title>
        <?php echo $_MIDCOM->print_head_elements(); ?>
    </head>

    <body onload="self.focus();document.midcom_services_auth_frontend_form.username.focus();">
        <div id="container">
            <div id="branding">
                <div id="title"><h1>Midgard CMS</h1><h2><?php echo $title; ?></h2></div>
                <div id="grouplogo"><a href="http://www.midgard-project.org/"><img src="<?php echo MIDCOM_STATIC_URL; ?>/midcom.services.auth/images/midgard-project.gif" width="104" height="104" /></a></div>
            </div>
            <div class="clear"></div>
            <div id="content">
                <div id="login">
                    <?php
                    $_MIDCOM->auth->show_login_form();
                    ?>
                    <div class="clear"></div>
                </div>

                <div id="error"><?php echo "<div>{$login_warning}</div><div>{$message}</div>"; ?></div>
            </div>

            <div id="bottom">
                <div id="version">version <?php echo mgd_version(); ?></div>
            </div>

            <div id="footer">
                <div class="midgard">
                    Copyright &copy; 1998-2006 <a href="http://www.midgard-project.org/">The Midgard Project</a>. Midgard is <a href="http://en.wikipedia.org/wiki/Free_software">free software</a> available under <a href="http://www.gnu.org/licenses/lgpl.html">GNU Lesser General Public License</a>.
                </div>
                <div class="server">
                    <?php echo "{$_SERVER['SERVER_NAME']}: {$_SERVER['SERVER_SOFTWARE']}"; ?>
                </div>
            </div>
    </body>
</html>
            <?php
        }
        $_MIDCOM->finish();
        debug_add("Error Page output finished, exiting now", MIDCOM_LOG_DEBUG);
        exit();
    }

    /**
     * This function should be used to render the main login form. This does only include the form,
     * no heading or whatsoever.
     *
     * It is recommended to call this function only as long as the headers are not yet sent (which
     * is usually given thanks to MidCOMs output buffering).
     *
     * What gets rendered depends on the authentication frontend, but will usually be some kind
     * of form. The output from the frontend is surrounded by a div tag whose CSS ID is set to
     * 'midcom_login_form'.
     *
     * @link http://www.midgard-project.org/midcom-permalink-c5e99db3cfbb779f1108eff19d262a7c further information about how to style these elements.
     */
    function show_login_form()
    {
        echo "<div id='midcom_login_form'>\n";
        $this->_auth_frontend->show_authentication_form();
        echo "</div>\n";
    }

    /**
     * This will show a complete login page unconditionally and exit afterwards.
     * If the current style has an element called <i>midcom_services_auth_login_page</i>
     * it will be shown instead. The local scope will contain the two variables
     * $title and $login_warning. $title is the localized string 'login' from the main
     * MidCOM L10n DB, login_warning is empty unless there was a failed authentication
     * attempt, in which case it will have a localized warning message enclosed in a
     * paragraph with the ID 'login_warning'.
     *
     * @link http://www.midgard-project.org/midcom-permalink-c5e99db3cfbb779f1108eff19d262a7c further information about how to style these elements.
     */
    function show_login_page()
    {
        debug_push(__CLASS__, __FUNCTION__);

        // Drop any output buffer first, hack this into the content cache.
        while (@ob_end_clean())
            // Empty Loop
        ;

        $this->_generate_http_response();

        $_MIDCOM->cache->content->_obrunning = false;
        $_MIDCOM->cache->content->no_cache();

        $title = $_MIDCOM->i18n->get_string('login', 'midcom');

        // Determine login warning so that wrong user/pass is shown.
        $login_warning = '';
        if (   $this->auth_credentials_found
            && is_null($this->user))
        {
            $login_warning = $_MIDCOM->i18n->get_string('login message - user or password wrong', 'midcom');
        }


        if (   function_exists('mgd_is_element_loaded')
            && mgd_is_element_loaded('midcom_services_auth_login_page'))
        {
            // Pass our local but very useful variables on to the style element
            $GLOBALS['midcom_services_auth_show_login_page_title'] = $title;
            $GLOBALS['midcom_services_auth_show_login_page_login_warning'] = $login_warning;
            mgd_show_element('midcom_services_auth_login_page');
        }
        else
        {
            $_MIDCOM->add_link_head
            (
                array
                (
                    'rel' => 'stylesheet',
                    'type' => 'text/css',
                    'href' => MIDCOM_STATIC_URL.'/midcom.services.auth/style.css',
                )
            );
            echo '<?'.'xml version="1.0" encoding="ISO-8859-1"?'.">\n";
            ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
    <head>
        <title><?php echo $title; ?></title>
        <?php echo $_MIDCOM->print_head_elements(); ?>
    </head>

    <body onload="self.focus();document.midcom_services_auth_frontend_form.username.focus();">
        <div id="container">
            <div id="branding">
                <div id="title"><h1>Midgard CMS</h1><h2><?php echo $title; ?></h2></div>
                <div id="grouplogo"><a href="http://www.midgard-project.org/"><img src="<?php echo MIDCOM_STATIC_URL; ?>/midcom.services.auth/images/midgard-project.gif" width="104" height="104" /></a></div>
            </div>
            <div class="clear"></div>
            <div id="content">
                <div id="login">
                    <?php
                    $_MIDCOM->auth->show_login_form();
                    ?>
                    <div class="clear"></div>
                </div>
                <?php
                if ($login_warning == '')
                {
                    echo "<div id=\"ok\">" . $_MIDCOM->i18n->get_string('login message - please enter credentials', 'midcom') . "</div>\n";
                }
                else
                {
                    echo "<div id=\"error\">{$login_warning}</div>\n";
                }
                ?>
            </div>

            <div id="bottom">
                <div id="version">version <?php echo mgd_version(); ?></div>
            </div>

            <div id="footer">
                <div class="midgard">
                    Copyright &copy; 1998-2006 <a href="http://www.midgard-project.org/">The Midgard Project</a>. Midgard is <a href="http://en.wikipedia.org/wiki/Free_software">free software</a> available under <a href="http://www.gnu.org/licenses/lgpl.html">GNU Lesser General Public License</a>.
                </div>
                <div class="server">
                    <?php echo "{$_SERVER['SERVER_NAME']}: {$_SERVER['SERVER_SOFTWARE']}"; ?>
                </div>
            </div>
    </body>
    <?php
    $_MIDCOM->uimessages->show();
    ?>
</html>
            <?php
        }
        $_MIDCOM->finish();
        exit();
    }

    /**
     * Returns the system-wide basic privilege set.
     *
     * @return Array Privilege Name / Value map.
     */
    function get_default_privileges()
    {
        return $this->_default_privileges;
    }

    /**
     * Returns the system-wide basic owner privilege set.
     *
     * @return Array Privilege Name / Value map.
     */
    function get_owner_default_privileges()
    {
        return $this->_owner_default_privileges;
    }

    /**
     * This helper function loads and prepares the list of class magic privileges for
     * usage. It will assign them to the $_*_default_class_privileges members.
     *
     * @param MidcomDBAObject $classname An instance of the object for which the class defaults should be
     *     loaded. Be aware, that this may be a simply a default constructed class instance.
     * @access private
     */
    function _load_class_magic_privileges(&$class)
    {
        // Check if we have loaded these privileges already...
        if (!isset($class->__new_class_name__))
        {
            $loadable_class = get_class($class);
        }
        else
        {
            $loadable_class = $class->__new_class_name__;
        }

        if (array_key_exists($loadable_class, $this->_default_magic_class_privileges))
        {
            return;
        }

        if (!method_exists($class, 'get_class_magic_default_privileges'))
        {
            $this->_default_magic_class_privileges[$loadable_class] = array
            (
                'EVERYONE' => array(),
                'ANONYMOUS' => array(),
                'USERS' => array()
            );
            return;
        }

        $privs = $class->get_class_magic_default_privileges();
        $this->_default_magic_class_privileges[$loadable_class] = $privs;

        return;
    }

}

?>