<?php
/**
 * @package midgard.admin.sitegroup
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * XML Component Interface Class. This is a pure code library.
 * 
 * @package midgard.admin.sitegroup
 */
class midgard_admin_sitegroup_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     */
    function midgard_admin_sitegroup_interface()
    {
        parent::__construct();
        
        $this->_component = 'midgard.admin.sitegroup';
        $this->_autoload_files = Array
        (
            'creation/base.php',
            'creation/sitegroup.php',
            'creation/host.php',
            'creation/config/config.php',
        );
              
    }
    
    
}

?>