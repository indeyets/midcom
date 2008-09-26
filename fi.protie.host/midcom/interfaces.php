<?php
/**
 * @package fi.protie.host
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: interfaces.php 12672 2007-10-05 11:57:32Z adrenalin $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Host editing interface class
 *
 * @package fi.protie.host
 */
class fi_protie_host_interface extends midcom_baseclasses_components_interface
{
    /**
     * Connect to the parent class constructor method and load the required files and libraries
     */
    function fi_protie_host_interface()
    {
       parent::__construct();
            
        $this->_component = 'fi.protie.host';
        
        $this->_autoload_files = array
        (
            // Normal structural files
            'viewer.php',
            'navigation.php',
        );
        
        $this->_autoload_libraries = array
        (
            'midcom.helper.datamanager2',
        );
    }
}
?>