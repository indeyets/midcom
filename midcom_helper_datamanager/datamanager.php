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
 *  extends midcom_core_component_baseclass
 */
class midcom_helper_datamanager_datamanager
{
    private $schemadb = null;
    public $schema = null;
    public $schema_name = null;
    protected $form = null;

    /**
     * This is the storage implementation which is used for operation on the types. It encapsulates
     * the storage target.
     *
     * @var midcom_helper_datamanager_storage
     */
    public $storage = null;
    
    public $types = null;

    public function __construct(&$schemadb)
    {
        if (! $schemadb instanceof midcom_helper_datamanager_schema)
        {
            try
            {
                $this->schemadb = midcom_helper_datamanager_schema::load_database($schemadb);
            }
            catch (Exception $e)
            {
                throw new midcom_helper_datamanager_exception_schema("given schema {$schemadb} is not instance of datamanager schema or a valid schema path");
            }
            
            return;
        }

        $this->schemadb =& $schemadb;

    }

    public function &get_form($form_class = 'simple')
    {
        if (strpos($form_class, '_') === false)
        {
            $form_class = "midcom_helper_datamanager_form_{$form_class}";
        }
        $this->form = new $form_class($this->schema, $this->types, $this->storage, $this);
        return $this->form;
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
        
        if (! $this->schema instanceof midcom_helper_datamanager_schema)
        {
            throw new midcom_helper_datamanager_exception_schema('given schema is not instance of datamanager schema');
        }

        $this->load_types();
        if ($this->form instanceof midcom_helper_datamanager_form)
        {
            $this->form->load_widgets();
        }

        return true;
    }

    /**
     * Clears possible dangling references and instance new datatype proxy object
     */
    private function load_types()
    {
        unset($this->types);
        if (! $this->storage instanceof midcom_helper_datamanager_storage)
        {
            $this->storage = new midcom_helper_datamanager_storage_null($this->schema);
        }

        $this->types = new midcom_helper_datamanager_typeproxy($this->schema, $this->storage);
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

        if (! $object instanceof midcom_helper_datamanager_storage)
        {
            $this->storage = new midcom_helper_datamanager_storage_midgard($this->schema, $object);
        }
        else
        {
            $this->storage =& $object;
        }

        $this->load_types();
        if ($this->form instanceof midcom_helper_datamanager_form)
        {
            $this->form->load_widgets();
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
     * This function is a shortcut that combines set_schema and set_storage together.
     * The schema name is looked up in the parameter 'midcom_helper_datamanager/schema_name',
     * if it is not found, the first schema from the schema database is used implicitly.
     *
     * @see set_schema()
     * @see set_storage()
     * @param mixed &$object A reference to either a MidCOM DBA class or a subclass of
     *     midcom_helper_datamanager2_storage.
     * @param boolean $strict Whether we should strictly use only the schema given by object params
     * @return boolean Indicating success.
     */
    public function autoset_storage(&$object, $strict = false)
    {
        if ($object instanceof midcom_helper_datamanager2_storage)
        {
            $schema = $object->object->get_parameter('midcom_helper_datamanager', 'schema_name');
        }
        else
        {
         //   $schema = $object->get_parameter('midcom_helper_datamanager', 'schema_name');
        }

        if (@! $schema)
        {
            $schema = null;
        }

        if (!$this->set_schema($schema))
        {
            if (   $strict
                || $schema == null)
            {
                return false;
            }
            else
            {
                // debug_push_class(__CLASS__, __FUNCTION__);
                // debug_add("Given schema name {$schema} was not found, reverting to default.", MIDCOM_LOG_INFO);
                // debug_pop();
                // Schema database has probably changed so we should be graceful here
                if (!$this->set_schema(null))
                {
                    return false;
                }
            }

        }
        return $this->set_storage($object);
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
    public function validate()
    {
        $this->validation_errors = $this->types->validate();
        return empty($this->validation_errors);
    }

    /** 
     * Note to self: we probably need to clear lots of things
     *
     public function __destructor()
     {
     }
     */

}

?>