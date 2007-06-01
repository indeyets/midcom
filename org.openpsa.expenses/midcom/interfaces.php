<?php
/**
 * @package org.openpsa.expenses 
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is the interface class for org.openpsa.expenses
 * 
 * @package org.openpsa.expenses
 */
class org_openpsa_expenses_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     *
     * Nothing fancy, loads all script files and the datamanager library.
     */
    function org_openpsa_expenses_interface()
    {
        parent::midcom_baseclasses_components_interface();
        $this->_component = 'org.openpsa.expenses';

        // Load all mandatory class files of the component here
        $this->_autoload_files = array
        (
            'viewer.php', 
            'admin.php', 
            'navigation.php'
        );
        
        // Load all libraries used by component here
        $this->_autoload_libraries = array
        (
            'midcom.helper.datamanager2'
        );
    }
    
    function _on_initialize()
    {
        // Load needed data classes
        $_MIDCOM->componentloader->load_graceful('org.openpsa.projects');
        
        return true;
    }
}
?>
