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
class midcom_admin_styleeditor_handler_create extends midcom_baseclasses_components_handler
{
    /**
     * Array containing names of the style elements
     * 
     * @access private
     * @var mixed $_style_elements
     */
    var $_style_elements = array ();
    
    var $_component = '';
    
    /**
     * Simple constructor
     * 
     * @access public
     */
    function midcom_admin_styleeditor_handler_create()
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
        
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL.'/midcom.admin.styleeditor/twisty.js');
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL.'/midcom.admin.styleeditor/midcom_admin_folder_styleeditor.js');
    }
    
    function _update_breadcrumb()
    {
        // Populate breadcrumb
        $tmp = Array();
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "__mfa/styleeditor/",
            MIDCOM_NAV_NAME => $_MIDCOM->i18n->get_string('edit layout template', 'midcom.admin.styleeditor'),
        );

        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "",
            MIDCOM_NAV_NAME => $_MIDCOM->i18n->get_string('create new element', 'midcom.admin.styleeditor'),
        );

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
    }
    
    function _process_form()
    {
    	$message = '';
    
		if (array_key_exists('midcom_admin_styleeditor_style_name', $_POST))
        {
        	if ($_POST['midcom_admin_styleeditor_style_name'] != null || !empty($_FILES))
        	{
        		if (!array_key_exists($_POST['midcom_admin_styleeditor_style_name'],$this->_style_elements['midcom']))                                                                                     
        		{                                                                                                                                 
                	$this->_request_data['style']->require_do('midgard:create');
                
                	// We don't have element yet, create
                	$this->_request_data['style_element_object'] = new midcom_db_element();
                	$this->_request_data['style_element_object']->style = $this->_request_data['style']->id;
                	
                
                	// do we have file uploaded?
                	// TODO: file should be text/plain only
                
                	if ($_FILES['midcom_admin_styleeditor_style_file']['tmp_name'])
                	{
                		$value = file_get_contents($_FILES['midcom_admin_styleeditor_style_file']['tmp_name']);
                		$name = midcom_generate_urlname_from_string(basename($_FILES['midcom_admin_styleeditor_style_file']['name'],'php'));
                	}
                	else
                	{
                		$value = $_POST['midcom_admin_styleeditor_style_edit'];
                		$name = $_POST['midcom_admin_styleeditor_style_name'];
                	}
                	$this->_request_data['style_element_object']->value = $value;
                	$this->_request_data['style_element_object']->name = $name;
                	
                	if (!$this->_request_data['style_element_object']->create())
                	{
                    	$message = sprintf($_MIDCOM->i18n->get_string('failed to create a new element: %s'), mgd_errstr());
                	}
                
                	mgd_cache_invalidate();
                	$_MIDCOM->relocate("__mfa/styleeditor/edit/".$this->_request_data['style_element_object']->name."/");
	            }
    	        else
        	    {
            		$message = sprintf($_MIDCOM->i18n->get_string('element %s exists', 'midcom.admin.styleeditor'),$_POST['midcom_admin_styleeditor_style_name']);
            	}
            }
            else
	        {
    	    	$message = $_MIDCOM->i18n->get_string('element name required', 'midcom.admin.styleeditor');
        	}
        }
        
        $this->_request_data['message'] = $message;

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
    function _handler_create($handler_id, $args, &$data)
    {
        $this->_topic->require_do('midcom.admin.styleeditor:template_management');
            
        $style_information = $_MIDCOM->style->get_style_elements_and_nodes($this->_topic->style);
        $this->_style_elements = $style_information['elements'];
        
        // Topic must have a defined style in order to be editable
        if ($this->_topic->style == '')
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Folder \"{$this->_topic->extra}\" does not have a style defined, aborting.");
        }
        
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
        
        $this->_process_form();

        // Ensure we get the correct styles
        $_MIDCOM->style->prepend_component_styledir('midcom.admin.styleeditor');
        
        $this->_update_breadcrumb();
        
        // Skip the page styles
        $_MIDCOM->skip_page_style = true;
        
        return true;
    }
    
    /**
     * Show the editing view for the requested style
     * 
     * @access private
     * @param string $handler_id Name of the used handler
     * @param mixed $data Data passed to the show method
     */
    function _show_create($handler_id, &$data)
    {
        midcom_show_style('midcom-admin-styleeditor-style-page-header');
        midcom_show_style('midcom-admin-styleeditor-style-create');
        midcom_show_style('midcom-admin-styleeditor-style-page-footer');
    }
}
?>