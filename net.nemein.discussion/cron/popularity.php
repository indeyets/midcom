<?php
/**
 * @package net.nemein.discussion
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Popularity Cronjob Handler
 *
 * - Invoked by daily by the MidCOM Cron Service
 * - Recalculates popularity for threads
 *
 * @package net.nemein.discussion
 */
class net_nemein_discussion_cron_popularity extends midcom_baseclasses_components_cron_handler
{
    function _on_initialize()
    {
        return true;
    }

    function _on_execute()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        if (version_compare(phpversion(), '5.0.0', '<'))
        {
            debug_add("Populatiry calculator requires PHP 5", MIDCOM_LOG_DEBUG);
            debug_pop();
            return;
        }
        require_once(MIDCOM_ROOT . '/net/nemein/discussion/calculator.php');
        $calculator = new net_nemein_discussion_calculator();

        $qb = net_nemein_discussion_thread_dba::new_query_builder();
        $qb->add_constraint('posts', '>', 0);
        $qb->add_order('metadata.score', 'DESC');
        $threads = $qb->execute();
        $threads_array = array();
        
        foreach ($threads as $thread)
        {
            $popularities = $calculator->calculate_thread($thread, true);
            debug_add("Thread #{$thread->id} {$thread->title} got popularity of {$popularities['popularity']}.");
        }

        debug_pop();
    }
}
?>