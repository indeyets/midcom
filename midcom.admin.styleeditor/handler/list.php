<?php
/**
 * @package midcom.admin.styleeditor
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id: viewer.php 3975 2006-09-06 17:36:03Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Style editor class for listing style elements
 * 
 * @package midcom.admin.styleeditor
 */
class midcom_admin_styleeditor_handler_list extends midcom_baseclasses_components_handler
{
    /**
     * Array storing component of topics using the current style path
     * 
     * @access private
     * @var mixed $_components
     */
    var $_components = array ();
    
    /**
     * Array containing names of the style elements
     * 
     * @access private
     * @var mixed $_style_elements
     */
    var $_style_elements = array ();
    
    /**
     * Simple constructor
     * 
     * @access public
     */
    function midcom_admin_styleeditor_handler_list()
    {
        parent::midcom_baseclasses_components_handler();
        
        // Add style sheets
        $_MIDCOM->add_link_head
        (
            array
            (
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL . '/midcom.admin.styleeditor/folder.css',
            )
        );
        $_MIDCOM->add_link_head
        (
            array
            (
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL . '/midcom.admin.styleeditor/style-editor.css',
            )
        );
        
        $_MIDCOM->add_link_head
        (
            array
            (
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL . '/midcom.admin.styleeditor/twisty.css',
                'media' => 'screen',
            )
        );
        
        $_MIDCOM->add_link_head
        (
            array
            (
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL . '/midcom.admin.styleeditor/twisty_print.css',
                'media' => 'screen',
            )
        );
        
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL.'/midcom.admin.styleeditor/twisty.js');
    }
    
    function _update_breadcrumb()
    {
        // Populate breadcrumb
        $tmp = Array();
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "__mfa/asgard_midcom.admin.styleeditor/",
            MIDCOM_NAV_NAME => $_MIDCOM->i18n->get_string('edit layout template', 'midcom.admin.styleeditor'),
        );
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
    }
    
    /**
     * Handler method for listing style elements for the currently used component topic
     *
     * @access private
     * @param string $handler_id Name of the used handler
     * @param mixed $args Array containing the variable arguments passed to the handler
     * @param mixed $data Data passed to the show method
     * @return boolean Indicating successful request
     */
    function _handler_list($handler_id, $args, &$data)
    {
        $this->_topic->require_do('midcom.admin.styleeditor:template_management');
        
        $data['view_title'] = $_MIDCOM->i18n->get_string('edit style', 'midcom.admin.styleeditor');
        $_MIDCOM->set_pagetitle($data['view_title']);
        
        $data['asgard_toolbar'] = new midcom_helper_toolbar();
        
        $data['asgard_toolbar']->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => "__mfa/asgard_midcom.admin.styleeditor/create/",
                MIDCOM_TOOLBAR_LABEL => $_MIDCOM->i18n->get_string('create a new element', 'midcom.admin.styleeditor'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/text-x-generic-template.png',
            )
        );        
        $data['asgard_toolbar']->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => "__mfa/asgard_midcom.admin.styleeditor/files/",
                MIDCOM_TOOLBAR_LABEL => $_MIDCOM->i18n->get_string('style attachments', 'midcom.admin.styleeditor'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/attach.png',
            )
        );

        $data['asgard_toolbar']->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX),
                MIDCOM_TOOLBAR_LABEL => $_MIDCOM->i18n->get_string('back to site', 'midgard.admin.asgard'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/gohome.png',
            )
        );

        $data['asgard_toolbar']->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)."/midcom-logout-",
                MIDCOM_TOOLBAR_LABEL => $_MIDCOM->i18n->get_string('logout','midcom'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/exit.png',
            )
        );

        // Set the variables
        $this->_component = $this->_topic->component;
        
        // Figure out what style object we're using
        $style_id = $_MIDCOM->style->get_style_id_from_path($this->_topic->style);
        if (!$style_id)
        {
            // Broken style link
            // TODO: Should we remove the style link and update topic?
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to resolve folder style \"{$this->_topic->style}\", aborting.");
            return false;
        }
        
        // Load the style object and check ACL
        $data['style'] = new midcom_db_style($style_id);
        $_MIDCOM->bind_view_to_object($data['style']);
        
        $this->_update_breadcrumb();
        
        // Get list of style elements and nodes related to this style
        $style_information = $_MIDCOM->style->get_style_elements_and_nodes($this->_topic->style);
        $this->_style_elements = $style_information['elements'];
        $this->_components = $style_information['nodes'];

        // Disable the "Edit template" button when we're at its view
        $this->_view_toolbar->hide_item("__mfa/asgard_midcom.admin.styleeditor/");
        
        // Ensure we get the correct styles
        $_MIDCOM->style->prepend_component_styledir('midgard.admin.asgard');
        $_MIDCOM->style->prepend_component_styledir('midcom.admin.styleeditor');
        $_MIDCOM->skip_page_style = true;
        
        // Get the list of all components
        $_MIDCOM->load_library('midcom.admin.folder');
        require_once(MIDCOM_ROOT . '/midcom/admin/folder/folder_management.php');
        $this->_component_list = midcom_admin_folder_folder_management::get_component_list();
        
        $this->_component_list['midcom'] = array
        (
            'name' => $_MIDCOM->i18n->get_string('common elements', 'midcom.admin.styleeditor'),
            'description' => $_MIDCOM->i18n->get_string('elements automatically included by midcom', 'midcom.admin.styleeditor'),
        );
        
        return true;
    }
    
    /**
     * Show list of the style elements for the currently edited topic component
     * 
     * @access private
     * @param string $handler_id Name of the used handler
     * @param mixed $data Data passed to the show method
     */
    function _show_list($handler_id, &$data)
    {
        midcom_show_style('midgard_admin_asgard_header');
        midcom_show_style('midgard_admin_asgard_middle');
        
        $data['nap'] = new midcom_helper_nav();
        $data['folder'] =& $this->_topic;
        $data['style_elements'] = $this->_style_elements;
        $data['current_component'] = $this->_topic->component;
        
        // TODO: Fix either the midcom.admin.help interface class or something else to
        // use autoload libraries instead of componentloader
        $_MIDCOM->componentloader->load('midcom.admin.help');
        
        $help = new midcom_admin_help_help();
        midcom_show_style('midcom-admin-styleeditor-stylelist-header');
        
        // Show list of components using the same style as the current topic
        foreach ($this->_components as $component => $topics)
        {
            if (!array_key_exists($component, $this->_component_list))
            {
                // There are folders using an uninstalled component. Don't display them
                continue;
            }
            
            $data['component'] = $component;
            $data['component_details'] = $this->_component_list[$component];
            $data['topics'] = $topics;
            $data['help'] = preg_replace('/&lt;\(([a-zA-Z0-9\-_]+)\)>/', '<code><a href="edit/\1/">&lt;(\1)&gt;</a></code>', $help->get_help_contents('style', $component));
            $data['style_elements'] = $this->_style_elements[$component];
            
            if ($data['component'] === $data['current_component'])
            {
                $data['display'] = 'block';
            }
            else
            {
                $data['display'] = 'none';
            }
            
            midcom_show_style('midcom-admin-styleeditor-style-component-header');
            
            foreach ($topics as $topic)
            {
                $data['topic'] = $topic;
                midcom_show_style('midcom-admin-styleeditor-style-component-item');
            }
            
            midcom_show_style('midcom-admin-styleeditor-style-component-footer');
        }
        
        midcom_show_style('midcom-admin-styleeditor-stylelist-all-header');
        
        // List all the components
        foreach ($this->_style_elements as $component => $style_elements)
        {
            if (!array_key_exists($component, $this->_component_list))
            {
                // There are folders using an uninstalled component. Don't display them
                continue;
            }
                    
            $data['component'] = $component;
            $data['component_details'] = $this->_component_list[$component];
            $data['style_elements'] = $style_elements;
            $data['display'] = 'none';
            
            midcom_show_style('midcom-admin-styleeditor-stylelist-all-elements');
        }
        
        midcom_show_style('midcom-admin-styleeditor-stylelist-all-footer');
        
        midcom_show_style('midcom-admin-styleeditor-stylelist-footer');
        midcom_show_style('midgard_admin_asgard_footer');    
    }
}
?>
