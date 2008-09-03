<?php
/**
 * @package net.fernmark.pedigree 
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is the interface class for net.fernmark.pedigree
 * 
 * @package net.fernmark.pedigree
 */
class net_fernmark_pedigree_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     *
     * Nothing fancy, loads all script files and the datamanager library.
     */
    function __construct()
    {
        parent::__construct();
        $this->_component = 'net.fernmark.pedigree';

        // Load all mandatory class files of the component here
        $this->_autoload_files = array
        (
            'dog.php',
            'result.php',
            'helpers.php',
            'viewer.php', 
            'navigation.php',
        );
        
        // Load all libraries used by component here
        $this->_autoload_libraries = array
        (
            'midcom.helper.datamanager2'
        );
    }

    function _on_initialize()
    {
        define('NET_FERMARK_PEDIGREE_SEX_MALE', 10);
        define('NET_FERMARK_PEDIGREE_SEX_FEMALE', 20);
        $_MIDCOM->componentloader->load('org.openpsa.contacts');
        return true;
    }

}
?>