<?php
/**
 * @package midcom_helper_datamanager
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Datamanager type proxy, loaded as $dm->types
 *
 * @package midcom_helper_datamanager
 */
class midcom_helper_datamanager_typeproxy
{
    private $schema = false;
    private $storage = false;
    private $types = array();

    public function __construct(&$schema, &$storage)
    {
        if (! is_a($schema, 'midcom_helper_datamanager_schema'))
        {
            throw new midcom_helper_datamanager_exception_type('given schema is not instance of midcom_helper_datamanager_schema');
        }
        $this->schema = $schema;
        if (! is_a($storage, 'midcom_helper_datamanager_storage'))
        {
            throw new midcom_helper_datamanager_exception_type('given storage is not instance of midcom_helper_datamanager_storage');
        }
        $this->storage = $storage;
    }

    public function __get($name)
    {
        $this->prepare_type($name);
        // PONDER: how does this work when the original call is something like $dm->types->fieldname->value
        return $this->types[$name];
    }

    public function __set($name, $value)
    {
        $this->prepare_type($name);
        // PONDER: how does this work when the original call is something like $dm->types->fieldname->value = x
        $this->types[$name] = $value;
    }

    /**
     * Checks if we have the field corresponding to the property name in schema
     */
    public function __isset($name)
    {
        return $this->schema->field_exists($name);
    }

    public function __unset($name)
    {
        // PONDER: Do we need to clear other references, if so do it here
        unset($this->types[$name]);
    }

    /**
     * Tries to load type for field name and throws exception if cannot
     * @param string $name name of the schema field
     */
    private function prepare_type($name)
    {
        if (isset($this->types[$name]))
        {
            return;
        }
        if (!$this->load_type($name))
        {
            //TODO: use dm exception
            throw new midcom_helper_datamanager_exception_type("The datatype for field {$name} could not be loaded");
        }
    }

    /**
     * Loads and initialized datatype for the given schema field, if config is not given schema is used
     *
     * @param string $name name of the schema field
     * @param array $config type configuration, if left as default the valu is read from schema
     * @return bool indicating success/failure
     */
    public function load_type($name, $config = null)
    {
        if (! $this->__isset($name))
        {
            throw new midcom_helper_datamanager_exception_widget("The field {$name} is not available");
        }

        if (is_null($config))
        {
            $config = $this->schema->fields[$name];
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

        $this->storage->load_type_data($this->types[$name], $name);

        return true;
    }

    /**
     * Validate the current types state. 
     *
     * @return array of validation errors (empty array means no errors)
     */
    public function validate()
    {
        $validation_errors = array();
        foreach ($this->schema->fields as $name => $config)
        {
            $this->prepare_type($name);
            if (! $this->$types['name']->validate())
            {
                $this->validation_errors[$name] = $this->types[$name]->validation_error;
            }
        }

        return $validation_errors;
    }

     public function __destructor()
     {
        // Specifically unset each datatype instance from here
        foreach ($this->types as $name => $type)
        {
            $this->__unset($name);
        }
     }
}

?>