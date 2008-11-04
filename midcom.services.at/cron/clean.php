<?php
/**
 * @package midcom.services.at
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: check.php 3012 2006-03-02 12:07:41Z rambo $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Clears dangling "running"/failed entries
 *
 * @package midcom.services.at
 */
class midcom_services_at_cron_clean extends midcom_baseclasses_components_cron_handler
{
    function _on_initialize()
    {
        return true;
    }

    /**
     * Loads all entries that need to be processed and processes them.
     */
    function _on_execute()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add('_on_execute called');

        $qb = midcom_services_at_entry::new_query_builder();
        // (to be) start(ed) AND last touched over two days ago
        $qb->add_constraint('start', '<=', time()-3600*24*2);
        $qb->begin_group('OR');
            $qb->add_constraint('host', '=', $_MIDGARD['host']);
            $qb->add_constraint('host', '=', 0);
        $qb->end_group();
        $qb->add_constraint('metadata.revised', '<=', date('Y-m-d H:i:s', time()-3600*24*2));
        $qb->add_constraint('status', '>=', MIDCOM_SERVICES_AT_STATUS_RUNNING);
        debug_add('Executing QB');
        $_MIDCOM->auth->request_sudo('midcom.services.at');
        $qbret = $qb->execute();
        if (empty($qbret))
        {
            debug_add('Got empty resultset, exiting');
            debug_pop();
            return;
        }
        debug_add('Processing results');
        foreach($qbret as $entry)
        {
            debug_add("Deleting dangling entry #{$entry->id}\n", MIDCOM_LOG_INFO);
            debug_print_r("Entry #{$entry->id} dump: ", $entry);
            $entry->delete();
        }
        $_MIDCOM->auth->drop_sudo();
        debug_add('Done');
        debug_pop();
        return;
    }
}
?>