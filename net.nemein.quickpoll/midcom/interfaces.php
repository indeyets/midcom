<?php

/**
 * @package net.nemein.quickpoll
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Quickpoll MidCOM interface class.
 * 
 * @package net.nemein.quickpoll
 */
class net_nemein_quickpoll_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     * 
     * Nothing fancy, loads all script files and the datamanager library.
     */
    function net_nemein_quickpoll_interface()
    {
        parent::midcom_baseclasses_components_interface();
        
        $this->_component = 'net.nemein.quickpoll';
        $this->_autoload_files = Array('viewer.php', 'admin.php', 'navigation.php');
        $this->_autoload_libraries = Array('midcom.helper.datamanager');
    }
    
    /* No _on_reindex, this component isn't indexed at this time. */
}

?>