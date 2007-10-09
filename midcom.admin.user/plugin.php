<?php
/**
 * @package midcom.admin.user
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * user editor interface for on-site editing of user elements, CSS and JavaScript
 * files and pictures
 * 
 * @package midcom.admin.user
 */
class midcom_admin_user_plugin extends midcom_baseclasses_components_request
{
    /**
     * Constructor. This probably isn't called in normal plugin users.
     * 
     * @access public
     */
    function midcom_admin_user_plugin()
    {
        parent::midcom_baseclasses_components_request();
    }
    
    /**
     * Get the plugin handlers, which act alike with Request Switches of MidCOM
     * Baseclasses Components (midcom.baseclasses.components.request)
     * 
     * @access public
     * @return mixed Array of the plugin handlers
     */
    function get_plugin_handlers()
    {
        $_MIDCOM->load_library('midgard.admin.asgard');
        $_MIDCOM->load_library('midcom.admin.user');
        
        return array
        (
            /**
             * user editor for onsite user editing
             */
            /**
             * List users
             * 
             * Match /user-editor/
             */
            'user_list' => array
            (
                'handler' => array ('midcom_admin_user_handler_list', 'list'),
            ),
            /**
             * Edit a user
             * 
             * Match /user-editor/edit/<guid>/
             */
            'user_edit' => array
            (
                'handler' => array ('midcom_admin_user_handler_user_edit', 'edit'),
                'fixed_args' => array ('edit'),
                'variable_args' => 1,
            ),
            /**
    		 * Create new user
    		 *
    		 * Match /create/
    		 *
    		 */
    		 'user_create' => array
    		 (
    		 	'handler' => array ('midcom_admin_user_handler_user_create','create'),
    		 	'fixed_args' => array ('create'),
    		 ),
            /**
             * Edit a group
             * 
             * Match /user-editor/group/edit/<guid>/
             */
            'group_edit' => array
            (
                'handler' => array ('midcom_admin_user_handler_group_edit', 'edit'),
                'fixed_args' => array ('group', 'edit'),
                'variable_args' => 1,
            ),
        );
    }
}
?>
