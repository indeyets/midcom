<?php
/**
 * @package midcom.helper.filesync
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is the interface class for midcom.helper.filesync
 *
 * @package midcom.helper.filesync
 */
class midcom_helper_filesync_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     *
     * Nothing fancy, loads all script files and the datamanager library.
     */
    function midcom_helper_filesync_interface()
    {
        parent::midcom_baseclasses_components_interface();
        $this->_component = 'midcom.helper.filesync';

        // Load all mandatory class files of the component here
        $this->_autoload_files = array
        (
            'exporter.php',
            'importer.php',
        );

        // Load all libraries used by component here
        $this->_autoload_libraries = array();
    }

    function prepare_dir($prefix)
    {
        $path = $this->_config->get('filesync_path');
        if (!file_exists($path))
        {
	        if (! mkdir($path))
	        {
	            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed create directory {$path}. Reason: " . $php_errormsg);
	            // This will exit.
	        }
        }

        if (substr($path, -1) != '/')
        {
            $path .= '/';
        }

        $module_dir = "{$path}{$prefix}";
        if (!file_exists($module_dir))
        {
 	        if (! mkdir($module_dir))
	        {
	            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed create directory {$module_dir}. Reason: " . $php_errormsg);
	            // This will exit.
	        }
        }

        return "{$module_dir}/";
    }
}
?>
