<?php
/**
 * @package midcom_helper_datamanager
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

include_once 'widget.php';

/**
 * Datamanager widget proxy, loaded as $dm->widgets
 *
 * @package midcom_helper_datamanager
 */
class midcom_helper_datamanager_widgetproxy
{
    private $schema = false;
    private $storage = false;
    private $widgets = array();
    private $types = false;
    protected $namespace = '';

    public function __construct(&$schema, &$storage, &$types)
    {
        if (! $schema instanceof midcom_helper_datamanager_schema)
        {
            throw new midcom_helper_datamanager_exception_widget('given schema is not instance of midcom_helper_datamanager_schema');
        }
        $this->schema =& $schema;
        if (! $storage instanceof midcom_helper_datamanager_storage)
        {
            throw new midcom_helper_datamanager_exception_widget('given storage is not instance of midcom_helper_datamanager_storage');
        }
        $this->storage =& $storage;
        if (! $types instanceof midcom_helper_datamanager_typeproxy)
        {
            throw new midcom_helper_datamanager_exception_widget('given types is not instance of midcom_helper_datamanager_typeproxy');
        }
        $this->types =& $types;
    }

    public function __get($name)
    {
        $this->prepare_widget($name);
        // PONDER: how does this work when the original call is something like $dm->widgets->fieldname->value
        return $this->widgets[$name];
    }

    public function __set($name, $value)
    {
        $this->prepare_widget($name);
        // PONDER: how does this work when the original call is something like $dm->widgets->fieldname->value = x
        $this->widgets[$name] = $value;
    }

    /**
     * Checks if we have the field corresponding to the property name
     * in schema and corresponding datatype available
     */
    public function __isset($name)
    {
        if (! $this->schema->field_exists($name))
        {
            return false;
        }
        return isset($this->types->$name);
    }

    public function __unset($name)
    {
        // PONDER: Do we need to clear other references, if so do it here
        unset($this->widgets[$name]);
    }

    /**
     * Tries to load widget and throws exception if cannot
     */
    private function prepare_widget($name)
    {
        if (isset($this->widgets[$name]))
        {
            return;
        }
        if (! $this->load_widget($name))
        {
            //TODO: use dm exception
            throw new midcom_helper_datamanager_exception_widget("The widget for field {$name} could not be loaded");
        }
    }

    /**
     * Loads and initialized widget for the given schema field, if config is not given schema is used
     *
     * @param string $name name of the schema field
     * @param array $config widget configuration, if left as default the valu is read from schema
     * @return bool indicating success/failure
     */
    public function load_widget($name, $config = null)
    {
        if (! $this->__isset($name))
        {
            throw new midcom_helper_datamanager_exception_widget("The field {$name} is not available");
        }

        if (is_null($config))
        {
            $config = $this->schema->fields[$name];
        }

        $widget_class = $config['widget'];
        
        if (strpos($widget_class, '_') === false)
        {
            $widget_class = "midcom_helper_datamanager_widget_{$widget_class}";
        }

        $this->widgets[$name] = new $classname();
        if (! $this->widgets[$name]->initialize($name, $config['widget_config'], $this->schema, $this->types->$name, $this->namespace))
        {
            return false;
        }

        return true;
    }

     public function __destructor()
     {
        // Specifically unset each datatype instance from here
        foreach ($this->widgets as $name => $widget)
        {
            $this->__unset($name);
        }
     }
}

?>