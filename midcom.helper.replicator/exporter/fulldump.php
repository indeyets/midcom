<?php
/**
* @package midcom.helper.replicator
* @author The Midgard Project, http://www.midgard-project.org
* @version $Id: viewer.php 3975 2006-09-06 17:36:03Z bergie $
* @copyright The Midgard Project, http://www.midgard-project.org
* @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
*/

/**
 * Export a full dump of the SG directly to the archive_serial transport
 *
 * @package midcom.helper.replicator
 */
class midcom_helper_replicator_exporter_fulldump extends midcom_helper_replicator_exporter
{
    var $transporter = false;
    var $serialized_guids = array();

    /**
     * @todo figure out a way to work this without the subscription
     */
    function midcom_helper_replicator_exporter_fulldump($subscription)
    {
        // We need serial transporter...
        $subscription->transporter = 'archive_serial';
        parent::__construct($subscription);
        $this->transporter =& midcom_helper_replicator_transporter::create($subscription);
        if (!is_a($this->transporter, 'midcom_helper_replicator_transporter_archive_serial'))
        {
            $x = false;
            return $x;
        }
    }

    /**
     * Fake is_exportable handler to avoid subscriptions from using this to queue stuff
     */
    function is_exportable(&$object)
    {
        /* causes problems when using $this->serialize_attachment (and would cause with $this->serialize_object)
        return false;
        */
        return true;
    }

    /**
     * Dump all MgdSchema classes
     *
     * @todo Is there a sensible way to make sure classes are dumped in such order that we do not get dependency errors when importing
     */
    function dump_all()
    {
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', -1);
        foreach($_MIDGARD['schema']['types'] as $mgdschema_class => $dummy)
        {
            if (empty($mgdschema_class))
            {
                continue;
            }
            if (!$this->dump_type($mgdschema_class))
            {
                return false;
            }
        }
        return true;
    }

    /**
     * Dump all objects in particular MgdSchema class
     *
     * @todo Is there a sensible way to order the objects so that we do not get dependency problems when importing
     * @todo handle deletes
     */
    function dump_type($mgdschema_classname)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $dummy_object = new $mgdschema_classname();
        $midcom_dba_classname = $_MIDCOM->dbclassloader->get_midcom_class_name_for_mgdschema_object($dummy_object);
        if (empty($midcom_dba_classname))
        {
            debug_add("MidCOM does not know of class {$mgdschema_classname}, cannot export.", MIDCOM_LOG_WARN);
            debug_pop();
            // We return true to avoid aborting rest of processing
            return true;
        }
        if (! $_MIDCOM->dbclassloader->load_mgdschema_class_handler($midcom_dba_classname))
        {
            debug_add("Failed to load the handling component for {$midcom_dba_classname}, cannot export.", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }

        /* TODO: can we use collector to get just GUIDs while still checking ACLs (so we can use limit/offset sensibly)
        $mc = call_user_func(array($midcom_dba_classname, 'new_collector'), array('', ''));
        if (!is_a($mc, 'midcom_core_collector'))
        {
            // TODO: error reporting
            return false;
        }
        */

        $qb_callback = array($midcom_dba_classname, 'new_query_builder');
        if (!is_callable($qb_callback))
        {
            debug_add("Static method {$midcom_dba_classname}::new_query_builder() is not callable", MIDCOM_LOG_ERROR);
            debug_pop();
            // We return true to avoid aborting rest of processing
            return true;
        }
        
        $qb = call_user_func($qb_callback);
        if (!is_a($qb, 'midcom_core_querybuilder'))
        {
            debug_add("Did not get valid QB to use", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        // Only dump objects in current SG, otherwise SG0 objects will cause issues...
        $qb->add_constraint('sitegroup', '=', $this->subscription->sitegroup);
        // REMINDER: we can't use include_deleted with MidCOM QB.
        $objects = $qb->execute();
        unset($qb);
        foreach ($objects as $k => $object)
        {
            if (   empty($object)
                || !is_object($object))
            {
                debug_add("given object is not object, skipping", MIDCOM_LOG_WARN);
                continue;
            }
            $class = get_class($object);

            if (   isset($this->serialized_guids[$object->guid])
                // Attachments are marked like this since they have metadata and blob parts
                || isset($this->serialized_guids["{$object->guid}_metadata"]))
            {
                debug_add("Object {$object->guid} ({$class}) already processed, skipping", MIDCOM_LOG_INFO);
                continue;
            }
            
            /* PONDER: use the serialize_object method ?? (would help with ordering dependencies but causes unnecessary recursion)
            $items = $this->serialize_object($object, true);
            if ($items === false)
            {
                // TODO: error reporting
                return false;
            }
            */

            // The special case of attachment
            if (is_a($object, 'midgard_attachment'))
            {
                // Define this even though we don't need it in this case
                $serialized = 'dummy';
                $items = $this->serialize_attachment($object);
                if ($items === false)
                {
                    debug_add("\$this->serialize_attachment() returned failure for {$class} {$object->guid}", MIDCOM_LOG_ERROR);
                    debug_pop();
                    return false;
                }
            }
            else
            {
                $serialized = midcom_helper_replicator_serialize($object);
                if ($serialized === false)
                {
                    debug_add("midcom_helper_replicator_serialize() returned failure for {$class} {$object->guid}", MIDCOM_LOG_ERROR);
                    debug_pop();
                    return false;
                }
                $items = array($object->guid => &$serialized);
            }

            // Mark serialized objects to avoid double-exports
            foreach ($items as $guid => $item)
            {
                $this->serialized_guids[$guid] = true;
            }

            if (!$this->transporter->add_items($items))
            {
                // TODO: error reporting
                debug_add("transporter->add_items() returned failure with error: {$this->transporter->error}", MIDCOM_LOG_ERROR);
                debug_pop();
                unset($items, $serialized, $objects);
                return false;
            }
            unset($items, $serialized, $objects[$k]);
        }
        unset($objects);

        // TODO: Handle deletes as well

        return true;
    }
}
?>