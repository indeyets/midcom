<?php
/**
 * @package midcom.admin.folder
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: configuration.php 3757 2006-07-27 14:32:42Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Handle the folder editing requests
 *
 * @package midcom.admin.folder
 */
class midcom_admin_folder_handler_edit extends midcom_baseclasses_components_handler
{
    /**
     * Constructor method
     * 
     * @access public
     */
    function midcom_admin_folder_handler_edit ()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * Handler for folder editing. Checks for the permissions and folder integrity.
     * 
     * @access private
     * @return boolean Indicating success
     */
    function _handler_edit($handler_id, $args, &$data)
    {
        $this->_topic->require_do('midgard:update');
        $this->_topic->require_do('midcom.admin.folder:topic_management');
        
        if (array_key_exists('f_cancel', $_REQUEST))
        {
            $_MIDCOM->relocate('');
            // This will exit.
        }

        if (array_key_exists('f_submit', $_REQUEST))
        {
            if ($this->_process_edit_form())
            {
                // Relocate to the renamed topic
                $_MIDCOM->relocate($_MIDCOM->permalinks->create_permalink($this->_topic->guid));
                // This will exit.
            }
        }
        
        // Add the view to breadcrumb trail
        $tmp = array();
        $tmp[] = array
        (
            MIDCOM_NAV_URL => '__ais/folder/edit.html',
            MIDCOM_NAV_NAME => $_MIDCOM->i18n->get_string('edit folder', 'midcom.admin.folder'),
        );
        
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
        
        // Hide the button in toolbar
        $this->_node_toolbar->hide_item('__ais/folder/edit.html');

        // Set page title
        $data['topic'] = $this->_topic;
        $data['title'] = sprintf($_MIDCOM->i18n->get_string('edit folder %s', 'midcom.admin.folder'), $data['topic']->extra);
        $_MIDCOM->set_pagetitle($data['title']);

        // Set the help object in the toolbar
        $this->_view_toolbar->add_help_item('edit_folder', 'midcom.admin.folder');
        
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
     * Create a new style for the topic
     * 
     * @access private
     * @param string $name Name of the style
     * @return string Style path
     */
    function _create_style($style_name)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        if (isset($GLOBALS['midcom_style_inherited']))
        {
            $up = $_MIDCOM->style->get_style_id_from_path($GLOBALS['midcom_style_inherited']);
            debug_add("Style inherited from {$GLOBALS['midcom_style_inherited']}");
        }
        else
        {
            $up = $_MIDGARD['style'];
            debug_add("No inherited style found, placing the new style under host style (ID: {$_MIDGARD['style']}");
        }
        
        $style = new midcom_db_style();
        $style->name = $style_name;
        $style->up = $up;
        
        if (!$style->create())
        {
            debug_print_r('Failed to create a new style due to ' . mgd_errstr(), $style, MIDCOM_LOG_WARN);
            debug_pop();
            
            $_MIDCOM->uimessages->add('edit folder', sprintf($_MIDCOM->i18n->get_string('failed to create a new style template: %s', 'midcom.admin.folder'), mgd_errstr()), 'error');
            return '';
        }
        
        debug_print_r('New style created', $style);
        debug_pop();
        
        return $_MIDCOM->style->get_style_path_from_id($style->id);
    }
    
    /**
     * Processes the _Edit folder_ page form and updates the folder.
     * 
     * @access private
     * @return boolean Indicating success
     */
    function _process_edit_form()
    {
        if (trim($_REQUEST['f_name']) == '')
        {
            $this->_processing_msg = $_MIDCOM->i18n->get_string('no url name specified', 'midcom.admin.folder');
            $_MIDCOM->uimessages->add($_MIDCOM->i18n->get_string('create folder', 'midcom.admin.folder'), $this->_processing_msg, 'error');
            
            return false;
        }
        if (trim($_REQUEST['f_title']) == '')
        {
            $this->_processing_msg = $_MIDCOM->i18n->get_string('title is empty', 'midcom.admin.folder');
            $_MIDCOM->uimessages->add($_MIDCOM->i18n->get_string('create folder', 'midcom.admin.folder'), $this->_processing_msg, 'error');
            return false;
        }
        
        if (mgd_get_topic_by_name($this->_topic->id, $_REQUEST['f_name']))
        {
            $this->_processing_msg = sprintf($_MIDCOM->i18n->get_string('folder with name %s already exists', 'midcom.admin.folder'), $_REQUEST['f_name']);
            $_MIDCOM->uimessages->add($_MIDCOM->i18n->get_string('create folder', 'midcom.admin.folder'), $this->_processing_msg, 'error');
            return false;
        }

        // store form data in topic object
        $this->_topic->name = midcom_generate_urlname_from_string($_REQUEST['f_name']);
        $this->_topic->extra = $_REQUEST['f_title'];
        //$this->_topic->score = $_REQUEST['f_score'];
        //$this->_topic->owner = $_REQUEST['f_owner'];
        
        if ($_REQUEST['f_style'] === '__create')
        {
            $this->_topic->require_do('midcom.admin.folder:template_management');

            $this->_topic->style = $this->_create_style($this->_topic->name);
            
            // Failed to create the new style template
            if ($this->_topic->style === '')
            {
                return false;
            }
        }
        else
        {
            $this->_topic->style = $_REQUEST['f_style'];
        }
        
        // TODO: Move to metadata
        $this->_topic->parameter('midcom.helper.nav', 'navorder', $_REQUEST['f_navorder']);
        
        if (   array_key_exists('f_style_inherit', $_REQUEST)
            && $_REQUEST['f_style_inherit'] == 'on')
        {
            $this->_topic->styleInherit = true;
        }
        else
        {
            $this->_topic->styleInherit = false;
        }

        if (   trim($_REQUEST['f_type']) !== $this->_topic->component
            && $_MIDCOM->auth->admin
            && $_REQUEST['f_type'] !== '')
        {
             $this->_topic->component = $_REQUEST['f_type'];
        }


        if (! $this->_topic->update())
        {
            $this->_processing_msg = 'Could not save Folder: ' . mgd_errstr();
            return false;
        }

        $_MIDCOM->cache->invalidate($this->_topic->guid());

        return true;
    }
    
    /**
     * Shows the _Edit folder_ page.
     * 
     * @access private
     */
    function _show_edit($handler_id, &$data)
    {
        // Get parent component and navorder
        $data['parent_topic'] = $this->_topic->component;
               
        $data['view'] =& $this->_topic;
        $data['style_inherit'] = $this->_topic->styleInherit;
        $data['style'] = $this->_topic->style;
        $data['folder'] =& $this->_topic;
        
        // TODO: Move to metadata
        $data['navorder'] = $this->_topic->parameter('midcom.helper.nav', 'navorder');
        
        $data['navorder_list'] = array
        (
            MIDCOM_NAVORDER_DEFAULT => $_MIDCOM->i18n->get_string('default sort order', 'midcom.admin.folder'),
            MIDCOM_NAVORDER_TOPICSFIRST => $_MIDCOM->i18n->get_string('folders first', 'midcom.admin.folder'),
            MIDCOM_NAVORDER_ARTICLESFIRST => $_MIDCOM->i18n->get_string('pages first', 'midcom.admin.folder'),
            MIDCOM_NAVORDER_SCORE => $_MIDCOM->i18n->get_string('by score', 'midcom.admin.folder'),
        );
        
        $style_default = array
        (
            '' => $_MIDCOM->i18n->get_string('default', 'midcom.admin.folder'),
        );
        
        if ($this->_topic->can_do('midcom.admin.folder:template_management'))
        {
            $style_default['__create'] = $_MIDCOM->i18n->get_string('new layout template', 'midcom.admin.folder');
        }
        
        $styles_all = midcom_admin_folder_folder_management::list_styles();
        
        $data['styles'] = array_merge($style_default, $styles_all);
        
        // Place $view as a super global for the style checker function
        // midcom_admin_content_list_styles_selector2
        $GLOBALS['view'] =& $this->_topic;
        
        // Show the style element
        midcom_show_style('midcom-admin-show-edit-folder');
    }
}
?>