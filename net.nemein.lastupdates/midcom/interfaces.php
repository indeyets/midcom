<?php
/**
 * @package net.nemein.lastupdates 
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is the interface class for net.nemein.lastupdates
 * 
 * @package net.nemein.lastupdates
 */
class net_nemein_lastupdates_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     *
     * Nothing fancy, loads all script files and the datamanager library.
     */
    function net_nemein_lastupdates_interface()
    {
        parent::midcom_baseclasses_components_interface();
        $this->_component = 'net.nemein.lastupdates';

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
        if (   !isset($GLOBALS['midcom_config']['indexer_backend'])
            || empty($GLOBALS['midcom_config']['indexer_backend']))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('No indexer backend defined, we cannot query indexer for data, this is fatal.', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        return true;
    }
}
?>
