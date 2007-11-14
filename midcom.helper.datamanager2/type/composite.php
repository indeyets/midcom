<?php
/**
 * @package midcom.helper.datamanager2
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Datamanager 2 composite object management type.
 *
 * This type allows you to control an arbitrary number of "child objects" on a given object.
 * It can only operate if the storage implementation provides it with a Midgard Object.
 * The storage location provided by the schema is unused at this time, as child object
 * operations cannot be undone.
 *
 * The type can manage an arbitrary number of objects. Each objects is identified
 * by a GUID. It provides management functions for existing child objects which allow you to 
 * add, delete and update them in all variants. These functions are executed immediately on the 
 * storage object, no undo is possible.
 *
 * <b>Available configuration options:</b>
 *
 * - <b>child_class</b>: the MidCOM DBA class of the child object
 * - <b>child_schemadb</b>: Path to DM2 schema database used for child elements
 * - <b>child_foreign_key_fieldname</b>: the field of the child objects used to connect them to the parent. By default <i>up</i>.
 * - <b>parent_key_fieldname</b>: field of the parent used as identifier in child objects. Typically <i>id</i> or <i>guid</i>.
 * - Array <b>child_constraints</b>: Other query constraints for the child objects as arrays containing field, constraint type and value suitable for QB add_constraint usage. 
 * - <b>style_element_name</b>: Name used for the header, footer and item elements of the object list
 * - <b>window_mode</b>: Whether the composites should be edited in a modal pop-up window instead of in-place. Useful for tight spaces.
 * - <b>maximum_items</b>: How many items are allowed into the composite. After this creation is disabled.
 * - <b>enable_creation</b>: Whether creation of new items is allowed
 * - <b>area_element</b>: The HTML element surrounding the composites. By default div.
 *
 * @package midcom.helper.datamanager2
 */
class midcom_helper_datamanager2_type_composite extends midcom_helper_datamanager2_type
{
    var $child_class = '';
    var $child_schemadb = '';
    var $child_foreign_key_fieldname = 'up';
    var $parent_key_fieldname = 'id';
    var $child_constraints = Array();
    var $orders = Array(
        'created' => 'ASC',
    );
    var $style_element_name = 'child';
    var $window_mode = false;
    var $wide_mode = false;
    var $maximum_items = null;
    var $enable_creation = true;
    var $area_element = 'div';
    var $defaults = array();

    /**
     * The schema database in use for the child elements
     *
     * @var Array
     * @access private
     */
    var $_schemadb = null;
    
    /**
     * Array of Datamanager 2 controllers for child object display and management
     *
     * @var array
     * @access private
     */
    var $_controllers = Array();     
    
    /**
     * Array of Datamanager 2 controllers for child object creation
     *
     * @var array
     * @access private
     */
    var $_creation_controllers = Array();          
    
    /**
     * All objects covered by this field. The array contains Midgard objects indexed by 
     * their identifier within the field.
     *
     * @var Array
     * @access public
     */
    var $objects = Array();

    /**
     * Initialize the class, if neccessary, create a callback instance, otherwise
     * validate that an option array is present.
     */
    function _on_initialize()
    {   
        if (!$this->child_class)
        {
            // TODO: We could have some smart defaults here
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'The configuration option child class must be defined for all composite types.');
            // This will exit.
        }

        if (!$this->child_schemadb)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'The configuration option child schema database must be defined for all composite types.');
            // This will exit.
        }
        
        if (! class_exists($this->child_class))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                "The mapping class {$this->child_class} does not exist.");
            // This will exit.
        }

        $this->_schemadb = midcom_helper_datamanager2_schema::load_database($this->child_schemadb);

        parent::_on_initialize();

        return true;
    }

    /**
     * This function loads child objects of the storage object. It
     * will leave the field empty in case the storage object is null.
     */
    function convert_from_storage ($source)
    {    
        $this->objects = Array();

        if ($this->storage->object === null)
        {
            // We don't have a storage object, skip the rest of the operations.
            return;
        }

        $qb = $_MIDCOM->dbfactory->new_query_builder($this->child_class);
        $parent_key = $this->parent_key_fieldname;
        $qb->add_constraint($this->child_foreign_key_fieldname, '=', $this->storage->object->$parent_key);
        
        foreach ($this->child_constraints as $constraint)
        {
            $qb->add_constraint($constraint[0], $constraint[1], $constraint[2]);
        }
        
        // Order according to configuration
        foreach ($this->orders as $field => $order)
        {
            $qb->add_order($field, $order);
        }
        
        $raw_objects = $qb->execute();
        foreach ($raw_objects as $object)
        {
            $this->objects[$object->guid] = $object;
        }
        
        // Load a creation controller per each schema in the database
        $this->_load_creation_controllers();        
    }

    function convert_to_storage()
    {
        return '';
    }

    /**
     * DM2 creation callback for creating children.
     */
    function & create_object(&$controller)
    {
        $child_class = $this->child_class;
        $foreign_key = $this->child_foreign_key_fieldname;
        $parent_key = $this->parent_key_fieldname;
        
        $object = new $child_class();
        $object->$foreign_key = $this->storage->object->$parent_key;

        foreach ($this->child_constraints as $constraint)
        {
            // Handle the "=" constraints
            if ($constraint[1] == '=')
            {
                $object->$constraint[0] = $constraint[2];
            }
        }

        if (! $object->create())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_print_r('We operated on this object:', $object);
            debug_pop();
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'Failed to create a new child object. Last Midgard error was: '. mgd_errstr());
            // This will exit.
        }
        
        // Notify parent of changes
        $this->storage->object->update();

        return $object;
    }

    /**
     * Deletes an existing child object.
     *
     * @param string $identifier The identifier of the object that should be deleted.
     * @return bool Indicating success.
     */
    function delete_object($identifier)
    {
        if (! array_key_exists($identifier, $this->objects))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Failed to delete the object: The identifier is unknown.", MIDCOM_LOG_INFO);
            debug_pop();
            return false;
        }

        $object = $this->objects[$identifier];
        if (! $object>delete())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Failed to delete the object: DBA delete call returned false.", MIDCOM_LOG_INFO);
            debug_pop();
            return false;
        }
        
        // Notify parent of changes
        $this->storage->object->update();

        unset($this->objects[$identifier]);
        return true;
    }

    /**
     * This call will unconditionally delete all objects currently contained by the type.
     *
     * @return bool Indicating success.
     */
    function delete_all_objects()
    {
        foreach ($this->objects as $identifier => $object)
        {
            if (! $this->delete_object($identifier))
            {
                return false;
            }
        }
        return true;
    }

    /**
     * Adds an item for an existing object
     */
    function add_object_item($identifier)
    {
        $object = $this->objects[$identifier];
        $this->_controllers[$identifier] =& midcom_helper_datamanager2_controller::create('ajax');
        
        if ($this->window_mode)
        {
            $this->_controllers[$identifier]->window_mode = $this->window_mode;
        }
        if ($this->wide_mode)
        {
            $this->_controllers[$identifier]->wide_mode = $this->wide_mode;
        }
        
        $this->_controllers[$identifier]->schemadb =& $this->_schemadb;
        $this->_controllers[$identifier]->set_storage($object);
        switch ($this->_controllers[$identifier]->process_ajax(false))
        {
            case 'view':
                break;
                
            case 'ajax_saved':
                // Notify parent of changes
                $this->storage->object->update();
            default:
                $_MIDCOM->finish();
                exit();
        }
    }
    
    function _load_creation_controllers()
    {   
        if (!$this->enable_creation)
        {
            return false;
        }
        
        if (   !is_null($this->maximum_items)
            && count($this->objects) >= $this->maximum_items)
        {
            return false;
        }
            
        if ($this->storage->object->can_do('midgard:create'))
        {
            foreach (array_keys($this->_schemadb) as $name)
            {
                $this->_creation_controllers[$name] =& midcom_helper_datamanager2_controller::create('create');
                $this->_creation_controllers[$name]->form_identifier = "midcom_helper_datamanager2_controller_create_{$this->name}_{$this->storage->object->guid}_{$name}";
                $this->_creation_controllers[$name]->ajax_mode = true;
                $this->_creation_controllers[$name]->ajax_options = Array();                
                if ($this->window_mode)
                {
                    $this->_creation_controllers[$name]->ajax_options['window_mode'] = $this->window_mode;
                }
                if ($this->wide_mode)
                {
                    $this->_creation_controllers[$name]->ajax_options['wide_mode'] = $this->wide_mode;
                }
                
                $this->_creation_controllers[$name]->schemadb =& $this->_schemadb;
                $this->_creation_controllers[$name]->schemaname = $name;
                $this->_creation_controllers[$name]->callback_object =& $this;
                $this->_creation_controllers[$name]->callback_method = 'create_object';
                $this->_creation_controllers[$name]->defaults = $this->defaults;
                if (! $this->_creation_controllers[$name]->initialize())
                {
                    $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to initialize a DM2 create controller.");
                    // This will exit.
                }
            }
        }      
    }

    function add_creation_data()
    {
        if (!$this->enable_creation)
        {
            return false;
        }
            
        if (   !is_null($this->maximum_items)
            && count($this->objects) >= $this->maximum_items)
        {
            return false;
        }
            
        if ($this->storage->object->can_do('midgard:create'))
        {
            foreach (array_keys($this->_schemadb) as $name)
            {
                // Add default values to fields
                $item_html = Array();
                $form_identifier = $this->_creation_controllers[$name]->form_identifier;
                foreach ($this->_schemadb[$name]->fields as $fieldname => $definition)
                {
                    $item_html[$fieldname] = "<span class=\"{$form_identifier}\" id=\"{$form_identifier}_{$fieldname}\">&lt;{$fieldname}&gt;</span>";
                }
                $request_data = Array(
                    'item_html'  => $item_html,
                    'item'       => null,
                    'item_count' => null,
                    'item_total' => null,
                );
                $_MIDCOM->set_custom_context_data('midcom_helper_datamanager2_widget_composite', $request_data);
                echo "<{$this->area_element} id=\"{$form_identifier}_area\" style=\"display: none;\">\n";
                midcom_show_style("_dm2_composite_{$this->style_element_name}_item");            
                echo "</{$this->area_element}>\n";
            }
            foreach (array_keys($this->_schemadb) as $name)
            {            
                $form_identifier = $this->_creation_controllers[$name]->form_identifier;            
                echo "<button value=\"name\" id=\"{$form_identifier}_button\" class=\"midcom_helper_datamanager2_composite_create_button\">\n";
                echo sprintf($this->_l10n_midcom->get('create %s'), $this->_schemadb[$name]->_l10n_schema->get($this->_schemadb[$name]->description));
                echo "</button>\n";
            }
        }
    }

    function convert_from_csv ($source)
    {
        // TODO: Not yet supported
        return '';
    }

    function convert_to_csv()
    {
        // TODO: Not yet supported
        return '';
    }


    /**
     * Displays the child objects
     */
    function convert_to_html()
    {
        ob_start();
        
        $item_total = count($this->objects);
        $request_data = Array(
            'item_total' => $item_total,
        );
        
        $_MIDCOM->set_custom_context_data('midcom_helper_datamanager2_widget_composite', $request_data);
        midcom_show_style("_dm2_composite_{$this->style_element_name}_header");
       
        $item_count = 0;
        foreach ($this->objects as $identifier => $object)
        {
            $item_count++;
            if (!array_key_exists($identifier, $this->_controllers))
            {
                $this->add_object_item($identifier);
            }
            $request_data['item_html'] = $this->_controllers[$identifier]->get_content_html();
            $request_data['item'] = $object;
            $request_data['item_count'] = $item_count;

            $_MIDCOM->set_custom_context_data('midcom_helper_datamanager2_widget_composite', $request_data);
            echo "<{$this->area_element} id=\"{$this->_controllers[$identifier]->form_identifier}_area\">\n";
            midcom_show_style("_dm2_composite_{$this->style_element_name}_item");
            echo "</{$this->area_element}>\n";
        } 

        $this->add_creation_data();
       
        midcom_show_style("_dm2_composite_{$this->style_element_name}_footer");
        
        $results = ob_get_contents();
        ob_end_clean();
        return $results;
    }

}

?>