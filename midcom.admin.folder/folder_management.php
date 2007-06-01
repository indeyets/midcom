<?php
/**
 * @package midcom.admin.folder
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id: viewer.php 3975 2006-09-06 17:36:03Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Folder managemement class.
 * 
 * @package midcom.admin.folder
 */
class midcom_admin_folder_folder_management extends midcom_baseclasses_components_handler
{
    /**
     * Anchor prefix stores the link back to the edited content topic
     * 
     * @access private
     * @var string
     */
    var $_anchor_prefix = null;
    
    /**
     * Simple constructor, which only initializes the parent constructor.
     */
    function midcom_admin_folder_folder_management()
    {
        parent::midcom_baseclasses_components_handler();
    }
    
    /**
     * Get the object title of the content topic.
     * 
     * @return string containing the content topic title
     */
    function _get_object_title($object)
    {
        $title = '';
        if (array_key_exists('title', $object))
        {
            $title = $object->title;
        }
        else if (is_a($object, 'midcom_baseclasses_database_topic'))
        {
            $title = $object->extra;
        }
        else if (array_key_exists('name', $object))
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
     * Initializes the context data and toolbar objects
     * 
     * @access private
     */
    function _on_initialize()
    {
        $config = $this->_request_data['plugin_config'];
        if ($config)
        {
            foreach ($config as $key => $value)
            {
                $this->$key = $value;
            }
        }
        $this->_anchor_prefix = $this->_request_data['plugin_anchorprefix'];
        
        // Ensure we get the correct styles
        $_MIDCOM->style->prepend_component_styledir('midcom.admin.folder');
        
        $this->_request_data['folder'] = $this->_topic;
        
        if (!array_key_exists($this->_topic->component, $_MIDCOM->componentloader->manifests))
        {
            $this->_topic->component = 'midcom.core.nullcomponent';
        }
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
        return array
        (
            /**
             * Basic functionalities such as creation, editing and deleting
             * topic objects.
             */
            /**
             * Create a new topic
             * 
             * Match /create/
             */
            'create' => array
            (
                'handler' => array('midcom_admin_folder_handler_create', 'create'),
                'fixed_args' => array ('create'),
            ),
            
            /**
             * Edit a topic
             * 
             * Match /edit/
             */
            'edit' => array
            (
                'handler' => array('midcom_admin_folder_handler_edit', 'edit'),
                'fixed_args' => array ('edit'),
            ),
            
            /**
             * Delete a topic
             * 
             * Match /delete/
             */
            'delete' => array
            (
                'handler' => array('midcom_admin_folder_handler_delete', 'delete'),
                'fixed_args' => array ('delete'),
            ),
            
            /**
             * Approval pseudo locations, which redirect back to the original page
             * after saving the new status.
             */
            /**
             * Approve a topic object
             * 
             * Match /metadata/approve/
             */
            'approve' => array
            (
                'handler' => array('midcom_admin_folder_handler_approvals', 'approval'),
                'fixed_args' => array ('approve'),
            ),
            
            /**
             * Unapprove a topic object
             * 
             * Match /metadata/unapprove/
             */
            'unapprove' => array
            (
                'handler' => array('midcom_admin_folder_handler_approvals', 'approval'),
                'fixed_args' => array ('unapprove'),
            ),
            
            /**
             * Miscellaneous other functionalities
             */
            /**
             * Metadata editing
             * 
             * Match /metadata/<object guid>/
             */
            'metadata' => array
            (
                'handler' => array('midcom_admin_folder_handler_metadata', 'metadata'),
                'fixed_args' => array ('metadata'),
                'variable_args' => 1,
            ),

            /**
             * Object moving
             * 
             * Match /move/<object guid>/
             */
            'move' => array
            (
                'handler' => array('midcom_admin_folder_handler_move', 'move'),
                'fixed_args' => array ('move'),
                'variable_args' => 1,
            ),
            
            // Match /order/
            'order' => array
            (
                'handler' => array('midcom_admin_folder_handler_order', 'order'),
                'fixed_args' => array ('order'),
            ),
        );
    }
    
    /**
     * Static method to list names of the non-purecore components
     * 
     * @access public
     * @param string $parent_component  Name of the parent component, which will pop the item first on the list
     * @return mixed Array containing names of the components
     */
    function get_component_list($parent_component = '')
    {
        $components = array ();
        
        // Loop through the list of components of component loader
        foreach ($_MIDCOM->componentloader->manifests as $manifest)
        {
            // Skip purecode components
            if ($manifest->purecode)
            {
                continue;
            }
            
            // Skip components beginning with midcom or midgard
            if (ereg('^(midcom|midgard)\.', $manifest->name))
            {
                continue;
            }
            
            // Skip components not ported to 2.6
            if (   !is_array($manifest->_raw_data)
                || !array_key_exists('package.xml', $manifest->_raw_data))
            {
                continue;
            }
            
            if (array_key_exists('description', $manifest->_raw_data['package.xml']))
            {
                $description = $_MIDCOM->i18n->get_string($manifest->_raw_data['package.xml']['description'], $manifest->name);
            }
            else
            {
                $description = '';
            }
            
            $components[$manifest->name] = array
            (
                'name' => $manifest->get_name_translated(),
                'description' => $description,
            );
        }
        
        // Sort the components in alphabetical order (by key i.e. component class name)
        asort($components);
        
        // Set the parent component to be the first if applicable
        if (   $parent_component !== ''
            && array_key_exists($parent_component, $components))
        {
            $temp = array();
            $temp[$parent_component] = $components[$parent_component];
            unset($components[$parent_component]);
            
            $components = array_merge($temp, $components);
        }
        
        return $components;
    }
    
    /**
     * Static method for listing available style templates
     * 
     * @access public
     */
    function list_styles($up = 0, $prefix = '/', $spacer = '')
    {
        static $style_array = array();
        
        $qb = midcom_db_style::new_query_builder();
        $qb->add_constraint('up', '=', $up);
        $styles = $qb->execute();
        
        foreach ($styles as $style)
        {
            $style_string = "{$prefix}{$style->name}";
            $style_array[$style_string] = "{$spacer}{$style->name}";
            midcom_admin_folder_folder_management::list_styles($style->id, $style_string . '/', $spacer . '&nbsp;&nbsp;');
        }
        
        if ($prefix === '/')
        {
            return $style_array;
        }
    }
}
?>
