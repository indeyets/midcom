<?php
/**
 * @package net.nemein.reservations 
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is the interface class for net.nemein.reservations
 * 
 * @package net.nemein.reservations
 */
class net_nemein_reservations_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     *
     * Nothing fancy, loads all script files and the datamanager library.
     */
    function __construct()
    {
        parent::__construct();
        $this->_component = 'net.nemein.reservations';

        // Load all libraries used by component here
        $this->_autoload_libraries = array
        (
            'midcom.helper.datamanager2',
            'org.openpsa.calendarwidget',
        );
    }

    function _on_initialize()
    {
        //We need the contacts person class available.
        $_MIDCOM->componentloader->load_graceful('org.openpsa.calendar');
        
        if (   !isset($GLOBALS['midcom_component_data']['org.openpsa.calendar']['calendar_root_event'])
            || !is_object($GLOBALS['midcom_component_data']['org.openpsa.calendar']['calendar_root_event']))
        {
            // Root event not found, abort
            return false;
        }
        
        return true;
    }

    function _on_resolve_permalink(&$topic, &$config, $guid)
    {
        $root_event =& $GLOBALS['midcom_component_data']['org.openpsa.calendar']['calendar_root_event'];
        if ($root_event->guid == $guid)
        {
            return '';
        }
        $event = new org_openpsa_calendar_event($guid);
        if (   $event
            && $event->up == $root_event->id)
        {
            return "reservation/{$event->guid}/";
        }
        return null;
    }
}
?>