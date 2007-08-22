<?php

/**
 * @package midcom.admin.folder 
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

class midcom_admin_folder_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     *
     * Nothing fancy, loads all script files and the datamanager library.
     */
    function midcom_admin_folder_interface()
    {
        parent::midcom_baseclasses_components_interface();
        
        $this->_component = 'midcom.admin.folder';
        $this->_purecode = true;
        
        $this->_autoload_files = array
        (
            'folder_management.php',
        );
        
        $this->_autoload_libraries = array
        (
            'midcom.admin.help',
        );
    }
}
?>
