<?php
/**
 * @package midcom.helper.replicator
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: cleartokens.php,v 1.1 2006/03/27 14:10:29 rambo Exp $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Cron handler for queuing objects edited outside midcom DBA.
 * @package midcom.helper.replicator
 */
class midcom_helper_replicator_cron_queuenondba extends midcom_baseclasses_components_cron_handler
{
    var $_qm = false;
    var $skip_classes = array
    (
        'midcom_helper_replicator_subscription',
        'midcom_services_at_entry',
    );

    function _on_initialize()
    {
        return true;
    }

    /**
     * Process the replication queue
     */
    function _on_execute()
    {
        // TODO: Figure out a better way to handle these
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', -1);
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add('_on_execute called');
        $this->_qm =& midcom_helper_replicator_queuemanager::get();
        if (!is_a($this->_qm, 'midcom_helper_replicator_queuemanager'))
        {
            // This is fatal enough error to make extra noise of
            $msg = 'Could not instantiate queue manager';
            $this->print_error($msg);
            debug_add($msg, MIDCOM_LOG_ERROR);
            debug_pop();
            return;
        }
        if (!$this->_qm->can_add_to_queue())
        {
            debug_add("We don't have any usable subscriptions, lets not waste resources any further", MIDCOM_LOG_INFO);
            debug_pop();
            return;
        }

        foreach($_MIDGARD['schema']['types'] as $mgdschema_class => $dummy)
        {
            if (empty($mgdschema_class))
            {
                // safety
                continue;
            }
            if (in_array($mgdschema_class, $this->skip_classes))
            {
                continue;
            }

            $this->queue_type($mgdschema_class);
        }

        debug_pop();
        return;
    }

    /**
     * Queue all objects in particular MgdSchema class
     *
     * @todo Is there a sensible way to order the objects so that we do not get dependency problems when importing
     */
    function queue_type($mgdschema_classname)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("queue_type called for {$mgdschema_classname}");
        // We must use core QB since we need deletes
        $qb = new midgard_query_builder($mgdschema_classname);
        // Only dump objects in current SG, otherwise SG0 objects will cause issues...
        $qb->add_constraint('sitegroup', '=', $_MIDGARD['sitegroup']);
        // Revised within last day
        $qb->add_constraint('metadata.revised', '>=', gmdate('Y-m-d H:i:s', mktime(0,0,1,date('m'), date('j')-1, date('Y'))));
        // Not exported within last day (revised vs exported check is made later on PHP level, QB can't do it for us)
        /* We can't do this here since there is the possibility of object being edited, exported and then edited again in the same time window...
        $qb->add_constraint('metadata.exported', '<=', gmdate('Y-m-d H:i:s', mktime(0,0,1,date('m'), date('j')-1, date('Y'))));
        */
        // Get deletes as well
        $qb->include_deleted();
        $objects = $qb->execute();
        unset($qb);
        if (!is_array($objects))
        {
            // This is likely to throw up warnings etc anyway so not calling print_error.
            debug_add("QB failed fatally for class {$mgdschema_classname}", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        foreach ($objects as $k => $object)
        {
            if (   empty($object)
                || !is_object($object))
            {
                debug_add("given object is not object, skipping", MIDCOM_LOG_WARN);
                continue;
            }
            if (   !empty($object->metadata->exported)
                && $object->metadata->exported != '0000-00-00 00:00:00'
                && !empty($object->metadata->revised)
                && $object->metadata->revised != '0000-00-00 00:00:00'
                && strtotime($object->metadata->exported) >= strtotime($object->metadata->revised))
            {
                debug_add("Object {$mgdschema_classname} {$object->guid} is already exported ({$object->metadata->exported} >= {$object->metadata->revised}), skipping");
                continue;
            }

            debug_add("Queuing object {$mgdschema_classname} {$object->guid}");
            // PONDER: Some point in the future we may need to cast to DBA objects, but with deleted ones it's *really hard*
            if (!$this->_qm->add_to_queue($object))
            {
                debug_add("Failed to queue object {$mgdschema_classname} {$object->guid}", MIDCOM_LOG_ERROR);
            }

            unset($objects[$k]);
        }
        unset($objects);
        return true;
    }
}
?>