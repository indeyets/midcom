<?php
/**
 * @package net.nemein.netmon 
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is the interface class for net.nemein.netmon
 * 
 * @package net.nemein.netmon
 */
class net_nemein_netmon_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     *
     * Nothing fancy, loads all script files and the datamanager library.
     */
    function net_nemein_netmon_interface()
    {
        parent::__construct();
        $this->_component = 'net.nemein.netmon';

        // We need classes from here to extend from, thus we need to load it here and now
        $_MIDCOM->componentloader->load('org.openpsa.contacts');

        // Load all mandatory class files of the component here
        $this->_autoload_files = array
        (
            'host.php', 
            'hostgroup.php', 
            'hostgroup_member.php',
            'helpers.php',
            'contact.php',
            'contactgroup.php',
            'viewer.php', 
            'navigation.php'
        );

        // Load all libraries used by component here
        $this->_autoload_libraries = array
        (
            'midcom.helper.datamanager2'
        );
    }

}
?>