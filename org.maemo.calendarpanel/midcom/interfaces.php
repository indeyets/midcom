<?php
/**
 * @package org.maemo.calendarpanel 
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is the interface class for org.maemo.calendarpanel
 * 
 * @package org.maemo.calendarpanel
 */
class org_maemo_calendarpanel_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     *
     * Nothing fancy, loads all script files and the datamanager library.
     */
    function org_maemo_calendarpanel_interface()
    {
        parent::midcom_baseclasses_components_interface();

        $this->_component = 'org.maemo.calendarpanel';
        $this->_purecode = true;
        
        // Load all mandatory class files of the component here
        $this->_autoload_files = array
        (
            'main.php',
            'calendarpanel_leaf.php',
            'calendar_leaf.php',
            'buddylist_leaf.php',
            'shelf_leaf.php',
            'profile_leaf.php'
        );
        
        // // Load all libraries used by component here
        // $this->_autoload_libraries = array
        // (
        //     'midcom.helper.datamanager2'
        // );
    }

    function _on_initialize()
    {
        return true;
    }

}
?>