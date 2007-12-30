<?php

/**
 * @package net.nemein.beaexporter
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Net.nemein.beaexporter component interface class.
 * 
 * 
 * @package net.nemein.beaexporter
 */
class net_nemein_beaexporter_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     */
    function net_nemein_beaexporter_interface()
    {
        parent::midcom_baseclasses_components_interface();
        
        $this->_component = 'net.nemein.beaexporter';
        $this->_purecode = true;
        $this->_autoload_files = Array
        (
            'state.php',
            'sprint_r.php',
            'main.php',
        );
        /* I don't think we need anything, at least yet.
        $this->_autoload_libraries = Array
        (
            'midcom.helper.datamanager'
        );
        */
    }

    /**
     * Nothing to do here but return true
     */
    function _on_initialize()
    {
        // PONDER: sanity-check the configuration (dump_dir is writable etc)
        return true;
    }
    
    /**
     * Passes the object to the main class operation specific handler.
     */
    function _on_watched_operation($operation, $object)
    {
        if (   !isset($this->_config)
            || !is_object($this->_config))
        {
            // cannot check for state, return silently
            return true;
        }
        if (!$this->_config->get('active'))
        {
            // Return with success if we're not active
            return true;
        }
        $handler = new net_nemein_beaexporter();
        switch ($operation)
        {
            case MIDCOM_OPERATION_DBA_CREATE:
                return $handler->created($object);
                break;
            case MIDCOM_OPERATION_DBA_UPDATE:
                return $handler->updated($object);
                break;
            case MIDCOM_OPERATION_DBA_DELETE:
                return $handler->deleted($object);
                break;
            default:
                return false;
        }
    }
    
}

?>