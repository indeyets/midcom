<?php
/**
 * @package midcom.admin.styleeditor
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Style editor interface for on-site editing of style elements, CSS and JavaScript
 * files and pictures
 * 
 * @package midcom.admin.styleeditor
 */
class midcom_admin_styleeditor_viewer extends midcom_baseclasses_components_request
{
    /**
     * Constructor. This probably isn't called in normal plugin users.
     * 
     * @access public
     */
    function midcom_admin_styleeditor_viewer()
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
        $_MIDCOM->load_library('midcom.admin.folder');
        
        return array
        (
            /**
             * Style editor for onsite style editing
             */
            /**
             * List style elements for topics/components using the style
             * 
             * Match /style-editor/
             */
            'style_list' => array
            (
                'handler' => array ('midcom_admin_styleeditor_handler_list', 'list'),
            ),
            /**
             * Edit a style element
             * 
             * Match /style-editor/edit/<style element name>/
             */
            'style_element_edit' => array
            (
                'handler' => array ('midcom_admin_styleeditor_handler_edit', 'edit'),
                'fixed_args' => array ('edit'),
                'variable_args' => 1,
            ),
            /**
    		 * Create new style element
    		 *
    		 * Match /create/
    		 *
    		 */
    		 'style_element_create' => array
    		 (
    		 	'handler' => array ('midcom_admin_styleeditor_handler_create','create'),
    		 	'fixed_args' => array ('create'),
    		 ),
            /**
             * Create a new file
             * 
             * Match /files/
             */
            'file_new' => array
            (
                'handler' => array ('midcom_admin_styleeditor_handler_file', 'new'),
                'fixed_args' => array ('files'),
            ),
            /**
             * Edit a file
             * 
             * Match /files/<filename>
             */
            'file_edit' => array
            (
                'handler' => array ('midcom_admin_styleeditor_handler_file', 'edit'),
                'fixed_args' => array ('files'),
                'variable_args' => 1,
            ),
        );
    }
}
?>
