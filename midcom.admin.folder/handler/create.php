<?php
/**
 * @package midcom.admin.folder
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: configuration.php 3757 2006-07-27 14:32:42Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Create a new folder.
 *
 * @package midcom.admin.folder
 */
class midcom_admin_folder_handler_create extends midcom_baseclasses_components_handler
{
    /**
     * Processing message
     * 
     * @access private
     * @var string
     */
    var $_processing_msg = '';
    
    /**
     * New topic object
     * 
     * @access private
     */
    var $_newtopic = null;
    
    /**
     * Constructor metdot
     * 
     * @access public
     */
    function midcom_admin_folder_handler_create ()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * Handler for creating a new folder. Processes the creation of the folder
     * and relocates to the content folder depending on the user actions.
     * 
     * @access private
     * @return boolean Indicating success
     */
    function _handler_create($handler_id, $args, &$data)
    {
        // Initialize the strings for folder name and title
        $this->_request_data['folder_name'] = '';
        $this->_request_data['folder_title'] = '';
        $this->_request_data['error_message'] = '';
        
        $this->_topic->require_do('midgard:create');
        $this->_topic->require_do('midcom.admin.folder:topic_management');

        if (array_key_exists('f_cancel', $_REQUEST))
        {
            $_MIDCOM->relocate('');
            // This will exit.
        }

        if (array_key_exists('f_submit', $_REQUEST))
        {
            if ($this->_process_create_form())
            {
                $_MIDCOM->relocate("{$this->_newtopic->name}/");
                // This will exit.
            }
        }
        
        // Add the view to breadcrumb trail
        $tmp = array();
        $tmp[] = array
        (
            MIDCOM_NAV_URL => '__ais/folder/create.html',
            MIDCOM_NAV_NAME => $_MIDCOM->i18n->get_string('create subfolder', 'midcom.admin.folder'),
        );
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
        
        // Hide the button in toolbar
        $this->_node_toolbar->hide_item('__ais/folder/create.html');

        // Set page title
        $data['title'] = sprintf($_MIDCOM->i18n->get_string('create folder under %s', 'midcom.admin.folder'), $this->_topic->extra);
        $_MIDCOM->set_pagetitle($data['title']);
        
        // Set the help object in the toolbar
        $this->_view_toolbar->add_help_item('create_folder', 'midcom.admin.folder');
        
        // Ensure we get the correct styles
        $_MIDCOM->style->prepend_component_styledir('midcom.admin.folder');
        
        // Add style sheet
        $_MIDCOM->add_link_head
        (
            array
            (
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL . '/midcom.admin.folder/folder.css',
            )
        );
        
        return true;
    }
    
    /**
     * Processes the creation of a new folder.
     * 
     * @access private
     * @return boolean Indicating success
     */
    function _process_create_form()
    {
        if (trim($_REQUEST['f_title']) == '')
        {
            $this->_processing_msg = $_MIDCOM->i18n->get_string('title is empty', 'midcom.admin.folder');
            $_MIDCOM->uimessages->add($_MIDCOM->i18n->get_string('create subfolder', 'midcom.admin.folder'), $this->_processing_msg, 'error');
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add($this->_processing_msg, MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }

        if (trim($_REQUEST['f_name']) == '')
        {
            // No URL name given, generate from title
            $name = midcom_generate_urlname_from_string($_REQUEST['f_title']);
        }
        else
        {
            $name = midcom_generate_urlname_from_string($_REQUEST['f_name']);
        }
        
        $qb = midcom_db_topic::new_query_builder();
        $qb->add_constraint('up', '=', $this->_topic->id);
        $qb->add_constraint('name', '=', $name);
        
        if ($qb->count() !== 0)
        {
            $this->_processing_msg = sprintf($_MIDCOM->i18n->get_string('a folder with name %s already exists', 'midcom.admin.folder'), $name);
            $_MIDCOM->uimessages->add($_MIDCOM->i18n->get_string('create subfolder', 'midcom.admin.folder'), $this->_processing_msg, 'error');
            
            $this->_request_data['folder_title'] = $_REQUEST['f_title'];
            $this->_request_data['folder_name'] = $name;
            $this->_request_data['error_message'] = $this->_processing_msg;
            
            // Add a UI message
            $_MIDCOM->uimessages->add($_MIDCOM->i18n->get_string('create folder', 'midcom.admin.folder'), $this->_processing_msg, 'error');
            
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add($this->_processing_msg, MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }

        $this->_newtopic = new midcom_db_topic();
        $this->_newtopic->up = $this->_topic->id;
        $this->_newtopic->name = $name;
        $this->_newtopic->extra = $_REQUEST['f_title'];
        $this->_newtopic->component = $_REQUEST['f_type'];
        $this->_newtopic->style = $_REQUEST['f_style'];

        if (! $this->_newtopic->create())
        {
            $this->_processing_msg = 'Could not create Folder: ' . mgd_errstr();
            $_MIDCOM->uimessages->add($_MIDCOM->i18n->get_string('create subfolder', 'midcom.admin.folder'), $this->_processing_msg, 'error');
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add($this->_processing_msg, MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        
        $newid = $this->_newtopic->id;
        
        // TODO: Move to metadata
        $this->_newtopic->set_parameter('midcom.helper.nav', 'navorder', $_REQUEST['f_navorder']);

        // We have to invalidate the current topic (so that it can be reread with the correct
        // childs), *not* the newly created topic (which won't be in any cache anyway, as it
        // has just been created with a new GUID...
        $_MIDCOM->cache->invalidate($this->_topic->guid);

        return true;
    }
    
    /**
     * Shows the folder creation form.
     * 
     * @access private
     */
    function _show_create($handler_id, &$data)
    {
        // TODO: Move to metadata
        $this->_request_data['parent_navorder'] = $this->_topic->get_parameter('midcom.helper.nav', 'navorder');
        
        $data['folder'] =& $this->_topic;

        $this->_request_data['navorder_list'] = array
        (
            MIDCOM_NAVORDER_DEFAULT => $_MIDCOM->i18n->get_string('default sort order', 'midcom.admin.folder'),
            MIDCOM_NAVORDER_TOPICSFIRST => $_MIDCOM->i18n->get_string('folders first', 'midcom.admin.folder'),
            MIDCOM_NAVORDER_ARTICLESFIRST => $_MIDCOM->i18n->get_string('pages first', 'midcom.admin.folder'),
            MIDCOM_NAVORDER_SCORE => $_MIDCOM->i18n->get_string('by score', 'midcom.admin.folder'),
        );
        
        // loads and populats request_array['components']
        $style_default = array
        (
            '' => $_MIDCOM->i18n->get_string('default', 'midcom.admin.folder'),
        );
        
        $styles_all = midcom_admin_folder_folder_management::list_styles();
        
        $this->_request_data['styles'] = array_merge($style_default, $styles_all);
        
        // Show the style element
        midcom_show_style('midcom-admin-show-create-folder');
    }
}
?>