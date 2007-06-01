<?php
/**
 * @package midcom.helper.replicator
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: cleartokens.php,v 1.1 2006/03/27 14:10:29 rambo Exp $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Cron handler for processing the replication queue
 * @package midcom.helper.replicator
 */
class midcom_helper_replicator_cron_processqueue extends midcom_baseclasses_components_cron_handler
{
    function _on_initialize()
    {
        return true;
    }

    /**
     * Process the replication queue
     */
    function _on_execute()
    {
        /*
        //Disable limits
        @ini_set('memory_limit', -1);
        @ini_set('max_execution_time', 0);
        */
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add('_on_execute called');
        $qm =& midcom_helper_replicator_queuemanager::get();
        if (!is_a($qm, 'midcom_helper_replicator_queuemanager'))
        {
            debug_add('Could not instantiate queue manager', MIDCOM_LOG_ERROR);
            debug_pop();
            return;
        }
        if (!$qm->process_queue())
        {
            debug_add('process_queue() returned failure', MIDCOM_LOG_ERROR);
            debug_pop();
            return;
        }

        debug_pop();
        return;
    }
}
?>