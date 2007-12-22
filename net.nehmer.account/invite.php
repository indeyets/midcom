<?php
/**
 * @package net.nehmer.account
 * @author Henri Bergius, http://bergie.iki.fi
 * @copyright Nemein Oy, http://www.nemein.com
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * MidCOM wrapper class for invite objects
 *
 * @package net.nehmer.account
 */
class net_nehmer_accounts_invites_invite_dba extends __net_nehmer_accounts_invites_invite_dba
{
    function net_nehmer_accounts_invites_invite_dba($src = null)
    {
        parent::__net_nehmer_accounts_invites_invite_dba($src);
    }

    function get_class_magic_default_privileges()
    {
        return Array (
            'EVERYONE' => Array(),
            'ANONYMOUS' => Array(),
            'USERS' => Array('midgard:create' => MIDCOM_PRIVILEGE_ALLOW),
        );
    }
}

?>
