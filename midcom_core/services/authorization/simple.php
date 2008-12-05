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
        if (version_compare(mgd_version(), '1.9.0alpha0+svn2008031408', '>='))
        {
            foreach ($_MIDGARD['schema']['types'] as $classname => $null)
            {
                $this->connect_to_signals($classname);
            }
        }
    }
    
    private function connect_to_signals($class)
    {
        if (!isset($_MIDGARD['schema']['types'][$class]))
        {
            throw new Exception("{$class} is not an MgdSchema class");
        }
        midgard_object_class::connect_default($class, 'action-loaded-hook', array($this, 'on_loaded'), array($class));
        midgard_object_class::connect_default($class, 'action-create-hook', array($this, 'on_creating'), array($class));
        midgard_object_class::connect_default($class, 'action-update-hook', array($this, 'on_updating'), array($class));
        midgard_object_class::connect_default($class, 'action-delete-hook', array($this, 'on_deleting'), array($class));
    }

    public function on_loaded($object, $params)
    {
        if (!$_MIDCOM->authorization->can_do('midgard:read', $object))
        {
            // Note: this is a *hook* so the object is still empty
            throw new midcom_exception_unauthorized("Not authorized to read " . get_class($object));
        }
    }
   
    public function on_creating($object, $params)
    {
        if (!$_MIDCOM->authorization->can_do('midgard:create', $object))
        {
            throw new midcom_exception_unauthorized("Not authorized to create " . get_class($object) . " {$object->guid}");
        }
    }
    
    public function on_updating($object, $params)
    {
        if (!$_MIDCOM->authorization->can_do('midgard:update', $object))
        {
            throw new midcom_exception_unauthorized("Not authorized to update " . get_class($object) . " {$object->guid}");
        }
    }
    
    public function on_deleting($object, $params)
    {
        if (!$_MIDCOM->authorization->can_do('midgard:delete', $object))
        {
            throw new midcom_exception_unauthorized("Not authorized to delete " . get_class($object) . " {$object->guid}");
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

        if ($_MIDCOM->authentication->is_user())
        {
            return true;
        }
        
        return false;
    }
    
    public function require_do($privilege, $object, $user = null)
    {
        if (!$this->can_do($privilege, $object, $user))
        {
            throw new midcom_exception_unauthorized("Not authorized to {$privilege} " . get_class($object) . " {$object->guid}");
        }
    }
}
?>