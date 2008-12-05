<?php
/**
 * @package midcom_core
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Simple authorization interface for MidCOM 3. Unauthenticated users are given read access,
 * and authenticated users write access.
 *
 * @package midcom_core
 */
class midcom_core_services_authorization_simple implements midcom_core_services_authorization
{
    /**
     * Starts up the authorization service and connects to various signals
     */
    public function __construct()
    {
        // Note: Signals implementation is not reliable in earlier builds so we won't use it
        if (version_compare(mgd_version(), '1.9.0alpha0+svn2008022807', '>='))
        {
            //foreach ($_MIDGARD['schema']['types'] as $classname => $null)
            //{
            //    $this->connect_to_signals($classname);
            //}
        }
    }
    
    private function connect_to_signals($class)
    {
        if (!isset($_MIDGARD['schema']['types'][$class]))
        {
            throw new Exception("{$class} is not an MgdSchema class");
        }        
        midgard_object_class::connect_default($class, 'action_loaded_hook', array($this, 'on_loaded'), array());
        midgard_object_class::connect_default($class, 'action_create_hook', array($this, 'on_creating'), array());
        midgard_object_class::connect_default($class, 'action_update_hook', array($this, 'on_updating'), array());
        midgard_object_class::connect_default($class, 'action_delete_hook', array($this, 'on_deleting'), array());        
    }

    public function on_loaded($object, $args)
    {
        if (!$this->can_do('midgard:read', $object))
        {
            throw new midcom_exception_unauthorized("Not authorized to read {$object->guid}");
        }
    }
   
    public function on_creating($object, $args)
    {
        if (!$this->can_do('midgard:create', $object))
        {
            throw new midcom_exception_unauthorized("Not authorized to create {$object->guid}");
        }
    }
    
    public function on_updating($object, $args)
    {
        if (!$this->can_do('midgard:update', $object))
        {
            throw new midcom_exception_unauthorized("Not authorized to update {$object->guid}");
        }
    }
    
    public function on_deleting($object, $args)
    {
        if (!$this->can_do('midgard:delete', $object))
        {
            throw new midcom_exception_unauthorized("Not authorized to delete {$object->guid}");
        }
    }
    
    /**
     * Checks whether a user has a certain privilege on the given content object.
     * Works on the currently authenticated user by default, but can take another
     * user as an optional argument.
     *
     * @param string $privilege The privilege to check for
     * @param MidgardObject &$content_object A Midgard Content Object
     * @param midcom_core_user $user The user against which to check the privilege, defaults to the currently authenticated user.
     *     You may specify "EVERYONE" instead of an object to check what an anonymous user can do.
     * @return boolean true if the privilege has been granted, false otherwise.
     */
    public function can_do($privilege, $object, $user = null)
    {
        if ($privilege == 'midgard:read')
        {
            return true;
        }
        
        if ($_MIDGARD['user'])
        {
            return true;
        }
        
        return false;
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
                $message = "access denied: privilege {$privilege} not granted";
            }
            throw new Exception($message);
            // This will exit.
        }
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
     * @return boolean True if the privilege has been granted, false otherwise.
     */
    function can_user_do($privilege, $user = null, $class = null, $component = null)
    {
        return $this->can_do($privilege, $class, $user);
    }
}
?>