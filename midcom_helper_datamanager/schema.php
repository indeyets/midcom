<?php
/**
 * @package midcom_helper_datamanager
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

include_once 'exceptions.php';

/**
 * Datamanager Schema class
 *
 * @package midcom_helper_datamanager
 */
class midcom_helper_datamanager_schema
{
    /**
     * The general field listing, indexed by their name. It contains the full field record
     * which has been completed with all defaults.
     *
     * @var Array
     */
    public $fields = Array();
    
    /**
     * The title of this schema, used to display schemas when
     *
     * @var string
     */
    public $description = '';

    /**
     * The name of the schema
     *
     * @var string
     */
    public $name = '';
    
    /**
     * The raw schema array as read by the system. This is a reference
     * into the schema database.
     *
     * @var Array
     */
    private $raw_schema = null;

    /**
     * The raw schema database as read by the system.
     *
     * @var Array
     */
    private $raw_schemadb = null;

    /**
     * The schema database path as read by the system.
     *
     * @var Array
     */
    private $schemadb_path = null;

    /**
     * A simple array holding the fields in the order they should be rendered identified
     * by their name.
     *
     * @var Array
     */
    public $field_order = array();

    /**
     * The operations to add to the form. 
     * 
     * This is a simple array of commands, valid entries are 'save', 'cancel', 'next' and 
     * 'previous', 'edit' is forbidden, other values are not interpreted by the DM infrastructure.
     *
     * @var Array
     */
    public $operations = array('save' => '', 'cancel' => '');
    
    private $configuration;
    
    public function __construct($schemadb, $name = null, $schemadb_path = null)
    {
        $this->schemadb_path = $schemadb_path;
        
        $this->configuration = new midcom_core_services_configuration_yaml('midcom_helper_datamanager');
        
        $this->load_schemadb($schemadb);

        if ($name === null)
        {
            reset($this->raw_schemadb);
            $name = key($this->raw_schemadb);
        }

        $this->load_schema($name);
    }
    
    /**
     * This function loads the schema database into the class
     *
     * @param mixed $schemadb Either the path or the already loaded schema database
     *     to use.
     */
    private function load_schemadb($schemadb)
    {
        if (is_string($schemadb))
        {
            try
            {
                $this->raw_schemadb = midcom_core_helpers_snippet::get($schemadb);
            }
            catch (OutOfBoundsException $e)
            {
                throw new midcom_helper_datamanager_exception_schema("Failed to parse the schema definition in '{$schemadb}'.");
            }
        }
        else if (is_array($schemadb))
        {
            $this->raw_schemadb = $schemadb;
        }
        else
        {
            throw new midcom_helper_datamanager_exception_schema('Failed to access the schema database: Invalid variable type while constructing.');
        }
    }
    
    /**
     * This function parses the schema and populates all members with the corresponding
     * information, completing defaults where necessary.
     *
     * @param string $name The name of the schema to load.
     */
    private function load_schema($name)
    {
        // Setup the raw schema reference
        if (! isset($this->raw_schemadb[$name]))
        {
            throw new Exception("The schema {$name} was not found in the schema database.");
            // This will exit.
        }
        $this->raw_schema =& $this->raw_schemadb[$name];
        
        /*
         * NOT JUST YET 
        // Populate the l10n_schema member
        if (array_key_exists('l10n_db', $this->raw_schema))
        {
            $l10n_name = $this->raw_schema['l10n_db'];
        }
        else
        {
            $l10n_name = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_COMPONENT);
        }
        $this->_l10n_schema = $_MIDCOM->i18n->get_l10n($l10n_name);
        */

        if (array_key_exists('operations', $this->raw_schema))
        {
            $this->operations = $this->raw_schema['operations'];
        }
        if (array_key_exists('customdata', $this->raw_schema))
        {
            $this->customdata = $this->raw_schema['customdata'];
        }
        if (array_key_exists('validation', $this->raw_schema))
        {
            $this->validation = $this->raw_schema['validation'];
        }
        if (array_key_exists('filters', $this->raw_schema))
        {
            $this->filters = $this->raw_schema['filters'];
        }

        $this->description = $this->raw_schema['description'];
        $this->name = $name;

        foreach ($this->raw_schema['fields'] as $name => $data)
        {
            $data['name'] = $name;
            $this->append_field($name, $data);
        }

        if (   $this->configuration->get('include_metadata_required')
            && $this->schemadb_path
            && $this->schemadb_path != $_MIDCOM->configuration->get('metadata_schema'))
        {
            // Include required fields from metadata schema to the schema
            $metadata_schema = midcom_helper_datamanager_schema::load_database($_MIDCOM->configuration->get('metadata_schema'));
            if (isset($metadata_schema['metadata']))
            {
                $prepended = false;
                foreach ($metadata_schema['metadata']->fields as $name => $field)
                {
                    if ($field['required'])
                    {
                        $this->append_field($name, $field);
                    }
                }
            }
        }
    }
    
    /**
     * This function adds a new field to the schema, appending it at the end of the
     * current field listing. 
     * 
     * This is callable after the construction of the object, to allow you to add 
     * additional fields like component required fields to the list.
     *
     * This can also be used to merge schemas together.
     *
     * It will complete the field's default and set the corresponding type and widget
     * setups.
     *
     * @param string $name The name of the field to add
     * @param Array $config The fields' full configuration set.
     */
    private function append_field($name, $config)
    {
        if (array_key_exists($name, $this->fields))
        {
            throw new midcom_helper_datamanager_exception_schema("Duplicate field {$name} encountered, schema operation is invalid. Aborting.");
            // This will exit.
        }

        $this->field_order[] = $name;
        $this->complete_field_defaults($config);
        $this->fields[$name] = $config;
    }
    
    /**
     * Internal helper function which completes all missing field declaration members
     * so that all fields can be treated uniformly.
     *
     */
    private function complete_field_defaults(&$config)
    {
        // Sanity check for broken schemas, missing type/widget would cause DM & PHP to barf later on...
        if (   !array_key_exists('type', $config)
            || empty($config['type']))
        {
            throw new Exception("Field '{$config['name']}' in schema '{$this->name}' loaded from {$this->schemadb_path} is missing *type* definition");
            // this will exit
        }
        
        if (   !array_key_exists('widget', $config)
            || empty($config['widget']))
        {
            throw new Exception("Field '{$config['name']}' in schema '{$this->name}' loaded from {$this->schemadb_path} is missing *widget* definition");
            // this will exit
        }
        
        /* Rest of the defaults */
        
        // Simple ones
        $simple_defaults = array
        (
            'description' => null,
            'helptext' => null,
            'read_privilege' => null,
            'write_privilege' => null,
            'default' => null,
            'readonly' => false,
            'hidden' => false,
            'required' => false,
        );
        foreach ($simple_defaults as $property => $value)
        {
            if (! array_key_exists( $property, $config))
            {
                $config[$property] = $value;
            }
        }
        unset($property, $value);

        if (! array_key_exists('index_method', $config))
        {
            $config['index_method'] = 'auto';
        }
        
        if (! array_key_exists('index_merge_with_content', $config))
        {
            $config['index_merge_with_content'] = true;
        }

        if (   ! array_key_exists('type_config', $config)
            || ! is_array($config['type_config']))
        {
            $config['type_config'] = array();
        }
        
        if (   ! array_key_exists('widget_config', $config)
            || ! is_array($config['type_config']))
        {
            $config['widget_config'] = array();
        }
    }

    /**
     * Schema translation helper, usable by components from the outside.
     *
     * The l10n db from the schema is used first, the Datamanager l10n db second and
     * the MidCOM core l10n db last. If the string is not found in both databases,
     * the string is returned unchanged.
     *
     * Note, that the string is translated to <i>lower case</i> before
     * translation, as this is the usual form how strings are in the
     * l10n database. (This is for backwards compatibility mainly.)
     *
     * @param string $string The string to be translated.
     * @return string The translated string.
     */
    public function translate_schema_string($string)
    {
        $translate_string = strtolower($string);

        // if (   $this->_l10n_schema !== null
        //     && $this->_l10n_schema->string_available($translate_string))
        // {
        //     return $this->_l10n_schema->get($translate_string);
        // }
        // else if ($this->_l10n->string_available($translate_string))
        // {
        //     return $this->_l10n->get($translate_string);
        // }
        // else if ($this->_l10n_midcom->string_available($translate_string))
        // {
        //     return $this->_l10n_midcom->get($translate_string);
        // }

        return $string;
    }
    
    /**
     * Helper function which transforms a raw schema database (either already parsed or
     * based on a URL to a schemadb) into a list of schema class instances.
     *
     * This function may be called statically.
     *
     * @param mixed $raw_db Either an already created raw schema array, or a midgard_get_snippet_content
     *     compatible URL to a snippet / file from which the db should be loaded.
     * @return Array An array of midcom_helper_datamanager_schema class instances.
     * @see midcom_get_snippet_content()
     */
    static function load_database($raw_db)
    {
        $path = null;
        if (is_string($raw_db))
        {
            $path = $raw_db;
            try
            {
                $raw_db = midcom_core_helpers_snippet::get($raw_db);
            }
            catch (OutOfBoundsException $e)
            {
                throw new midcom_helper_datamanager_exception_type("Failed to parse the schema database loaded from '{$raw_db}'");
            }
        }

        $schemadb = array();

        foreach ($raw_db as $name => $raw_schema)
        {
            $schemadb[$name] = new midcom_helper_datamanager_schema($raw_db, $name, $path);
        }
        
        return $schemadb;
    }
    
    /**
     * Check if given field name exists in this schema
     * @param string $name name of the schema field
     */
    public function field_exists($name)
    {
        return isset($this->fields[$name]);
    }
}

?>