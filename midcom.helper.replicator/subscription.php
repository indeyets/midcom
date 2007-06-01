<?php
/**
 * @package midcom.helper.replicator
 * @author Nemein Oy, http://www.nemein.com/
 * @version $Id: subscription.php,v 1.4 2006/05/11 15:43:12 rambo Exp $
 * @copyright Nemein Oy, http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * Midcom wrapped base class, keep logic here
 *
 * @package midcom.helper.replicator
 */
class midcom_helper_replicator_subscription_dba extends __midcom_helper_replicator_subscription_dba
{
    function midcom_helper_replicator_subscription_dba($id = null)
    {
        return parent::__midcom_helper_replicator_subscription_dba($id);
    }
}
?>