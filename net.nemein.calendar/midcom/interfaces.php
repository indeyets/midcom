<?php

/**
 * @package net.nemein.calendar
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Calendar MidCOM interface class.
 *
 * @package net.nemein.calendar
 */

class net_nemein_calendar_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     *
     * Nothing fancy, loads all script files and the datamanager library.
     */
    function net_nemein_calendar_interface()
    {
        parent::__construct();

        $this->_on_initialize();

        $this->_component = 'net.nemein.calendar';
        $this->_autoload_files = array
        (
            'viewer.php',
            'navigation.php',
            'event.php',
            'functions.php',
        );
        $this->_autoload_libraries = array
        (
            'midcom.helper.datamanager2',
            'midcom.helper.dm2config',
            'net.nemein.repeathandler',
            'org.openpsa.calendarwidget',
        );
    }

    /**
     * Initialize n.n.calendar library.
     *
     * @return boolean inidicating success.
     */
    function _on_initialize()
    {
        parent::_on_initialize();

        return true;
    }

    /**
     * Iterate over all events and create index record using the custom indexer
     * method.
     * 
     * @todo Rewrite to DM1 usage.
     * @todo Prevent indexing of master-topic Calendars (they're for aggregation only)
     */
    function _on_reindex($topic, $config, &$indexer)
    {
        $qb = net_nemein_calendar_event_dba::new_query_builder();
        $qb->add_constraint('node', '=', $topic->id);
        $events = $qb->execute();

        $schemadb = midcom_helper_datamanager2_schema::load_database($config->get('schemadb'));
        $datamanager = new midcom_helper_datamanager2_datamanager($schemadb);
        if (! $datamanager)
        {
            debug_add('Warning, failed to create a datamanager instance with this schemapath:' . $config->get('schemadb'),
                MIDCOM_LOG_WARN);
            continue;
        }
        
        foreach ($events as $event)
        {
            if (! $datamanager->autoset_storage($event))
            {
                debug_add("Warning, failed to initialize datamanager for Event {$event->id}. Skipping it.", MIDCOM_LOG_WARN);
                continue;
            }

            net_nemein_calendar_viewer::index($datamanager, $indexer, $topic);
        }
        
        return true;
    }
    
    /**
     * Simple lookup method which tries to map the guid to an article of out topic.
     */
    function _on_resolve_permalink($topic, $config, $guid)
    {    
        $event = new net_nemein_calendar_event_dba($guid);
        if (!$event)
        {
            return null;
        }
        
        if ($event->node != $topic->id)
        {
            return null;
        }
        
        return "{$event->name}/";
    }
}

// Backup implementation.
if (! function_exists('midcom_helper_generate_daylabel'))
{
    function midcom_helper_generate_daylabel ($arg1, $arg2, $arg3, $arg4=true, $arg5=false)
    {
        return net_nemein_calendar_functions_daylabel($arg1, $arg2, $arg3, $arg4, $arg5);
    }
}
?>