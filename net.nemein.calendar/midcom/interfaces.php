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
        parent::midcom_baseclasses_components_interface();

        $this->_on_initialize();

        $this->_component = 'net.nemein.calendar';
        $this->_autoload_files = Array(
            'viewer.php',
            'admin.php',
            'navigation.php',
            'event.php',
            'functions.php',
        );
        $this->_autoload_libraries = Array(
            'midcom.helper.datamanager2',
            'net.nemein.repeathandler',
        );
    }

    /**
     * Initialize n.n.calendar library.
     *
     * @return bool inidicating success.
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
        $root_event = $topic->parameter("net.nemein.calendar","root_event");
        if ($root_event)
        {
            $root_event = new midcom_db_event($root_event);
        }
        if ($events = mgd_list_events($root_event->id))
        {
            while ($events->fetch ())
            {
                $document = net_nemein_calendar_event2document(new midcom_db_event($events->id));
                if ($document)
                {
                    $indexer->index($document);
                }
            }
        }
        return true;
    }
}

// Backup implementation.
if (! function_exists('midcom_helper_generate_daylabel'))
{
    function midcom_helper_generate_daylabel ($arg1, $arg2, $arg3)
    {
        return net_nemein_calendar_functions_daylabel($arg1, $arg2, $arg3); 
    }
}
?>