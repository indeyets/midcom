<?php

/**
 * @package net.nemein.hourview2
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Net.nemein.hourview2 component interface class.
 * 
 * Net.nemein.hourview2 is a component that integrates with the OpenPSA suite.
 * Base idea is that clients could approve project hours in extranet.
 * 
 * ...
 * 
 * @package net.nemein.hourview2
 */
class net_nemein_hourview2_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     * 
     * Load all component files/snippets.
     */
    function net_nemein_hourview2_interface()
    {
        parent::midcom_baseclasses_components_interface();
        
        $this->_component = 'net.nemein.hourview2';
        $this->_autoload_files = Array
        (
            'viewer.php', 
            'navigation.php', 
            'admin.php'
        );
        $this->_autoload_libraries = Array
        (
            'midcom.helper.datamanager'
        );
    }

    /**
     * Initialize
     * 
     * Initializing the component and OpenPSA Projects interfaces.
     * We must tune down error reporting as the OpenPSA is not E_ALL compatible.
     */

    function _on_initialize()
    {   
        return true;
    }
    
}

?>