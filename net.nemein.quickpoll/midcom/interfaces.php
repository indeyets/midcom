<?php
/**
 * @package net.nemein.quickpoll 
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is the interface class for net.nemein.quickpoll
 * 
 * @package net.nemein.quickpoll
 */
class net_nemein_quickpoll_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     *
     * Nothing fancy, loads all script files and the datamanager library.
     */
    function net_nemein_quickpoll_interface()
    {
        parent::midcom_baseclasses_components_interface();

        define('NET_NEMEIN_QUICKPOLL_LEAFID_ARCHIVE', 1);

        $this->_component = 'net.nemein.quickpoll';

        // Load all mandatory class files of the component here
        $this->_autoload_files = array
        (
            'viewer.php', 
            'admin.php', 
            'navigation.php',
            'option.php',
            'vote.php',
        );
        
        // Load all libraries used by component here
        $this->_autoload_libraries = array
        (
            'midcom.helper.datamanager2'
        );
    }

}
?>
