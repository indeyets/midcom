<?php

/**
 * @package ${module} 
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

class ${module_class}_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     *
     * Nothing fancy, loads all script files and the datamanager library.
     */
    function ${module_class}_interface()
    {
        parent::midcom_baseclasses_components_interface();
        $this->_component = '${module}';
        $this->_autoload_files = Array('viewer.php', 'admin.php', 'navigation.php');
        $this->_autoload_libraries = Array('midcom.helper.datamanager2');
    }

}
?>
