<?php
/**
 * @package net.nemein.approvenotifier
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: fetchplazes.php 3757 2006-07-27 14:32:42Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Cron handler for checking page expiry times and reporting on those
 * @package net.nemein.approvenotifier
 */
class net_nemein_approvenotifier_cron_checkexpiries extends midcom_baseclasses_components_cron_handler
{
    function _on_initialize()
    {
        return true;
    }

    /**
     * Checks expiry times
     */
    function _on_execute()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add('_on_execute called');

        $notifier = new net_nemein_approvenotifier();
        $nap = new midcom_helper_nav();
        $notifier->check_topic_articles($nap->get_root_node());
        
        debug_add('Done');
        debug_pop();
        return;
    }
}
?>