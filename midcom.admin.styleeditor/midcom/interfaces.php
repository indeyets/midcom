<?php
/**
 * @package midcom.admin.styleeditor
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Style editor class for listing style elements
 * 
 * @package midcom.admin.styleeditor
 */
class midcom_admin_styleeditor_interface extends midcom_baseclasses_components_interface 
{
    function midcom_admin_styleeditor_interface() 
    {
        parent::__construct();

        $this->_component = 'midcom.admin.styleeditor';
        $this->_purecode = true;
        
        $this->_autoload_libraries = array
        (
            'midcom.admin.folder',
        );
    }
}
?>