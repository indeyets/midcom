<?php
/**
 * @package midcom_helper_datamanager
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

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

    public function __construct(&$schema, &$storage)
    {
        if (! is_a($schema, 'midcom_helper_datamanager_schema'))
        {
            throw new midcom_helper_datamanager_exception_widget('given schema is not instance of midcom_helper_datamanager_schema');
        }
        $this->schema = $schema
        if (! is_a($storage, 'midcom_helper_datamanager_storage'))
        {
            throw new midcom_helper_datamanager_exception_widget('given storage is not instance of midcom_helper_datamanager_storage');
        }
        $this->storage = $storage;
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

    public function __isset($name)
    {
        return $this->field_exists($name);
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
        if (!$this->load_widget($name))
        {
            //TODO: use dm exception
            throw new midcom_helper_datamanager_exception_widget("The datawidget for field {$name} could not be loaded");
        }
    }

    private function field_exists($name)
    {
        return isset($this->schema->fields[$name]);
    }

    /**
     * Loads and initialized datawidget for the given schema field, if config is not given schema is used
     *
     * @param string $name name of the schema field
     * @param array $config widget configuration, if left as default the valu is read from schema
     * @return bool indicating success/failure
     */
    public function load_widget($name, $config = null)
    {
        if (! $this->field_exists($name))
        {
            throw new midcom_helper_datamanager_exception_widget("The field {$name} is not defined in schema");
        }

        if (is_null($config))
        {
            $config = $this->schema->fields[$name];
        }

        // TODO: Move to schema class internal sanity checks
        if (! isset($config['widget']) )
        {
            throw new midcom_helper_datamanager_exception_widget("The field {$name} is missing widget");
        }

        $widget_class = $config['widget'];
        
        if (strpos($widget_class, '_') === false)
        {
            $widget_class = "midcom_helper_datamanager_widget_{$widget_class}";
        }

        $this->widgets[$name] = new $widget_class();
        if (! $this->widgets[$name]->initialize($name, $config['widget_config'], $this->storage))
        {
            return false;
        }

        return true;
    }
}

?>