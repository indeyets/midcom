<?php
/**
 * @package cc.kaktus.exhibitions
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: interfaces.php 5500 2007-03-08 13:22:25Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Exhibition component MidCOM interface class.
 *
 * @package cc.kaktus.exhibitions
 */
class cc_kaktus_exhibitions_interface extends midcom_baseclasses_components_interface
{
    /**
     * Contructor. Set the libraries and files to autoload and set
     * component constants
     * 
     * @access public
     */
    function cc_kaktus_exhibitions_interface()
    {
        parent::midcom_baseclasses_components_interface();
        
        $this->_autoload_files = array
        (
            'viewer.php',
            'navigation.php',
        );
        
        // Autoload libraries
        $this->_autoload_libraries = Array
        (
            'midcom.helper.datamanager2'
        );
        
        // Set the component name
        $this->_component = 'cc.kaktus.exhibitions';
        
        // Sub event types
        define ('CC_KAKTUS_EXHIBITIONS_SUBPAGE', 0);
        define ('CC_KAKTUS_EXHIBITIONS_ATTACHMENT', 1);
    }
    
    /**
     * Simple lookup method which tries to map the guid to an event of out topic.
     *
     * @access public
     * @return String    Path to the event
     */
    function _on_resolve_permalink($topic, $config, $guid)
    {    
        $event = new midcom_db_event($guid);
        
        if (!$event)
        {
            return null;
        }
        
        // Does the event belong to a master event
        $master_event = new midcom_db_event($config->get('root_event'));
        
        // No master event found, return null
        if (!$master_event)
        {
            return null;
        }
        
        if ($event->id === $master_event->id)
        {
            return '';
        }
        
        // Check if the event master belongs to the master event
        if ($event->up !== $master_event->id)
        {
            $up = new midcom_db_event($event->up);
            
            // It really doesn't belong to the master event of this topic
            if ($up->id !== $master_event->id)
            {
                return null;
            }
            
            // Return null if the type is not subpage
            if ($event->type !== CC_KAKTUS_EXHIBITIONS_SUBPAGE)
            {
                return null;
            }
            
            // Found a sub page
            return date('Y', $up->start) . "/{$up->extra}/{$event->extra}/";
        }
        
        return date('Y', $event->start) . "/{$event->extra}/";
    }
}
?>