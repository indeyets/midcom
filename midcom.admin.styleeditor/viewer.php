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
            /**
             * Delete a file
             * 
             * Match /files/<filename>/delete/
             */
            'file_delete' => array
            (
                'handler' => array ('midcom_admin_styleeditor_handler_file', 'delete'),
                'fixed_args' => array('files'),
                'variable_args' => 2,
            ),
        );
    }
    function navigation()
    {
        /*
        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

        // Get list of style elements related to this component
        $style_elements = $_MIDCOM->style->get_component_default_elements($this->_request_data['topic']->component);
        ksort($style_elements);
        echo "<ul class=\"midgard_admin_asgard_navigation\">";
        foreach ($style_elements as $style_element => $filename)
        {
            echo "<li class='midcom_baseclasses_database_style'><a href=\"{$prefix}__mfa/asgard_midcom.admin.styleeditor/edit/{$style_element}/\">&lt;({$style_element})&gt;</a></li>";
        }
        echo "</ul>";
        */
    }

}
?>
