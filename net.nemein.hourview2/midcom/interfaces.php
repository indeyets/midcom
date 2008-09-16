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
 * @package net.nemein.hourview2
 */
class net_nemein_hourview2_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     * 
     * Load all component files/snippets.
     */
    function __construct()
    {
        parent::__construct();
        
        $this->_component = 'net.nemein.hourview2';
        $this->_autoload_files = Array
        (
            'viewer.php', 
            'navigation.php'
        );
        
        $this->_autoload_libraries = Array
        (
            'org.openpsa.contactwidget',
            'org.openpsa.notifications',
        );
    }

    function _on_initialize()
    {
        // Load needed data classes
        $_MIDCOM->componentloader->load_graceful('org.openpsa.sales');
        $_MIDCOM->componentloader->load_graceful('org.openpsa.projects');
    
        return true;
    }
}

?>