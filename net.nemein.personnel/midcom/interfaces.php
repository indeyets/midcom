<?php
/**
 * @package net.nemein.personnel
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Person viewer MidCOM interface class.
 * 
 * @package net.nemein.personnel
 */
class net_nemein_personnel_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     * 
     * Nothing fancy, loads all script files and the datamanager library.
     */
    function net_nemein_personnel_interface()
    {
        parent::midcom_baseclasses_components_interface();
        
        $this->_component = 'net.nemein.personnel';
        $this->_autoload_files = Array('viewer.php', 'admin.php', 'navigation.php');
        $this->_autoload_libraries = Array('midcom.helper.datamanager2');
    }
    
}

?>
