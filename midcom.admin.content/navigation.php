<?php
/**
 * @package midcom.admin.content
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * MidCOM NAP dummy interface class.
 * 
 * Does not return anything to hide NAP from the navigation.
 * 
 * @package midcom.admin.content
 */
class midcom_admin_content_navigation extends midcom_baseclasses_components_navigation
{
    function get_node() 
    {
        return null;
    }
    
    function get_leaves() 
    {
        return null;
    }
} 


?>