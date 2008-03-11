<?php
/**
 * @package midgard.admin.asgard
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is the interface class for midgard.admin.asgard
 *
 * @package midgard.admin.asgard
 */
class midgard_admin_asgard_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     *
     * Nothing fancy, loads all script files and the datamanager library.
     */
    function midgard_admin_asgard_interface()
    {
        parent::midcom_baseclasses_components_interface();
        $this->_component = 'midgard.admin.asgard';
        $this->_purecode = true;
        $this->_autoload_files = array
        (
            'navigation.php',
            'plugin.php',
            'tree.php',
        );

        $this->_autoload_libraries = array
        (
            'midcom.helper.reflector',
            'midcom.admin.help',
        );

    }

    function _on_initialize()
    {
        if (!version_compare(mgd_version(), '1.8.3', 'gt'))
        {
            // Only works on 1.8.4 RC and above
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('Asgard can only by used on Midgard 1.8.4 RC and newer', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }

        // Enable jQuery
        $_MIDCOM->enable_jquery();

        return true;
    }

}
?>