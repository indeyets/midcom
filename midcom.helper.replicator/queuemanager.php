<?php
/**
* @package midcom.helper.replicator
* @author The Midgard Project, http://www.midgard-project.org
* @version $Id: viewer.php 3975 2006-09-06 17:36:03Z bergie $
* @copyright The Midgard Project, http://www.midgard-project.org
* @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
*/

/**
 * @package midcom.helper.replicator
 */
class midcom_helper_replicator_queuemanager extends midcom_baseclasses_components_purecode
{
    var $exporters = array();
    var $transporters = array();
    var $started = 0;
    /**
     * @todo this should be per-subscription
     */
    var $file_count = 0;

    /**
     * Initializes the class. The real startup is done by the initialize() call.
     *
     * @param midcom_helper_replicator_subscription_dba $subscription Subscription
     */
    function midcom_helper_replicator_queuemanager()
    {
         $this->_component = 'midcom.helper.replicator';
         parent::midcom_baseclasses_components_purecode();
         $this->started = date('YmdHis');
    }

    /**
     * The correct way to get a queue manager, call this statically, returns reference
     * 
     * @static
     */
    function &get()
    {
        if (!array_key_exists('midcom_helper_replicator_queuemanager_instance', $GLOBALS))
        {
            $GLOBALS['midcom_helper_replicator_queuemanager_instance'] = new midcom_helper_replicator_queuemanager();
        }
        return $GLOBALS['midcom_helper_replicator_queuemanager_instance'];
    }

    function sanity_check()
    {
        // TODO: Sanity check queue root etc
        return true;
    }

    /**
     * Check if we have any valid queues to add data to
     *
     * @return boolean indicating state
     */
    function can_add_to_queue()
    {
        $global_base = $this->_config->get('queue_root_dir');
        if (   !is_dir($global_base)
            || !is_writable($global_base))
        {
            return false;
        }
        $qb = midcom_helper_replicator_subscription_dba::new_query_builder();
        $qb->add_constraint('status', '=', MIDCOM_REPLICATOR_AUTOMATIC);
        $qb->set_limit(1);
        $count = $qb->count();
        if ($count > 0)
        {
            return true;
        }
        return false;
    }

    /**
     * This method will check exporters of each subscription whether they are interested
     * in exporting the particular object.
     *
     * If the exporters are interested, they will be asked to serialize the data. Queue manager
     * will then store the serialized data for each queue.
     * @todo refactor to smaller methods
     * @todo query for subscriptions only once
     */
    function add_to_queue(&$object, $rewrite_to_delete = false)
    {
        $GLOBALS['midcom_helper_replicator_logger']->push_prefix('Queue Manager');
        debug_push_class(__CLASS__, __FUNCTION__);
        $qb = midcom_helper_replicator_subscription_dba::new_query_builder();
        // NOTE: if this constraint is changed see can_add_to_queue()
        $qb->add_constraint('status', '=', MIDCOM_REPLICATOR_AUTOMATIC);
        $subscriptions = $qb->execute();
        $queued_guids = array();
        foreach ($subscriptions as $subscription)
        {
            debug_add("Processing subscription '{$subscription->title}' ({$subscription->guid})");
        
            $exporter = midcom_helper_replicator_exporter::create($subscription);

            if (!$exporter->is_exportable($object))
            {
                debug_add('exporter->is_exportable() returned false');
                continue;
            }
            
            if ($rewrite_to_delete)
            {
                $exporter->_serialize_rewrite_to_delete[$object->guid] = true;
            }
            $exporter_serializations = $exporter->serialize($object);
            if ($exporter_serializations === false)
            {
                debug_add('exporter->serialize() returned false', MIDCOM_LOG_ERROR);
                continue;
            }
            if (empty($exporter_serializations))
            {
                debug_add('exporter->serialize() returned empty array', MIDCOM_LOG_WARN);
                continue;
            }
            
            // Store the serialized XML data for the queue
            $path = $this->_get_subscription_queue_basedir($subscription);
            if (empty($path))
            {
                // TODO: error handling
                debug_add('could not get queue dir for subscription', MIDCOM_LOG_ERROR);
                continue;
            }
            $i =& $this->file_count;
            debug_add('about to queue ' . count($exporter_serializations) . ' keys');
            reset($exporter_serializations);
            foreach ($exporter_serializations as $key => $data)
            {
                if (empty($data))
                {
                    $msg = "Key {$key} has empty data, skipping";
                    $GLOBALS['midcom_helper_replicator_logger']->log($msg, MIDCOM_LOG_WARN);
                    debug_add($msg, MIDCOM_LOG_WARN);
                    continue;
                }
                $file = "{$path}/" . sprintf('%010d', $i) . "_{$key}.xml";
                if (file_exists($file))
                {
                    // PANIC: file is already there (it *really* should not be)
                    unset($exporter_serializations, $key, $data);
                    debug_add("file {$file} already exists", MIDCOM_LOG_ERROR);
                    debug_pop();
                    $GLOBALS['midcom_helper_replicator_logger']->pop_prefix();
                    return false;
                }
                $fp = fopen($file, 'w');
                if (!$fp)
                {
                    // PANIC: Can't open file for writing
                    debug_add("can't open file {$file} for writing", MIDCOM_LOG_ERROR);
                    unset($exporter_serializations, $key, $data);
                    debug_pop();
                    $GLOBALS['midcom_helper_replicator_logger']->pop_prefix();
                    return false;
                }
                fwrite($fp, $data, strlen($data));
                fclose($fp);
                
                $msg = "Queued {$key} as {$file}";
                $GLOBALS['midcom_helper_replicator_logger']->log($msg);
                debug_add($msg);

                // TODO: How to call midgard_replicator::export() for all the objects exported ?? (and is this the correct place for that ?)
                if (($guid_end = strpos($key, '_metadata')) !== false)
                {
                    // This key is attachment metadata
                    $export_guid = substr($key, 0, $guid_end);
                    unset($guid_end);
                }
                elseif (mgd_is_guid($key))
                {
                    $export_guid = $key;
                }
                else
                {
                    debug_add("could not determine GUID from key '{$key}'");
                    $export_guid = false;
                }
                if ($export_guid)
                {
                    $queued_guids[$export_guid] = true;
                }

                unset($key, $data);
                $i++;
            }
            unset($exporter_serializations);
            debug_add('all keys queued, now marking them exported');
            foreach ($queued_guids as $export_guid => $bool)
            {
                $marked_exported = false;
                if (version_compare(mgd_version(), '1.8.2', '>='))
                {
                    // In Midgard 1.8.2 we can do this efficiently with the export_by_guid method
                    $marked_exported = midgard_replicator::export_by_guid($export_guid);
                }
                else
                {
                    $object = $_MIDCOM->dbfactory->get_object_by_guid($export_guid);
                    $marked_exported = midgard_replicator::export($object);
                }
                
                if ($marked_exported)
                {
                    $msg = "Marked GUID '{$export_guid}' as exported to queue \"{$subscription->title}\"";
                    $GLOBALS['midcom_helper_replicator_logger']->log($msg);
                    debug_add($msg);
                }
                else
                {
                    $msg = "Failed to mark GUID '{$export_guid}' as exported, errstr: " . mgd_errstr();
                    $GLOBALS['midcom_helper_replicator_logger']->log($msg, MIDCOM_LOG_ERROR);
                    debug_add($msg, MIDCOM_LOG_ERROR);
                }
            }
            debug_add("All done for subscription '{$subscription->title}' ({$subscription->guid})");
        }
        debug_add('All automatic subscriptions processed');
        debug_pop();
        $GLOBALS['midcom_helper_replicator_logger']->pop_prefix();
        return true;
    }

    /**
     * Gets/creates the path for subscriptions spool dir
     * @todo make a smarter recursive directory creator
     */
    function _get_subscription_basedir(&$subscription)
    {
        $global_base = $this->_config->get('queue_root_dir');
        if (!is_dir($global_base))
        {
            // The configuration key might have dynamic part to it
            debug_push_class(__CLASS__, __FUNCTION__);    
            debug_add("directory {$global_base} does not exist, creating", MIDCOM_LOG_DEBUG);
            if (!mkdir($global_base))
            {
                // TODO: Error reporting
                debug_add("could not create directory {$global_base}", MIDCOM_LOG_ERROR);
                debug_pop();
                return false;
            }
            debug_pop();
        }
        $subscription_path = "{$global_base}/{$subscription->guid}";
        if (!is_dir($subscription_path))
        {
            debug_push_class(__CLASS__, __FUNCTION__);    
            debug_add("directory {$subscription_path} does not exist, creating", MIDCOM_LOG_DEBUG);
            if (!mkdir($subscription_path))
            {
                // TODO: Error reporting
                debug_add("could not create directory {$subscription_path}", MIDCOM_LOG_ERROR);
                debug_pop();
                return false;
            }
            debug_pop();
        }
        return $subscription_path;
    }

    function _get_subscription_quarantine_basedir(&$subscription)
    {
        $global_base = $this->_config->get('queue_root_dir');
        if (!is_dir($global_base))
        {
            // The configuration key might have dynamic part to it
            debug_push_class(__CLASS__, __FUNCTION__);    
            debug_add("directory {$global_base} does not exist, creating", MIDCOM_LOG_DEBUG);
            if (!mkdir($global_base))
            {
                // TODO: Error reporting
                debug_add("could not create directory {$global_base}", MIDCOM_LOG_ERROR);
                debug_pop();
                return false;
            }
            debug_pop();
        }
        $subscription_path = "{$global_base}/{$subscription->guid}-quarantine";
        if (!is_dir($subscription_path))
        {
            debug_push_class(__CLASS__, __FUNCTION__);    
            debug_add("directory {$subscription_path} does not exist, creating", MIDCOM_LOG_DEBUG);
            if (!mkdir($subscription_path))
            {
                // TODO: Error reporting
                debug_add("could not create directory {$subscription_path}", MIDCOM_LOG_ERROR);
                debug_pop();
                return false;
            }
            debug_pop();
        }
        return $subscription_path;
    }

    function _get_subscription_quarantine_queuedir(&$subscription)
    {
        $quarantine_path = $this->_get_subscription_quarantine_basedir($subscription);
        if ($quarantine_path === false)
        {
            // TODO: Error reporting
            return false;
        }
        $queue_path = "{$quarantine_path}/{$this->started}";
        if (!is_dir($queue_path))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("directory {$queue_path} does not exist, creating", MIDCOM_LOG_DEBUG);
            if (!mkdir($queue_path))
            {
                // TODO: Error reporting
                debug_add("could not create directory {$queue_path}", MIDCOM_LOG_ERROR);
                debug_pop();
                return false;
            }
            debug_pop();
        }
        return $queue_path;
    }


    function _get_subscription_queue_basedir(&$subscription)
    {
        $subscription_path = $this->_get_subscription_basedir($subscription);
        if ($subscription_path === false)
        {
            // TODO: Error reporting
            return false;
        }
        $queue_path = "{$subscription_path}/{$this->started}";
        if (!is_dir($queue_path))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("directory {$queue_path} does not exist, creating", MIDCOM_LOG_DEBUG);
            if (!mkdir($queue_path))
            {
                // TODO: Error reporting
                debug_add("could not create directory {$queue_path}", MIDCOM_LOG_ERROR);
                debug_pop();
                return false;
            }
            debug_pop();
        }
        return $queue_path;
    }

    function _process_queue_queuepath_sanitychecks(&$queue_name, &$subscription_path)
    {
        if (   $queue_name == '.'
            || $queue_name == '..')
        {
            // Skip the . and .. entries (which are always present)
            return false;
        }
        // Sanity check the subdir name
        if (!is_numeric($queue_name))
        {
            // Nonnumeric paths are not our queues
            debug_add("Weird queue name '{$queue_name}' in path '{$subscription_path}'", MIDCOM_LOG_WARN);
            return false;
        }
        // Sanity check path
        $queue_path = "{$subscription_path}/{$queue_name}";
        if (!is_dir($queue_path))
        {
            debug_add("Queue path '{$queue_path}' is not a directory, skipping", MIDCOM_LOG_ERROR);
            return false;
        }
        return true;
    }

    /**
     * Helper for process_queue, quarantines failed items
     */
    function _process_queue_quarantines(&$items, &$items_paths, &$subscription)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        foreach ($items_paths as $item_key => $item_path)
        {
            if (array_key_exists($item_key, $items))
            {
                // Item still in array (transporter should remove each key as it's transported properly)
                debug_add("Transporter left key '{$item_key}' into items, quarantineing '{$item_path}'", MIDCOM_LOG_INFO);
                $quarantine_path = $this->_get_subscription_quarantine_queuedir($subscription);
                if (!is_dir($quarantine_path))
                {
                    // Could not get valid dir
                    continue;
                }
                $quarantine_filepath = $quarantine_path . '/' . basename($item_path);
                $output = array();
                $code = 0;
                exec("mv {$item_path} {$quarantine_filepath}", $output, $code);
                if ($code != 0)
                {
                    debug_add("Failed to quarantine '{$item_path}' as '{$quarantine_filepath}'", MIDCOM_LOG_ERROR);
                }
                else
                {
                    debug_add("Quarantined '{$item_path}' as '{$quarantine_filepath}'", MIDCOM_LOG_INFO);
                }
                // TODO: create notice
                continue;
            }
            if (!unlink($item_path))
            {
                debug_add("Could not remove file '{$item_path}'", MIDCOM_LOG_ERROR);
                continue;
            }
            $GLOBALS['midcom_helper_replicator_logger']->log("File {$item_path} removed from queue \"{$subscription->title}\"");
        }
        debug_pop();
    }

    /**
     * Helper for process_queue, gets items for given queue directory pointer
     */
    function _process_queue_get_items(&$dp_queue, &$queue_path, &$subscription)
    {
        // Start crunching each file into key => XML array
        debug_push_class(__CLASS__, __FUNCTION__);
        $items = array();
        $items_paths = array();
        $items_sort = array();
        $GLOBALS['_process_queue_get_items__items_sort'] =& $items_sort;
        while (($queue_item = readdir($dp_queue)) !== false)
        {
            if (   $queue_item == '.'
                || $queue_item == '..'
                || $queue_item == 'quarantine')
            {
                // Skip the . and .. entries (which are always present) & old style quarantine dir
                continue;
            }
            $item_path = "{$queue_path}/{$queue_item}";
            if (   !is_readable($item_path)
                || !is_file($item_path))
            {
                // PANIC: Could not read queue item
                debug_add("Cannot open file '{$item_path}' for reading", MIDCOM_LOG_ERROR);
                debug_pop();
                return false;
            }

            // reset time limit counter while reading files
            set_time_limit(30);

            // Separate the indexing prefix from key in item filename
            $item_key = substr($queue_item, 11);
            $items_sort[$item_key] = (int)substr($queue_item, 0, 10);
            // Read item
            $items[$item_key] = file_get_contents($item_path);
            if ($items[$item_key] === false)
            {
                unset($items[$item_key]);
                debug_add("Could not read file '{$item_path}'", MIDCOM_LOG_ERROR);
                debug_pop();
                return false;
            }
            $GLOBALS['midcom_helper_replicator_logger']->log("Read {$item_key} from queue \"{$subscription->title}\" file {$item_path}");
            $items_paths[$item_key] = $item_path;
            unset($item_key, $item_path);
        }
        unset($queue_item);
        // reset time limit counter to config value
        set_time_limit(ini_get('max_execution_time'));

        // Sort the arrays, readdir may return the files in "weird" order
        uksort($items, array($this, '_process_queue_sort_items'));
        uksort($items_paths, array($this, '_process_queue_sort_items'));
        debug_print_r('items_sort', $items_sort);
        debug_print_r('items_paths', $items_paths);
        reset($items);
        reset($items_paths);
        debug_pop();
        return array($items, $items_paths);
    }

    /**
     * Helper for _process_queue_get_items, to sort the arrays
     */
    function _process_queue_sort_items($a, $b)
    {
        $items_sort =& $GLOBALS['_process_queue_get_items__items_sort'];
        $av = $items_sort[$a];
        $bv = $items_sort[$b];
        if ($av > $bv)
        {
            return 1;
        }
        if ($av < $bv)
        {
            return -1;
        }
        return 0;
    }

    /**
     * Helper for process_queue, processes given queue of given subscription
     */
    function _process_queue_queuepath(&$queue_name, &$subscription_path, &$subscription)
    {
        if (!$this->_process_queue_queuepath_sanitychecks($queue_name, $subscription_path))
        {
            return;
        }
        debug_push_class(__CLASS__, __FUNCTION__);
        $queue_path = "{$subscription_path}/{$queue_name}";

        // Open handle
        $dp_queue = opendir($queue_path);
        if (!$dp_queue)
        {
            debug_add("Cannot open queue path '{$queue_path}' for reading", MIDCOM_LOG_ERROR);
            debug_pop();
            return;
        }

        // Get us the transporter
        $transporter = midcom_helper_replicator_transporter::create($subscription);
        if (!is_a($transporter, 'midcom_helper_replicator_transporter'))
        {
            debug_add("Could not instantiate transporter for subscription {$subscription->guid}", MIDCOM_LOG_ERROR);
            debug_pop();
            return;
        }

        /**
         * TODO: Read only X items to memory at a time ?
         */
        $items_ret = $this->_process_queue_get_items($dp_queue, $queue_path, $subscription);
        if ($items_ret === false)
        {
            closedir($dp_queue);
            debug_add("Fatal error while reading items in {$queue_name}", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        $items =& $items_ret[0];
        $items_paths =& $items_ret[1];

        closedir($dp_queue);

        if (!$transporter->process($items))
        {
            // Transporter returned error, skip removal of files
            $GLOBALS['midcom_helper_replicator_logger']->log("Got error \"{$transporter->error}\" from transporter for queue \"{$subscription->title}\".", MIDCOM_LOG_ERROR);
            $GLOBALS['midcom_helper_replicator_logger']->log("Saving queue for later retry.", MIDCOM_LOG_ERROR);
            debug_pop();
            return;
        }

        // Remove files transported correctly, quarantine problematic files
        $this->_process_queue_quarantines($items, $items_paths, $subscription);
        unset($items, $items_paths, $items_ret);
        /**
         * /TODO: Read only X items to memory at a time ?
         */

        // Check if the queue_dir has any more items left, if not rmdir it
        $this->_rm_empty_dir($queue_path);

        debug_pop();
        return true;
    }

    /**
     * Helper for process_queue, processes given subscriptions queues
     */
    function _process_queue_subscription(&$subscription)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $subscription_path = $this->_get_subscription_basedir($subscription);
        if ($subscription_path === false)
        {
            debug_add('Could not get base dir for subscription', MIDCOM_LOG_ERROR);
            return;
        }
        $dp_queues = opendir($subscription_path);
        if (!$dp_queues)
        {
            debug_add("Could not open dir '{$subscription_path}' for reading", MIDCOM_LOG_ERROR);
            return;
        }
        // TODO: refactor these loops to separate methods
        while (($queue_name = readdir($dp_queues)) !== false)
        {
            if ($this->_process_queue_queuepath($queue_name, $subscription_path, $subscription) === false)
            {
                // If the method returns strict boolean false then something failed fatally and we abort
                debug_add("Processing queue {$queue_name} for {$subscription->title} failed fatally", MIDCOM_LOG_ERROR);
                debug_pop();
                return false;
            }
        }
        closedir($dp_queues);
        debug_pop();
        return true;
    }

    /**
     * This method will process all unprocessed items in queues and send them via the
     * appropriate transporters.
     */
    function process_queue()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $GLOBALS['midcom_helper_replicator_logger']->push_prefix('Queue Manager');
        $qb = midcom_helper_replicator_subscription_dba::new_query_builder();
        $qb->add_constraint('status', '<>', MIDCOM_REPLICATOR_DISABLED);
        $subscriptions = $qb->execute();
        foreach ($subscriptions as $subscription)
        {
            if ($this->_process_queue_subscription($subscription) === false)
            {
                // If the method returns strict boolean false then something failed fatally and we abort
                debug_add("Processing subscription {$subscription->title} failed fatally", MIDCOM_LOG_ERROR);
                debug_pop();
                $GLOBALS['midcom_helper_replicator_logger']->pop_prefix();
                return false;
            }
        }
        debug_pop();
        $GLOBALS['midcom_helper_replicator_logger']->pop_prefix();
        return true;
    }

    function _rm_empty_dir($dir)
    {
        if (!is_dir($dir))
        {
            // Directoty does not exist
            return true;
        }
        $dp = opendir($dir);
        if (!$dp)
        {
            // Can't open dir
            return false;
        }
        $files = array();
        while (($file = readdir($dp)) !== false)
        {
            if (   $file == '.'
                || $file == '..')
            {
                // Skip the . and .. entries (which are always present)
                continue;
            }
            $files[] = $file;
        }
        if (!empty($files))
        {
            // Directory not empty
            // TODO: Dump file list to debug
            return false;
        }
        return rmdir($dir);
    }

    /**
     * statically callable method to get url/filesystem safe name for current SG name
     */
    function safe_sg_name()
    {
        if ($_MIDGARD['sitegroup'] == 0)
        {
            return 'sg0';
        }
        $sg = mgd_get_sitegroup($_MIDGARD['sitegroup']);
        if (   !is_object($sg)
            || empty($sg->name))
        {
            return 'unknown';
        }
        return midcom_generate_urlname_from_string($sg->name);
    }
}
?>