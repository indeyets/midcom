<?php

/**
 * @package cc.kaktus_pearserver
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: interfaces.php 5041 2007-01-20 16:42:27Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * PEAR server MidCOM interface class.
 *
 * @package cc.kaktus_pearserver
 */
class cc_kaktus_pearserver_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     */
    function cc_kaktus_pearserver_interface()
    {
        parent::midcom_baseclasses_components_interface();

        $this->_component = 'cc.kaktus.pearserver';
        $this->_autoload_files = Array
        (
            'viewer.php',
//            'admin.php',
            'navigation.php'
        );
        
        $this->_autoload_libraries = Array
        (
            'midcom.helper.datamanager2',
        );
    }
    

    function _on_initialize()
    {
        // We need the contacts organization class available.
        $_MIDCOM->componentloader->load('org.openpsa.products');
        
        return true;
    }
    
    /**
     * Simple lookup method which tries to map a release
     * 
     * @TODO: write this to track down
     * @return string    Link under the current topic or null if not found
     */
    function _on_resolve_permalink($topic, $config, $guid)
    {
        return '';
    }
}
?>
