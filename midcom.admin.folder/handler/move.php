<?php
/**
 * @package midcom.admin.folder
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: configuration.php 3757 2006-07-27 14:32:42Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Metadata editor.
 *
 * This handler uses midcom.helper.datamanager2 to edit object move properties
 *
 * @package midcom.admin.folder
 */
class midcom_admin_folder_handler_move extends midcom_baseclasses_components_handler
{
    /**
     * Object requested for move editing
     * 
     * @access private
     * @var $_object mixed Object for move editing
     */
    var $_object = null;
    
    /**
     * Constructor, call for the class parent constructor method.
     * 
     * @access public
     */
    function midcom_admin_folder_handler_move()
    {
        parent::midcom_baseclasses_components_handler();
    }
    
    /**
     * Get the object title of the content topic.
     * 
     * @return string containing the content topic title
     */
    function _get_object_title(&$object)
    {
        $title = '';
        if (   array_key_exists('title', $object)
            && $object->title !== '')
        {
            $title = $object->title;
        }
        else if (is_a($object, 'midcom_baseclasses_database_topic')
            && $object->extra !== '')
        {
            $title = $object->extra;
        }
        else if (array_key_exists('name', $object)
            && $object->name !== '')
        {
            $title = $object->name;
        }
        else
        {
            $title = get_class($object) . " GUID {$object->guid}"; 
        }
        
        return $title;
    }
    
    /**
     * Handler for folder move. Checks for updating permissions, initializes
     * the move and the content topic itself. Handles also the sent form.
     * 
     * @access private
     * @return boolean Indicating success
     */
    function _handler_move($handler_id, $args, &$data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        $this->_object = $_MIDCOM->dbfactory->get_object_by_guid($args[0]);
        if (! $this->_object)
        {
            debug_add("Object with GUID '{$args[0]}' was not found!", MIDCOM_LOG_ERROR);
            debug_pop();
            
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "The GUID '{$args[0]}' was not found.");
            // This will exit.
        }
        
        if (   !is_a($this->_object, 'midcom_baseclasses_database_topic')
            && !is_a($this->_object, 'midcom_baseclasses_database_article'))
        {
            return false;
        }
        
        $this->_object->require_do('midgard:update');
        
        if (isset($_POST['move_to']))
        {
            $move_to_topic = new midcom_db_topic($_POST['move_to']);
            if (!$move_to_topic)
            {
                return false;
            }
            
            $move_to_topic->require_do('midgard:create');
            
            if (is_a($this->_object, 'midcom_baseclasses_database_topic'))
            {
                $this->_object->up = $move_to_topic->id;
                $this->_object->update();
            }
            else
            {
                $this->_object->topic = $move_to_topic->id;
                $this->_object->update();
            }
            $_MIDCOM->relocate($_MIDCOM->permalinks->create_permalink($this->_object->guid));
            // This will exit
        }
        
        if (is_a($this->_object, 'midcom_baseclasses_database_topic'))
        {
            // This is a topic
            $this->_topic->require_do('midcom.admin.folder:topic_management');
            $this->_node_toolbar->hide_item("__ais/folder/move/{$this->_object->guid}.html"); 
            $data['current_folder'] = new midcom_db_topic($this->_object->up);
        }
        else
        {
            // This is a regular object, bind to view
            $_MIDCOM->bind_view_to_object($this->_object);
            
            $tmp[] = array
            (
                MIDCOM_NAV_URL => $_MIDCOM->permalinks->create_permalink($this->_object->guid),
                MIDCOM_NAV_NAME => $this->_get_object_title($this->_object),
            );
            $this->_view_toolbar->hide_item("__ais/folder/move/{$this->_object->guid}.html");

            $data['current_folder'] = new midcom_db_topic($this->_object->topic);
        }
        
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "__ais/folder/move/{$this->_object->guid}.html",
            MIDCOM_NAV_NAME => $_MIDCOM->i18n->get_string('move', 'midcom.admin.folder'),
        );
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);

        $data['title'] = sprintf($_MIDCOM->i18n->get_string('move %s', 'midcom.admin.folder'), $this->_get_object_title($this->_object));
        $_MIDCOM->set_pagetitle($data['title']);
        
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
     * Output the style element for move editing
     * 
     * @access private
     */
    function _show_move($handler_id, &$data)
    {
        // Bind object details to the request data
        $data['object'] =& $this->_object;
        
        midcom_show_style('midcom-admin-show-folder-move');
    }
    
}
?>