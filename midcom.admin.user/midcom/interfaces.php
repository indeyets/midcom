<?php
/**
 * @package midcom.admin.user
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Style editor class for listing style elements
 * 
 * @package midcom.admin.user
 */
class midcom_admin_user_interface extends midcom_baseclasses_components_interface 
{
    function midcom_admin_user_interface() 
    {
        parent::midcom_baseclasses_components_interface();

        $this->_component = 'midcom.admin.user';
        $this->_purecode = true;
        
        $this->_autoload_libraries = array
        (
            'midcom.helper.datamanager2',
        );
    }
}
?>
