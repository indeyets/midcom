<?php
/**
 * @package net.nemein.updatenotification 
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is the interface class for net.nemein.updatenotification
 * 
 * @package net.nemein.updatenotification
 */
class net_nemein_updatenotification_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     *
     * Nothing fancy, loads all script files and the datamanager library.
     */
    function __construct()
    {
        parent::__construct();
        $this->_component = 'net.nemein.updatenotification';

        // Load all mandatory class files of the component here
        $this->_autoload_files = array
        (
            'viewer.php', 
            'navigation.php'
        );
        
        // Load all libraries used by component here
        $this->_autoload_libraries = array
        (
            'midcom.helper.datamanager2'
        );
    }

}
?>