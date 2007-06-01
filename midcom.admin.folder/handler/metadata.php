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
 * This handler uses midcom.helper.datamanager2 to edit object metadata properties
 *
 * @package midcom.admin.folder
 */
class midcom_admin_folder_handler_metadata extends midcom_baseclasses_components_handler
{
    /**
     * Object requested for metadata editing
     * 
     * @access private
     * @var $_object mixed Object for metadata editing
     */
    var $_object = null;
    
    /**
     * Edit controller instance for Datamanager 2
     * 
     * @access private
     * @var $_controller midcom_helper_datamanager2_controller
     */
    var $_controller = null;
    
    /**
     * Datamanager 2 schema instance
     * 
     * @access private
     * @var $_schemadb midcom_helper_datamanager2_schema
     */
    var $_schemadb = null;
    
    /**
     * Constructor, call for the class parent constructor method.
     * 
     * @access public
     */
    function midcom_admin_folder_handler_metadata()
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
     * Load the DM2 edit controller instance
     * 
     * @access private
     * @return bool Indicating success of DM2 edit controller instance
     */
    function _load_datamanager()
    {
        $this->_schemadb = midcom_helper_datamanager2_schema::load_database($GLOBALS['midcom_config']['metadata_schema']);
        
        $this->_controller =& midcom_helper_datamanager2_controller::create('simple');
        $this->_controller->schemadb =& $this->_schemadb;
        
        $this->_controller->set_storage($this->_object, 'metadata');
        
        
        if (! $this->_controller->initialize())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to initialize a DM2 controller instance for article {$this->_article->id}.");
            // This will exit.
        }
    }
    
    /**
     * Handler for folder metadata. Checks for updating permissions, initializes
     * the metadata and the content topic itself. Handles also the sent form.
     * 
     * @access private
     * @return boolean Indicating success
     */
    function _handler_metadata($handler_id, $args, &$data)
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
        
        // FIXME: We should modify the schema according to whether or not scheduling is used
        $this->_object->require_do('midgard:update');
        
        if (is_a($this->_object, 'midcom_baseclasses_database_topic'))
        {
            // This is a topic
            $this->_topic->require_do('midcom.admin.folder:topic_management');
        }
        else
        {
            // This is a regular object, bind to view
            $_MIDCOM->bind_view_to_object($this->_object);
        }

        $this->_metadata =& midcom_helper_metadata::retrieve($this->_object);
        
        if (! $this->_metadata)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                "Failed to retrieve Metadata for '{$this->_object->__table__}' ID {$this->_object->id}.");
            // This will exit.
        }
        
        // Load the DM2 controller instance
        $this->_load_datamanager();
        
        switch ($this->_controller->process_form())
        {
            case 'save':
            case 'cancel':
                $_MIDCOM->relocate($_MIDCOM->permalinks->create_permalink($this->_object->guid));
                // This will exit
        }
        
        $tmp = array ();
        
        if (is_a($this->_object, 'midcom_baseclasses_database_topic'))
        {       
            $this->_node_toolbar->hide_item("__ais/folder/metadata/{$this->_object->guid}.html"); 
        }
        else
        {
            $tmp[] = array
            (
                MIDCOM_NAV_URL => $_MIDCOM->permalinks->create_permalink($this->_object->guid),
                MIDCOM_NAV_NAME => $this->_get_object_title($this->_object),
            );
            $this->_view_toolbar->hide_item("__ais/folder/metadata/{$this->_object->guid}.html");
        }
        
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "__ais/folder/metadata/{$this->_object->guid}.html",
            MIDCOM_NAV_NAME => $_MIDCOM->i18n->get_string('edit metadata', 'midcom.admin.folder'),
        );
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);

        $data['title'] = sprintf($_MIDCOM->i18n->get_string('edit metadata of %s', 'midcom.admin.folder'), $this->_get_object_title($this->_object));
        $_MIDCOM->set_pagetitle($data['title']);
        
        // Set the help object in the toolbar
        $this->_view_toolbar->add_help_item('edit_metadata', 'midcom.admin.folder');
        
        // Ensure we get the correct styles
        $_MIDCOM->style->prepend_component_styledir('midcom.admin.folder');
        
        return true;
    }
    
    /**
     * Output the style element for metadata editing
     * 
     * @access private
     */
    function _show_metadata($handler_id, &$data)
    {
        // Bind object details to the request data
        $data['controller'] =& $this->_controller;
        $data['object'] =& $this->_object;
        
        midcom_show_style('midcom-admin-show-folder-metadata');
    }
    
}
?>