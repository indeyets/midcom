<?php
/**
 * @package midcom_helper_datamanager
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Datamanager core class.
 *
 * This class controls all type I/O operations, including entering and exiting
 * editing operations and creation support. It brings Types, Schemas and Storage objects
 * together.
 *
 * @package midcom_helper_datamanager
 */
class midcom_helper_datamanager_datamanager extends midcom_core_component_baseclass
{
    
    private var $schemadb = null;
    protected var $schema = null;
    protected var $schema_name = null;

    /**
     * This is the storage implementation which is used for operation on the types. It encapsulates
     * the storage target.
     *
     * @var midcom_helper_datamanager_storage
     */
    public var $storage = null;
    
    protected var $types = array();
    protected var $widgets = array();
    
    public function initialize(&$schemadb)
    {
        $this->schemadb =& $schemadb;
    }

    /**
     * This function activates the given schema. This will drop all existing types
     * and create a new set of them which are in the default state at this point.
     *
     * This will reset the existing schema and type listing. If a storage object
     * exists, the change of the schema will be propagated implicitly, as it will
     * reference the schema member of ours.
     *
     * @param string $name The name of the schema to use, omit this to use the default
     *     schema.
     * @return boolean Indicating success.
     */
    public function set_schema($name = null)
    {
        if (! is_array($this->schemadb))
        {
            return false;
        }
        
        if (   $name !== null
            && !array_key_exists($name, $this->schemadb))
        {
            return false;
        }

        if ($name === null)
        {
            reset($this->schemadb);
            $name = key($this->schemadb);
        }

        $this->schema =& $this->schemadb[$name];
        $this->schema_name = $name;
        
        if (! is_a('midcom_helper_datamanager_schema', $schema))
        {
            throw new Exception('given schema is not instance of datamanager schema');
        }
        
        $this->load_widgets();
        $this->load_types();

        return true;
    }
    
    private function load_widgets()
    {
        $this->widgets = array();

        foreach ($this->schema->fields as $name => $config)
        {
            if (! $this->load_widget($name, $config))
            {
                return false;
            }
        }
    }
    
    public function load_widget($name, $config = null)
    {
        if ($config == null)
        {
            $config = $this->schema->fields[$name];            
        }
        
        //TODO: Add fallback to types default widget
        if (! isset($config['widget']) )
        {
            throw new Exception("The field {$name} is missing widget");
        }
        
        $widget_class = $config['widget'];
        
        if (strpos($widget_class, '_') === false)
        {
            $widget_class = "midcom_helper_datamanager_widget_{$widget_class}";
        }

        $this->widgets[$name] = new $widget_class();
        if (! $this->widgets[$name]->initialize($name, $config['widget_config'], $this->schema, $this->types[$name], $this->namespace))
        {
            return false;
        }
        
        return true;
    }
    
    private function load_types()
    {
        $this->types = array();

        foreach ($this->schema->fields as $name => $config)
        {
            if (! $this->load_type($name, $config))
            {
                return false;
            }
        }
    }
    
    public function load_type($name, $config = null)
    {
        if ($config == null)
        {
            $config = $this->schema->fields[$name];            
        }

        if (! isset($config['type']) )
        {
            throw new Exception("The field {$name} is missing type");
        }

        $type_class = $config['type'];
        
        if (strpos($type_class, '_') === false)
        {
            $type_class = "midcom_helper_datamanager_type_{$type_class}";
        }

        $this->types[$name] = new $type_class();
        if (! $this->types[$name]->initialize($name, $config['type_config'], $this->storage))
        {
            return false;
        }
        
        return true;        
    }

    /**
     * This function sets the system to use a specific storage object. You can pass
     * either a MidCOM DBA object or a fully initialized storage subclass. The former
     * is automatically wrapped in a midcom storage object. If you pass your own
     * storage object, ensure that it uses the same schema as this class. Ideally,
     * you should use references for this.
     *
     * This call will fail if there is no schema set. All types will be set and
     * initialized to the new storage object. Thus, it is possible to call set_storage
     * repeatedly thus switching an existing DM instance over to a new storage object
     * as long as you work with the same schema.
     *
     * @param mixed &$object A reference to either a MidCOM DBA class or a subclass of
     * midcom_helper_datamanager_storage.
     *
     * @return boolean Indicating success.
     */
    public function set_storage(&$object)
    {
        if ($this->schema === null)
        {
            return false;
        }

        if (! is_a($object, 'midcom_helper_datamanager_storage'))
        {
            $this->storage = new midcom_helper_datamanager_storage_midgard($this->schema, $object);
        }
        else
        {
            $this->storage =& $object;
        }

        // For reasons I do not completely comprehend, PHP drops the storage references into the types
        // in the lines above. Right now the only solution (except debugging this 5 hours long line
        // by line) I see is explicitly setting the storage references in the types.

        // TODO: Need to investigate how this should be done
        // and implement the storage backend :)
        
        // foreach ($this->types as $type => $copy)
        // {
        //     $this->types[$type]->set_storage($this->storage);
        // }
        // 
        // $this->storage->load($this->types);

        return true;
    }
    
    /**
     * This function will save the current state of all types to disk. A full
     * validation cycle is done beforehand, if any validation fails, the function
     * aborts and sets the $validation_errors member variable accordingly.
     *
     * @return boolean Indicating success
     */
    public function save()
    {
        if (! $this->validate())
        {
            return false;
        }

        return $this->storage->store($this->types);
    }

    /**
     * Validate the current object state. It will populate $validation_errors
     * accordingly.
     *
     * @return boolean Indicating validation success.
     */
    function validate()
    {
        $this->validation_errors = array();
        $validated = true;
        // foreach ($this->schema->fields as $name => $config)
        // {
        //     if (! $this->types[$name]->validate())
        //     {
        //         $this->validation_errors[$name] = $this->types[$name]->validation_error;
        //         $validated = false;
        //     }
        // }
        
        return $validated;
    }
    
}

?>