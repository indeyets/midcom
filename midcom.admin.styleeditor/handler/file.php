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
class midcom_admin_styleeditor_handler_file extends midcom_baseclasses_components_handler
{
    /**
     * Current loaded style
     *
     * @var midcom_db_style
     * @access private
     */
    var $_style = null;

    /**
     * Files in the current style
     *
     * @var array
     * @access private
     */
    var $_files = array();

    /**
     * Current file being edited
     *
     * @var midcom_baseclasses_database_attachment
     * @access private
     */
    var $_file = null;

    /**
     * Simple constructor
     * 
     * @access public
     */
    function midcom_admin_styleeditor_handler_file()
    {
        //$this->_component = 'midcom.admin.styleeditor';
        parent::midcom_baseclasses_components_handler();

        $_MIDCOM->add_link_head
        (
            array
            (
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL . '/midcom.admin.styleeditor/style-editor.css',
            )
        );
        
    }
    

    function _prepare_toolbar(&$data)
    {

        // Set the Asgard toolbar
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
                MIDCOM_TOOLBAR_URL => $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)."midcom-logout-",
                MIDCOM_TOOLBAR_LABEL => $_MIDCOM->i18n->get_string('logout','midcom'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/exit.png',
            )
        );

    }

    /**
     * Rewrite a filename to URL safe form
     *
     * @param $filename string file name to rewrite
     * @return string rewritten filename
     */
    function safe_filename($filename)
    {
        $filename = basename(trim($filename));

        $regex = '/^(.*)(\..*?)$/';

        if (preg_match($regex, $filename, $ext_matches))
        {
            $name = $ext_matches[1];
            $ext = $ext_matches[2];
        }
        else
        {
            $name = $filename;
            $ext = '';
        }
        return midcom_generate_urlname_from_string($name) . $ext;
    }
    
    function _load_config()
    {
        $_MIDCOM->load_library('midcom.admin.styleeditor');
        $this->_config = $GLOBALS['midcom_component_data']['midcom.admin.styleeditor']['config'];
    }
    
    /**
     * Load the style associated with the topic and populate it to the _style private property
     * @return boolean
     */
    function _load_style()
    {
        // Topic must have a defined style in order to be editable
        if ($this->_topic->style == '')
        {
            return false;
        }
        
        // Figure out what style object we're using
        $style_id = $_MIDCOM->style->get_style_id_from_path($this->_topic->style);
        if (!$style_id)
        {
            // Broken style link
            return false;
        }
        
        $this->_style = new midcom_db_style($style_id);
        if (   !$this->_style
            || !$this->_style->id)
        {
            return false;
        }
        
        return true;
    }
       
    function _update_breadcrumb($handler_id)
    {
        // Populate breadcrumb
        $tmp = Array();
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => '__mfa/asgard_midcom.admin.styleeditor/',
            MIDCOM_NAV_NAME => $_MIDCOM->i18n->get_string('edit layout template', 'midcom.admin.styleeditor'),
        );
        

        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "__mfa/asgard_midcom.admin.styleeditor/files/",
            MIDCOM_NAV_NAME => $_MIDCOM->i18n->get_string('style attachments', 'midcom.admin.styleeditor'),
        );
        
        switch ($handler_id)
        {
            case '____mfa-asgard_midcom.admin.styleeditor-file_edit':
                $tmp[] = Array
                (
                    MIDCOM_NAV_URL => "__mfa/asgard_midcom.admin.styleeditor/files/{$this->_request_data['filename']}/",
                    MIDCOM_NAV_NAME => $this->_request_data['filename'],
                );
                break;
            case '____mfa-asgard_midcom.admin.styleeditor-file_delete':
                $tmp[] = array
                (
                    MIDCOM_NAV_URL => "__mfa/asgard_midcom.admin.styleeditor/files/{$this->_request_data['filename']}/",
                    MIDCOM_NAV_NAME => sprintf($_MIDCOM->i18n->get_string('%s', 'midcom.admin.styleeditor'), $this->_request_data['filename']),
                );
                $tmp[] = array
                (
                    MIDCOM_NAV_URL => "__mfa/asgard_midcom.admin.styleeditor/files/{$this->_request_data['filename']}/delete/",
                    MIDCOM_NAV_NAME => $_MIDCOM->i18n->get_string('delete', 'midcom'),
                );
                break;
        }

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
    }
    
    function _process_file_upload($uploaded_file)
    {
        if (is_null($this->_file))
        {
            $local_filename = $this->safe_filename($uploaded_file['name']);
            $local_file = $this->_get_file($local_filename);
            if (!$local_file)
            {
                // New file, create
                $local_file = new midcom_baseclasses_database_attachment();
                $local_file->name = $local_filename;
                $local_file->parentguid = $this->_style->guid;
                $local_file->mimetype = $uploaded_file['type'];
                
                // Legacy data, TODO: Remove
                $local_file->ptable = 'style';
                $local_file->pid = $this->_style->id;
                
                if (!$local_file->create())
                {
                    $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Failed to create attachment, reason: ' . mgd_errstr());
                    // This will exit.
                }
            }
        }
        else
        {
            $local_file = $this->_file;
        }
        

        if ($local_file->mimetype != $uploaded_file['type'])
        {
            $local_file->mimetype = $uploaded_file['type'];
            $local_file->update();
        }
        
        if (!$local_file->copy_from_file($uploaded_file['tmp_name']))
        {
            return false;
        }
        return $local_file->name;
    }
    
    function _process_form()
    {
        if (!isset($_POST['midcom_admin_styleeditor_save']))
        {
            return false;
        }
        
        // Check if we have an uploaded file
        if (   isset($_FILES['midcom_admin_styleeditor_file'])
            && is_uploaded_file($_FILES['midcom_admin_styleeditor_file']['tmp_name']))
        {
            return $this->_process_file_upload($_FILES['midcom_admin_styleeditor_file']);
        }
        
        if (is_null($this->_file))
        {
            if (   !isset($_POST['midcom_admin_styleeditor_filename'])
                || empty($_POST['midcom_admin_styleeditor_filename']))
            {
                return false;
            }
        
            // We're creating a new file
            $local_filename = $this->safe_filename($_POST['midcom_admin_styleeditor_filename']);
            $local_file = $this->_get_file($local_filename);
            if (!$local_file)
            {
                // New file, create
                $local_file = new midcom_baseclasses_database_attachment();
                $local_file->name = $local_filename;
                $local_file->parentguid = $this->_style->guid;
                
                // Legacy data, TODO: Remove
                $local_file->ptable = 'style';
                $local_file->pid = $this->_style->id;
                
                if (!$local_file->create())
                {
                    $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Failed to create attachment, reason: ' . mgd_errstr());
                    // This will exit.
                }
            }
        }
        else
        {
            $local_file = $this->_file;
        }
        
        $success = true;

        if (   isset($_POST['midcom_admin_styleeditor_filename'])
            && !empty($_POST['midcom_admin_styleeditor_filename'])
            && $local_file->name != $_POST['midcom_admin_styleeditor_filename'])
        {
            $local_file->name = $_POST['midcom_admin_styleeditor_filename'];
            
            if (!$local_file->update())
            {
                $success = false;
            }
        }

        if (   isset($_POST['midcom_admin_styleeditor_mimetype'])
            && !empty($_POST['midcom_admin_styleeditor_mimetype'])
            && $local_file->mimetype != $_POST['midcom_admin_styleeditor_mimetype'])
        {
            $local_file->mimetype = $_POST['midcom_admin_styleeditor_mimetype'];
            
            if (!$local_file->update())
            {
                $success = false;
            }
        }

        // We should always store at least an empty string so it can be edited later
        $contents = '';
        if (   isset($_POST['midcom_admin_styleeditor_contents'])
            && !empty($_POST['midcom_admin_styleeditor_contents']))
        {
            $contents = $_POST['midcom_admin_styleeditor_contents'];
        }
        
        if (!$local_file->copy_from_memory($contents))
        {
            $success = false;
        }
        
        if (!$success)
        {
            return false;
        }
        return $local_file->name;
    }
    
    function _get_file($filename)
    {
        $qb = midcom_baseclasses_database_attachment::new_query_builder();
        $qb->add_constraint('parentguid', '=', $this->_style->guid);
        
        $qb->add_constraint('name', '=', $filename);
        
        $files = $qb->execute();
        if (empty($files))
        {
            return false;
        }
        return $files[0];
    }
    
    function _list_files()
    {
        $qb = midcom_baseclasses_database_attachment::new_query_builder();
        $qb->add_constraint('parentguid', '=', $this->_style->guid);
        $qb->add_order('mimetype');
        $qb->add_order('score', 'DESC');
        $qb->add_order('name');
        $this->_files = $qb->execute();
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
    function _handler_new($handler_id, $args, &$data)
    {
        $this->_load_config();
        if (!$this->_load_style())
        {
            return false;
        }
        $this->_topic->require_do('midcom.admin.styleeditor:template_management');
        $this->_style->require_do('midgard:attachments');
        
        $filename = $this->_process_form();
        if (!$filename)
        {
            // Show error
        }
        else
        {
            $_MIDCOM->relocate("__mfa/asgard_midcom.admin.styleeditor/files/{$filename}/");
        }
        
        // Ensure we get the correct styles
        $_MIDCOM->style->prepend_component_styledir('midgard.admin.asgard');
        $_MIDCOM->style->prepend_component_styledir('midcom.admin.styleeditor');
        
        $this->_update_breadcrumb($handler_id);
        $this->_list_files();
        
        // Skip the page styles
        $_MIDCOM->skip_page_style = true;
        
        $this->_prepare_toolbar($data);

        // Add the page title
        $data['view_title'] = $_MIDCOM->i18n->get_string('style attachments', 'midcom.admin.styleeditor');
        
        return true;
    }
    
    /**
     * Show the editing view for the requested style
     * 
     * @access private
     * @param string $handler_id Name of the used handler
     * @param mixed $data Data passed to the show method
     */
    function _show_new($handler_id, &$data)
    {
        midcom_show_style('midgard_admin_asgard_header');
        midcom_show_style('midgard_admin_asgard_middle');
        
        $data['files'] =& $this->_files;
        midcom_show_style('midcom-admin-styleeditor-files-header');

        $data['text_types'] = $this->_config->get('text_types');
        midcom_show_style('midcom-admin-styleeditor-files-new');
        midcom_show_style('midcom-admin-styleeditor-files-footer');
        midcom_show_style('midgard_admin_asgard_footer');
    }

    /**
     * Handler method for listing style elements for the currently used component topic
     *
     * @access public
     * @param string $handler_id Name of the used handler
     * @param mixed $args Array containing the variable arguments passed to the handler
     * @param mixed $data Data passed to the show method
     * @return boolean Indicating successful request
     */
    function _handler_edit($handler_id, $args, &$data)
    {
        $this->_load_config();
        $data['filename'] = $args[0];
        if (!$this->_load_style())
        {
            return false;
        }
        $this->_topic->require_do('midcom.admin.styleeditor:template_management');
        $this->_style->require_do('midgard:attachments');
        
        $this->_file = $this->_get_file($data['filename']);
        if (!$this->_file)
        {
            return false;
        }
        $this->_file->require_do('midgard:update');
        $_MIDCOM->bind_view_to_object($this->_file);
        
        $filename = $this->_process_form();
        if (!$filename)
        {
            // Show error
        }
        else
        {
            if ($filename != $data['filename'])
            {
                $_MIDCOM->relocate("__mfa/asgard_midcom.admin.styleeditor/files/{$filename}/");
            }
        }
        
        // Ensure we get the correct styles
        $_MIDCOM->style->prepend_component_styledir('midgard.admin.asgard');
        $_MIDCOM->style->prepend_component_styledir('midcom.admin.styleeditor');
        
        $this->_update_breadcrumb($handler_id);
        $this->_list_files();
        
        // Skip the page styles
        $_MIDCOM->skip_page_style = true;
        
        // Add the codepress syntax highlight
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL . '/midcom.helper.datamanager2/codepress/codepress.js');
        
        // Add the page title
        $data['view_title'] = sprintf($_MIDCOM->i18n->get_string('edit file %s', 'midcom.admin.styleeditor'), "'{$args[0]}'");

        $this->_prepare_toolbar($data);        
        
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
        midcom_show_style('midgard_admin_asgard_header');
        midcom_show_style('midgard_admin_asgard_middle');
        
        $data['files'] =& $this->_files;
        $data['file'] =& $this->_file;
        midcom_show_style('midcom-admin-styleeditor-files-header');
        
        $data['text_types'] = $this->_config->get('text_types');
        midcom_show_style('midcom-admin-styleeditor-files-file');
        midcom_show_style('midcom-admin-styleeditor-files-footer');
        midcom_show_style('midgard_admin_asgard_footer');    
    }
    
    /**
     * Handler method for confirming file deleting for the requested file
     *
     * @access public
     * @param string $handler_id Name of the used handler
     * @param mixed $args Array containing the variable arguments passed to the handler
     * @param mixed $data Data passed to the show method
     * @return boolean Indicating successful request
     */
    function _handler_delete($handler_id, $args, &$data)
    {
        $this->_load_config();
        $data['filename'] = $args[0];
        if (!$this->_load_style())
        {
            return false;
        }
        $this->_topic->require_do('midcom.admin.styleeditor:template_management');
        $this->_style->require_do('midgard:attachments');
        
        $this->_file = $this->_get_file($data['filename']);
        if (!$this->_file)
        {
            return false;
        }
        
        // Require delete privilege
        $this->_file->require_do('midgard:delete');
        
        if (isset($_POST['f_cancel']))
        {
            $_MIDCOM->uimessages->add($_MIDCOM->i18n->get_string('midcom.admin.styleeditor', 'midcom.admin.styleeditor'), $_MIDCOM->i18n->get_string('delete cancelled', 'midcom.admin.styleeditor'));
            $_MIDCOM->relocate("__mfa/asgard_midcom.admin.styleeditor/files/{$data['filename']}/");
            // This will exit
        }
        
        if (isset($_POST['f_confirm']))
        {
            if ($this->_file->delete())
            {
                $_MIDCOM->uimessages->add($_MIDCOM->i18n->get_string('midcom.admin.styleeditor', 'midcom.admin.styleeditor'), sprintf($_MIDCOM->i18n->get_string('file %s deleted', 'midcom.admin.styleeditor'), $data['filename']));
                $_MIDCOM->relocate("__mfa/asgard_midcom.admin.styleeditor/files/");
                // This will exit
            }
            
            
        }
        
        $_MIDCOM->bind_view_to_object($this->_file);
        
        // Ensure we get the correct styles
        $_MIDCOM->style->prepend_component_styledir('midgard.admin.asgard');
        $_MIDCOM->style->prepend_component_styledir('midcom.admin.styleeditor');
        
        $this->_update_breadcrumb($handler_id);
        
        // Skip the page styles
        $_MIDCOM->skip_page_style = true;
        
        // Add the page title
        $data['view_title'] = sprintf($_MIDCOM->i18n->get_string('delete file %s', 'midcom.admin.styleeditor'), "'{$args[0]}'");
        
        $this->_prepare_toolbar($data);
        
        return true;
    }
    
    /**
     * Show the delete request
     * 
     * @access private
     * @param string $handler_id Name of the used handler
     * @param mixed $data Data passed to the show method
     */
    function _show_delete($handler_id, &$data)
    {
        midcom_show_style('midgard_admin_asgard_header');
        midcom_show_style('midgard_admin_asgard_middle');
        
        $data['file'] =& $this->_file;
        $data['text_types'] = $this->_config->get('text_types');
        midcom_show_style('midcom-admin-styleeditor-files-delete');
        midcom_show_style('midgard_admin_asgard_footer');    
    }
    
}
?>