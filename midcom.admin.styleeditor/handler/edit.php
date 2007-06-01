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
class midcom_admin_styleeditor_handler_edit extends midcom_baseclasses_components_handler
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
    function midcom_admin_styleeditor_handler_edit()
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
    
    function _load_default_elements()
    {
        $this->_request_data['style_element_default_path'] = $this->_style_elements[$this->_component][$this->_request_data['style_element']];
        
        if (file_exists($this->_request_data['style_element_default_path']))
        {
            $this->_request_data['style_element_default_contents'] = file_get_contents($this->_request_data['style_element_default_path']);
        }
        else
        {
            $this->_request_data['style_element_default_contents'] = '';
        }
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
            MIDCOM_NAV_URL => "__mfa/styleeditor/edit/{$this->_request_data['style_element']}/",
            MIDCOM_NAV_NAME => sprintf($_MIDCOM->i18n->get_string('edit element %s', 'midcom.admin.styleeditor'), "<({$this->_request_data['style_element']})>"),
        );

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
    }
    
    function _process_form()
    {
        if (array_key_exists('midcom_admin_styleeditor_style_delete', $_POST))
        {
            if (!isset($this->_request_data['style_element_object']))
            {
                return false;
            }
            
            $this->_request_data['style_element_object']->require_do('midgard:delete');
            
            $this->_request_data['style_element_object']->delete();
            
            mgd_cache_invalidate();            
            
            unset($this->_request_data['style_element_object']);

        }
        elseif (array_key_exists('midcom_admin_styleeditor_style_save', $_POST))
        {
           	// do we have file uploaded?
          	// TODO: file should be text/plain only
                
            if ($_FILES['midcom_admin_styleeditor_style_file']['tmp_name'])
            {
            	$value = file_get_contents($_FILES['midcom_admin_styleeditor_style_file']['tmp_name']);
            }
            else
            {
            	$value = $_POST['midcom_admin_styleeditor_style_edit'];
            }

            // User is saving, do it
            if (!isset($this->_request_data['style_element_object']))
            {
                $this->_request_data['style']->require_do('midgard:create');
                
                // We don't have element yet, create
                $this->_request_data['style_element_object'] = new midcom_db_element();
                $this->_request_data['style_element_object']->style = $this->_request_data['style']->id;
                $this->_request_data['style_element_object']->name = $this->_request_data['style_element'];
                $this->_request_data['style_element_object']->value = $value;
                
                if (!$this->_request_data['style_element_object']->create())
                {
                    $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Failed to create a new element, cannot continue. Last Midgard error was: '. mgd_errstr());
                }
                
                mgd_cache_invalidate();
                
                $_MIDCOM->bind_view_to_object($this->_request_data['style_element_object']);
            }
            else
            {
                $this->_request_data['style_element_object']->require_do('midgard:update');
                
                $this->_request_data['style_element_object']->value = $value;

                if (!$this->_request_data['style_element_object']->update())
                {
                    $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Failed to update the element, cannot continue. Last Midgard error was: '. mgd_errstr());
                }
                
                mgd_cache_invalidate();
            }
        }
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
    function _handler_edit($handler_id, $args, &$data)
    {
        $this->_topic->require_do('midcom.admin.styleeditor:template_management');
            
        $data['style_element'] = $args[0];
        
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
        
        // Requesting to edit style element which doesn't exist
        $style_element_found = false;
        foreach ($this->_style_elements as $component => $elements)
        {
            if (isset($elements[$data['style_element']]))
            {
                $style_element_found = true;
                $this->_component = $component;
            }
        }
        if (!$style_element_found)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Element \"{$data['style_element']}\" does not exist in elements list, aborting.");
            return false;
        }
        
        // Load the style object and check ACL
        $data['style'] = new midcom_db_style($style_id);
        
        // See if we have a style element already
        $qb = midcom_db_element::new_query_builder();
        $qb->add_constraint('name', '=', $data['style_element']);
        $qb->add_constraint('style', '=', $data['style']->id);
        $elements = $qb->execute();
        if (count($elements) > 0)
        {
            $data['style_element_object'] = $elements[0];
            $_MIDCOM->bind_view_to_object($data['style_element_object']);
        }
        
        $this->_process_form();

        $this->_load_default_elements();
        
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
    function _show_edit($handler_id, &$data)
    {
        midcom_show_style('midcom-admin-styleeditor-style-page-header');
        midcom_show_style('midcom-admin-styleeditor-style-edit');
        midcom_show_style('midcom-admin-styleeditor-style-page-footer');
    }
}
?>