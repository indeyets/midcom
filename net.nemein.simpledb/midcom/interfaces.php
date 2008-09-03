<?php
/**
 * @package net.nemein.simpledb
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Simpledb MidCOM interface class.
 * 
 * @package net.nemein.simpledb
 */
class net_nemein_simpledb_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     * 
     * Nothing fancy, loads all script files and the datamanager library.
     */
    function net_nemein_simpledb_interface()
    {
        parent::__construct();
        
        $this->_component = 'net.nemein.simpledb';
        $this->_autoload_files = Array('viewer.php', 'navigation.php');
        $this->_autoload_libraries = Array('midcom.helper.datamanager');
    }
    
}

?>