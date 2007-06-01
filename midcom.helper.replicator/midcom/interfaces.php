<?php
/**
 * @package midcom.helper.replicator 
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is the interface class for midcom.helper.replicator
 * 
 * @package midcom.helper.replicator
 */
class midcom_helper_replicator_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     *
     * Nothing fancy, loads all script files and the datamanager library.
     */
    function midcom_helper_replicator_interface()
    {
        parent::midcom_baseclasses_components_interface();
        $this->_component = 'midcom.helper.replicator';
        
        $this->_purecode = true;

        // Load all mandatory class files of the component here
        $this->_autoload_files = array
        (
            'helpers.php',
            'exporter.php',
            'importer.php',
            'queuemanager.php',
            'subscription.php',
            'transporter.php',
            'logger.php',
        );
        
        // Load all libraries used by component here
        $this->_autoload_libraries = array
        (
        );
    }
    
    function _on_initialize()
    {
        define('MIDCOM_REPLICATOR_AUTOMATIC', 1);
        define('MIDCOM_REPLICATOR_MANUAL', 2);
        define('MIDCOM_REPLICATOR_DISABLED', 3);
    
        // Start the replication logger instance
        $logfile = $this->_data['config']->get('log_filename');
        if (!file_exists($logfile))
        {
            touch($logfile);
        }
        
        if (!is_writable($logfile))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Replication log file {$logfile} is not writable by Apache.");
            // This will exit.
        }
        
        $GLOBALS['midcom_helper_replicator_logger'] = new midcom_helper_replicator_logger($logfile);
    
        return true;
    }

    function _on_watched_operation($operation, &$object)
    {
        if (   is_a($object, 'midcom_helper_replicator_subscription')
            || is_a($object, 'midcom_services_at_entry')
            )
        {
            // Never DBA queue subscription and AT entry objects
            return;
        }
        $qmanager =& midcom_helper_replicator_queuemanager::get();
        
        // Deletes require love
        if ($operation === MIDCOM_OPERATION_DBA_DELETE)
        {
            $qmanager->add_to_queue($object, true);
            return;
        }
        $qmanager->add_to_queue($object);
    }

    /**
     * AT service handler to queue an object and then process the queue
     *
     * Used by staging-live to handle scheduling
     *
     * @param array $args handler arguments
     * @param object $handler reference to the cron_handler object calling this method.
     * @return bool indicating success/failure
     * @todo figure out how to handle deleted (not purged) object replication
     */
    function at_queue_guid($args, &$handler)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        if (   !isset($args['guid'])
            || empty($args['guid']))
        {
            $message = "\$args['guid'] not set or empty";
            $handler->print_error($message);
            debug_add($message, MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }

        $object = $_MIDCOM->dbfactory->get_object_by_guid($args['guid']);
        // TODO: Figure out what to do with deleted objects and how
        if (   !is_object($object)
            || !isset($object->guid)
            || empty($object->guid))
        {
            $message = "Could not get_object_by_guid('{$args['guid']}')";
            $handler->print_error($message);
            debug_add($message, MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }

        $qmanager =& midcom_helper_replicator_queuemanager::get();
        if (!is_a($qmanager, 'midcom_helper_replicator_queuemanager'))
        {
            $message = 'Could not instantiate queue manager';
            $handler->print_error($message);
            debug_add($message, MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }

        if (!$qmanager->add_to_queue($object))
        {
            $class = get_class($object);
            $message = "Failed to queue {$class} {$object->guid}";
            $handler->print_error($message);
            debug_add($message, MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }

        if (!$qmanager->process_queue())
        {
            $message = 'Queue manager failed to process_queue()';
            $handler->print_error($message);
            debug_add($message, MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }

        debug_pop();
        return true;
    }
}

?>