<?php
/**
 * @package midcom_helper_datamanager
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Datamanager Form Manager core class.
 *
 * This class controls all form rendering and basic form data i/o. It works independent
 * of any data storage, getting its defaults from some external controlling instance in
 * the form of a type array (f.x. a datamanager class can provide this). The list of types
 * is taken by-reference.
 *
 * @package midcom_helper_datamanager
 */
class midcom_helper_datamanager_form
{
    /**
     * The schema (not the schema <i>database!</i>) to use for operation. This variable will always contain a parsed
     * representation of the schema, so that one can swiftly switch between individual schemas
     * of the Database.
     *
     * This member is initialized by-reference.
     *
     * @var Array
     */
    protected $schema = null;

    protected $types = null;

    protected $datamanager = null;
    
    protected $storage = null;
    
    protected $frozen = false;

    public $widgets = null;
    
    /**
     * The namespace of the form. This value is to be considered read only.
     *
     * This is the Namespace to use for all HTML/CSS/JS elements. It is deduced by the formmanager
     * and tries to be as smart as possible to work safely with more then one form on a page.
     *
     * You have to prefix all elements which must be unique using this string (it includes a trailing
     * underscore).
     *
     * @var const string
     */
    public $namespace = '';
    
    /**
     * Initializes the Form manager with a list of types for a given schema.
     *
     * @param midcom_helper_datamanager_schema &$schema The schema to use for processing. This
     *     variable is taken by reference.
     * @param Array &$types A list of types matching the passed schema, used as a basis for the
     *     form types. This variable is taken by reference.
     */
    public function __construct(&$schema, &$types, &$storage, &$datamanager)
    {
        if (! $datamanager instanceof midcom_helper_datamanager_datamanager)
        {
            throw new midcom_helper_datamanager_exception_datamanager('given datamanager is not instance of midcom_helper_datamanager');
        }
        $this->datamanager =& $datamanager;
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

        $this->resolve_namespace();

        $this->load_widgets();
    }

    protected function resolve_namespace()
    {
        // TODO figure something shorter based on schema and storage
        $this->namespace = md5("{$this->schema->schemadb_path}:{$this->schema->name}:" . $this->storage->get_identifier());
    }

    /**
     * Clears possible dangling references and instance new widget proxy object
     */
    public function load_widgets()
    {
        if (! $this->types instanceof midcom_helper_datamanager_typeproxy)
        {
            throw new midcom_helper_datamanager_exception_datamanager('$this->types is not instance of midcom_helper_datamanager_typeproxy');
        }
        unset($this->widgets);
        $this->widgets = new midcom_helper_datamanager_widgetproxy($this->schema, $this->storage, $this->types, $this->namespace);
    }

    /**
     * Magic getters for the contents of form in a given format
     */
    public function __get($key)
    {
        // Whole form in different formats
        switch ($key)
        {
            case 'as_html':
                return $this->render_html_all();

            case 'as_tal':
                return $this->render_tal_all();
        }
        
        // Form parts in different formats
        if (preg_match('/^(.*?)_as_(.*?)$/', $key, $matches))
        {
            $property = $matches[1];
            $format = $matches[2];
            $method = "render_{$property}_{$format}";
            if (is_callable(array($this, $method)))
            {
                return $this->$method();
            }
        }
    }

    /**
     * Renders the whole form as HTML
     */
    public function render_html_all()
    {
        $output = '';
        $output .= $this->start_as_html;

        foreach($this->schema->fields as $name => $field_data)
        {
            $output .= $this->widgets->$name->as_html . "\n";
        }

        $output .= $this->toolbar_as_html;
        $output .= $this->end_as_html;
        
        return $output;
    }

    /**
     * Renders the whole form as TAL
     */
    public function render_tal_all()
    {
        $output = '';
        $output .= $this->start_as_tal;

        foreach($this->schema->field_order as $name)
        {
            $output .= $this->widgets->$name->as_tal . "\n";
        }

        $output .= $this->toolbar_as_tal;
        $output .= $this->end_as_tal;
        
        return $output;
    }

    public function render_start_html()
    {
        return "<form method=\"post\" class=\"midcom_helper_datamanager\">\n";
    }
    
    public function render_toolbar_html()
    {
        if ($this->frozen)
        {
            return '';
        }
        $output  = "<div class=\"form_toolbar\">\n";
        foreach ($this->schema->operations as $operation => $config)
        {
            $label = ucfirst($operation);
            $accesskey = substr($operation, 0, 1);
            $output .= "    <input type=\"submit\" name=\"{$this->namespace}_{$operation}\" class=\"{$operation}\" accesskey=\"{$accesskey}\" value=\"{$label}\" />\n";
        }
        
        $output .= "</div>\n";
        return $output;
    }

    public function render_end_html()
    {
        return "</form>\n";
    }

    protected function pass_results_to_method($method, &$results, $pass_null = false)
    {
        foreach ($this->schema->field_order as $field_name)
        {
            if (!array_key_exists($field_name, $results))
            {
                if (!$pass_null)
                {
                    continue;
                }
                $this->widgets->$field_name->$method(null);
                continue;
            }
            $this->widgets->$field_name->$method($results[$field_name]);
        }
    }

    public function compute_form_result()
    {
        foreach ($this->schema->operations as $operation => $config)
        {
            $var = "{$this->namespace}_{$operation}";
            if (isset($_POST[$var]))
            {
                return $operation;
            }
        }
        
        if (is_null($this->storage->object))
        {
                return 'create';
        }
         
        return 'edit';
    }

    public function process()
    {
        throw new midcom_helper_datamanager_exception_datamanager('Method ' . __FUNCTION__ . ' must be overridden.');
    }
    
    public function freeze()
    {
        $this->frozen = true;
        foreach ($this->schema->field_order as $field_name)
        {
            $this->widgets->$field_name->freeze();
        }
    }
    
    public function unfreeze()
    {
        $this->frozen = false;
        foreach ($this->schema->field_order as $field_name)
        {
            $this->widgets->$field_name->unfreeze();
        }
    }

    public function get_submit_values()
    {
        $values = array();
        foreach ($this->schema->field_order as $field_name)
        {
            $widget =& $this->widgets->$field_name;
            $var = "{$widget->namespace}_{$widget->main_input_name}";
            if (isset($_FILES[$var]))
            {
                $values[$field_name] = $_POST[$var];
                continue;
            }
            if (isset($_POST[$var]))
            {
                $values[$field_name] = $_POST[$var];
                continue;
            }
        }

        return $values;
    }
}

?>