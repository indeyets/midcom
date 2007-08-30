<?php
/**
 * @package net.nemein.openid 
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is the interface class for net.nemein.openid
 * 
 * @package net.nemein.openid
 */
class net_nemein_openid_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     *
     * Nothing fancy, loads all script files and the datamanager library.
     */
    function net_nemein_openid_interface()
    {
        parent::midcom_baseclasses_components_interface();
        $this->_component = 'net.nemein.openid';

        // Load all mandatory class files of the component here
        $this->_autoload_files = array
        (
            'consumer.php',
        );
    }

    function _on_initialize()
    {
        if (!$GLOBALS['midcom_config']['auth_openid_enable'])
        {
            return false;
        }
        
        return true;
    }
}
?>