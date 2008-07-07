<?php
/**
* @package midcom.helper.replicator
* @author The Midgard Project, http://www.midgard-project.org
* @version $Id: viewer.php 3975 2006-09-06 17:36:03Z bergie $
* @copyright The Midgard Project, http://www.midgard-project.org
* @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
*/

/** @ignore */
if (!class_exists('midcom_helper_replicator_exporter_mirror'))
{
    require_once('mirror.php');
}
// We need this library
$_MIDCOM->load_library('midcom.services.at');

/**
 * This class handles mirroring any content to the subscriber
 *
 * In addition, it tries to be smart about different components so that
 * their main dependencies (like root events or index articles) are
 * exported as well.
 *
 * @package midcom.helper.replicator
 */
class midcom_helper_replicator_exporter_staging2live extends midcom_helper_replicator_exporter_mirror
{
    /**
     * array of classes for which to check the approval for
     *
     * @see configuration key exporter_staging2live_check_approvals_for
     * @todo configurable on per subscription basis ?
     */
    var $check_approvals_for = array();

    function midcom_helper_replicator_exporter_staging2live($subscription)
    {
        parent::midcom_helper_replicator_exporter_mirror($subscription);
        $this->check_approvals_for = $this->_config->get('exporter_staging2live_check_approvals_for');
        if (!is_array($this->check_approvals_for))
        {
            // Safety
            $this->check_approvals_for = array();
        }
    }

    /**
     * This is the main entry point of the exporter, we'll do some approval and scheduling checks
     *
     * @param midgard_object &$object The Object to export parameters of
     * @param boolean $check_exported check if the object has already been exported or not
     * @return boolean Whether the object may be exported with this exporter
     * @todo log the debug messages to generic debug log and log only more informative reasons to object replication log
     */
    function is_exportable(&$object, $check_exported = true)
    {
        /*
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_print_r('called for', $object);
        debug_pop();
        */
        // If we already know the state return early
        if (isset($this->exportability[$object->guid]))
        {
            /*
            $GLOBALS['midcom_helper_replicator_logger']->push_prefix('exporter');
            $GLOBALS['midcom_helper_replicator_logger']->log_object($object, "\$this->exportability already set, returning " . (int)$this->exportability[$object->guid]);
            $GLOBALS['midcom_helper_replicator_logger']->pop_prefix();
            */
            return $this->exportability[$object->guid];
        }

        $GLOBALS['midcom_helper_replicator_logger']->push_prefix('exporter');

        // Otherwise start checking...
        // we need AT service
        if (!class_exists('midcom_services_at_interface'))
        {
            $GLOBALS['midcom_helper_replicator_logger']->log_object($object, 'midcom.services.at library not available, cannot handle scheduling, aborting export');
            $this->exportability[$object->guid] = false;
            $GLOBALS['midcom_helper_replicator_logger']->pop_prefix();
            return $this->exportability[$object->guid];
        }

        // Check baseline checks first
        if (!$this->is_exportable_baseline($object))
        {
            return false;
        }

        // Then default to true
        $this->exportability[$object->guid] = true;

        // We inherit some basic exportability checks
        if ($check_exported)
        {
            // CAVEAT: may cause issues with multiple subscriptions
            //$GLOBALS['midcom_helper_replicator_logger']->log_object($object, "BEFORE is_exportable_by_metadata check: exportability = " . (int)$this->exportability[$object->guid]);
            $this->exportability[$object->guid] = parent::is_exportable_by_metadata(&$object);
            //$GLOBALS['midcom_helper_replicator_logger']->log_object($object, "AFTER is_exportable_by_metadata check: exportability = " . (int)$this->exportability[$object->guid]);
        }

        // Approvals checks
        //$GLOBALS['midcom_helper_replicator_logger']->log_object($object, "BEFORE approval check: exportability = " . (int)$this->exportability[$object->guid]);
        $this->_check_approval($object);
        //$GLOBALS['midcom_helper_replicator_logger']->log_object($object, "AFTER approval check: exportability = " . (int)$this->exportability[$object->guid]);

        /**
         * Check scheduling, muck object->deleted and register AT service handlers as needed
         *
         */
        // FIXME: use strtotime when MidCOM stops automagically rewriting these between ISO and Unix timestamps
        $schedule_action = 'ok';
        if (   $object->metadata->schedulestart != 0
            && !preg_match('%[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}%', $object->metadata->schedulestart))
        {
            $schedulestart_unixtime = strtotime($object->metadata->schedulestart);
        }
        else
        {
            $schedulestart_unixtime = $object->metadata->schedulestart;
        }
        if (   $object->metadata->scheduleend != 0
            && !preg_match('%[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}%', $object->metadata->scheduleend))
        {
            $scheduleend_unixtime = strtotime($object->metadata->scheduleend);
        }
        else
        {
            $scheduleend_unixtime = $object->metadata->scheduleend;
        }
        if ($schedulestart_unixtime)
        {
            $GLOBALS['midcom_helper_replicator_logger']->log_object($object, 'Has schedulestart set to ' . strftime('%x %X', $schedulestart_unixtime));
        }
        if ($scheduleend_unixtime)
        {
            $GLOBALS['midcom_helper_replicator_logger']->log_object($object, 'Has scheduleend set to ' . strftime('%x %X', $scheduleend_unixtime));
        }

        if (   $schedulestart_unixtime > time()
            && $this->exportability[$object->guid] == true)
        {
            // Object is exportable but has scheduling in future we register at run and export later
            $schedule_action = 'not_yet';
        }

        if ($scheduleend_unixtime > 0)
        {
            // Object has expiry information

            if ($scheduleend_unixtime <= time())
            {
                // Regardless of exportability, if object's schedule has passed we mark it deleted and export
                // FIXME: this delete may be exported already. How to prevent running this every time?
                $schedule_action = 'expired';
            }
            elseif ($schedule_action != 'not_yet')
            {
                // Object has expiry in future, so we allow exporting now and register at run to export later
                $schedule_action = 'ok';
            }
        }
        //$GLOBALS['midcom_helper_replicator_logger']->log_object($object, "BEFORE schedule check: \$schedule_action = {$schedule_action}, exportability = " . (int)$this->exportability[$object->guid]);
        switch ($schedule_action)
        {
            case 'ok':
                // Object is in schedule, passthru as is and schedule a replication for expire
                /* REMINDER: Do not arbitarily set this to true, it might be false for other reasons
                $this->exportability[$object->guid] = true;
                */
                // Shedule expires only if we would export the object (otherwise there will soon be a ton of expiry runs in DB)
                if (   $scheduleend_unixtime > 0
                    && $this->exportability[$object->guid] == true)
                {
                    // Schedule replication after expiry time
                    $GLOBALS['midcom_helper_replicator_logger']->log_object($object, 'Scheduling expiry exportation at ' . strftime('%x %X', $schedulestart_unixtime), MIDCOM_LOG_INFO);
                    $this->at_queue_guid_sweepnclean($object);
                    if (!midcom_services_at_interface::register($scheduleend_unixtime+1, 'midcom.helper.replicator', 'at_queue_guid', array('guid' => $object->guid)))
                    {
                        $GLOBALS['midcom_helper_replicator_logger']->log_object($object, 'Failed to schedule expiry exportation, errstr: ' . mgd_errstr(), MIDCOM_LOG_ERROR);
                    }
                }
                break;
            case 'expired':
                // Object has expired, simulate a delete and pass trhough
                $GLOBALS['midcom_helper_replicator_logger']->log_object($object, 'Has expired, rewriting to delete and exporting', MIDCOM_LOG_INFO);
                $this->_serialize_rewrite_to_delete[$object->guid] = true;
                // We must be able to export the simulated delete so force exportability to true
                $this->exportability[$object->guid] = true;
                break;
            case 'not_yet':
                // Object is not yet exportable, skip export and schedule replication at the correct time
                $this->exportability[$object->guid] = false;
                $GLOBALS['midcom_helper_replicator_logger']->log_object($object, 'Is scheduled to appear later, skipping for now', MIDCOM_LOG_INFO);
                $GLOBALS['midcom_helper_replicator_logger']->log_object($object, 'Scheduling export for later at ' . strftime('%x %X', $schedulestart_unixtime), MIDCOM_LOG_INFO);
                $this->at_queue_guid_sweepnclean($object);
                if (!midcom_services_at_interface::register($schedulestart_unixtime+1, 'midcom.helper.replicator', 'at_queue_guid', array('guid' => $object->guid)))
                {
                    $GLOBALS['midcom_helper_replicator_logger']->log_object($object, 'Failed to schedule exportation, errstr: ' . mgd_errstr(), MIDCOM_LOG_ERROR);
                }
                break;
        }
        //$GLOBALS['midcom_helper_replicator_logger']->log_object($object, "AFTER schedule check: exportability = " . (int)$this->exportability[$object->guid]);

        //$GLOBALS['midcom_helper_replicator_logger']->log_object($object, "is_exportable checks done, returning " . (int)$this->exportability[$object->guid]);
        $GLOBALS['midcom_helper_replicator_logger']->pop_prefix();
        return $this->exportability[$object->guid];
    }

    function _check_approval(&$object)
    {
        static $parent_check_stack = array();
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("Called for {$object->guid}");
        //$GLOBALS['midcom_helper_replicator_logger']->log_object($object, "_check_approval called");

        // Do not check approvals for deletes
        if (   $object->metadata->deleted
            || (   isset($this->_serialize_rewrite_to_delete[$object->guid])
                && $this->_serialize_rewrite_to_delete[$object->guid]))
        {
            $msg = 'Object is deleted, do not check approvals, setting exportability explicitly to true';
            $this->exportability[$object->guid] = true;
            $GLOBALS['midcom_helper_replicator_logger']->log_object($object, $msg, MIDCOM_LOG_INFO);
            debug_add($msg);
            debug_pop();
            return;
        }

        /* Objects to check approvals for */
        $approvals_check_continue = false;
        foreach ($this->check_approvals_for as $class)
        {
            if (is_a($object, $class))
            {
                $approvals_check_continue = true;
                break;
            }
        }
        if (!$approvals_check_continue)
        {
            $object_class = get_class($object);
            $msg = "Not checking approvals for class {$object_class}";
            $GLOBALS['midcom_helper_replicator_logger']->log_object($object, $msg);
            debug_add($msg);
            debug_pop();
            return;
        }

        if (   is_a($object, 'midgard_article')
            && !empty($object->up))
        {
            // Child articles don't currently have any approval UI, skip approval for them (but do check parent below)
            $GLOBALS['midcom_helper_replicator_logger']->log_object($object, 'Reply article, not checking approval status', MIDCOM_LOG_INFO);
            $this->exportability[$object->guid] = true;
        }
        elseif ($object->metadata->revised > $object->metadata->approved)
        {
            // FIXME: use metadata service (?)
            // Not approved since last update
            $GLOBALS['midcom_helper_replicator_logger']->log_object($object, 'Object could not be exported for replication because it\'s not approved', MIDCOM_LOG_WARN);
            $this->exportability[$object->guid] = false;
        }

        if ($this->exportability[$object->guid] == true)
        {
            // Check the parent also, as that may be unapproved
            $parent = $object->get_parent();
            if (   is_object($parent)
                && isset($parent->guid)
                && !empty($parent->guid))
            {
                array_push($parent_check_stack, $object->guid);
                $this->exportability[$object->guid] = $this->is_exportable($parent, false);
                array_pop($parent_check_stack);
                if (!$this->exportability[$object->guid])
                {
                    //$GLOBALS['midcom_helper_replicator_logger']->log_object($object, 'Parent not approved, skipping');
                    if (empty($parent_check_stack))
                    {
                        // Empty stack means we're  not inside parent recursion, thus we can raise an UIMessage
                        $ref = new midcom_helper_reflector($object);
                        $_MIDCOM->uimessages->add
                        (
                            $this->_l10n->get('midcom.helper.replicator'),
                            sprintf
                            (
                                $this->_l10n->get('%s %s could not be exported for replication because one of its parents is not approved'), 
                                $ref->get_class_label(),
                                $ref->get_object_label($object)
                            ),
                            'warning'
                        );
                        unset($ref);
                        $GLOBALS['midcom_helper_replicator_logger']->log_object($object, 'Object could not be exported for replication because one of its parents is not approved', MIDCOM_LOG_WARN);
                    }
                }
            }
        }
        debug_pop();
    }


    /**
     * "Sweep'n'Clean" of previously registered at_queue_guid runs for the object
     *
     * @param midgard_object $object
     */
    function at_queue_guid_sweepnclean($object)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $GLOBALS['midcom_helper_replicator_logger']->log_object($object, "at_queue_guid_sweepnclean called");
        debug_add("Called for {$object->guid}");
        $qb = midcom_services_at_entry::new_query_builder();
        $qb->add_constraint('component', '=', 'midcom.helper.replicator');
        $qb->add_constraint('method', '=', 'at_queue_guid');
        $qb->add_constraint('argumentsstore', '=', serialize(array('guid' => $object->guid)));
        $old_entries = $qb->execute();
        foreach ($old_entries as $entry)
        {
            debug_add("Found old entry #{$entry->id}");
            if (!$entry->delete())
            {
                debug_add("Failed to delete old entry #{$entry->id}, errstr: " . mgd_errstr(), MIDCOM_LOG_WARN);
            }
        }
        debug_pop();
    }


    /**
     * Export some children of the object
     *
     * @param midgard_object &$object The Object to export parameters of
     * @return array Array of exported objects as XML indexed by GUID
     * @todo rethink child/parent handling
     */
    function serialize_children(&$object)
    {
        //$GLOBALS['midcom_helper_replicator_logger']->log_object($object, "serialize_children called");
        $serializations = array();
        if (   $object->metadata->deleted
            || (   isset($this->_serialize_rewrite_to_delete[$object->guid])
                && $this->_serialize_rewrite_to_delete[$object->guid]))
        {
            // Object is deleted (either for real or just simulated), return early
            debug_push_class(__CLASS__, __FUNCTION__);
            $object_class = get_class($object);
            debug_add("Object {$object_class} {$object->guid} is deleted, skipping child export", MIDCOM_LOG_INFO);
            $GLOBALS['midcom_helper_replicator_logger']->log_object($object, "object is deleted, do not try to find child objects, setting exportability explicitly to true", MIDCOM_LOG_INFO);
            $this->exportability[$object->guid] = true;
            debug_pop();
            return $serializations;
        }

        // In case of a topic we have to check for possible extra dependencies
        if (is_a($object, 'midgard_topic'))
        {
            $dependency_serializations = $this->serialize_component_dependencies(&$object);
            $serializations = array_merge($serializations, $dependency_serializations);
            unset($dependency_serializations);
        }

        return $serializations;
    }
}
?>