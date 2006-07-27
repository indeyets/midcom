<?php

/**
 * @package midcom.helper.search
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * MidCOM Hello World component interface class.
 * 
 * Use this as an example to write new components.
 * 
 * ...
 * 
 * @package midcom.helper.helloworld
 */
class midcom_helper_helloworld_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     * 
     * Nothing fancy, loads all script files and the datamanager library.
     */
    function midcom_helper_helloworld_interface()
    {
        parent::midcom_baseclasses_components_interface();
        
        $this->_component = 'midcom.helper.helloworld';
        $this->_autoload_files = Array('viewer.php', 'navigation.php'/*, 'admin.php' */);
        $this->_autoload_libraries = Array('midcom.helper.datamanager');
    }
    
}

?>