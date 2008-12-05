<?php
/**
 * @package net_nemein_notifications
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * MidCOM wrapped class for access to stored queries
 *
 * @package net_nemein_notifications
 * extends __net_nemein_notifications_notification
 */
class net_nemein_notifications_notification extends __org_openpsa_notifications_notification_dba
{
    function __construct($id = null)
    {
        return parent::__construct($id);
    }
}
?>