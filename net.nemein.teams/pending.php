<?php
/**
 * @package net.nemein.teams
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * @package net.nemein.teams
 */
class net_nemein_teams_pending_dba extends __net_nemein_teams_pending_dba
{

    function __construct($src = null)
    {
        parent::__construct($src);
    }

    /**
     * DBA magic defaults which assign write privileges for all USERS, so that they can freely
     * create mails without the need to sudo of the component. Also, we deny read unconditionally,
     * as read privileges are set during creation for the sender, and are inherited from the
     * mailbox for the receiver.
     */
    function get_class_magic_default_privileges()
    {
        return Array (
            'EVERYONE' => Array('midgard:read' => MIDCOM_PRIVILEGE_DENY),
            'ANONYMOUS' => Array(),
            'USERS' => Array('midgard:create' => MIDCOM_PRIVILEGE_ALLOW),
        );
    }
}

?>
