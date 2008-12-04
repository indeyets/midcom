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
}
?>