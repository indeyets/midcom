<?php
/**
 * @package midcom_core
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Authorization interface for MidCOM 3
 *
 * @package midcom_core
 */
interface midcom_core_services_authorization
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
    public function can_do($privilege, $object, $user = null);

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
    public function require_user_do($privilege, $message = null, $class = null);
    
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
    function can_user_do($privilege, $user = null, $class = null, $component = null);
}
?>