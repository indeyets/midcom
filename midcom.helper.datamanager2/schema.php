<?php
/**
 * @package midcom.helper.datamanager2
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Datamanger 2 Schema class.
 *
 * This class encaspulates Datamanager Schemas. It contains all information required to construct
 * the types and widgets of a given data schema. The base class constructs out of a Datamanager 2
 * Schema definition, which is not compatible with a legacy Datamanager 1 Schema, you need to
 * use the appropriate subclass to handle them dynamically.
 *
 * <b>Schema Definition</b>
 *
 * See http://www.midgard-project.org/documentation/midcom-2-5-datamanager-rewrite-schema-definition/
 * for now.
 *
 * <b>Storage</b>
 * When using the Midgard storage backend, it is possible to define a callback class to be called
 * that will then save the object. The class is defined as follows. Please note that if the classname
 * follows the midcom hierarcy, it may be loaded automaticly.
 *
 * The class must satisfy the following interfaces:
 * <code>
 * class midcom_admin_parameters_callback {
 *      // params:
 *      // name: the name of the field
 *      // data: the data that comes from the type defined.
 *      // storage: a reference to the datamanagers storageclass.
 *      function on_load_data($name,&$storage);
 *      function on_store_data($name, $data,&$storage);
 * }
 * <code>
 *
 * What the functions should return depends on the datatype they return to.
 *
 * The callback may be defined in the schema like this:
 * <code>
 * 'fields' => Array
 * (
 *      'parameters' => Array
 *       (
 *           'title' => 'url name',
 *           'storage' => Array
 *            (
 *                   'location' => 'object',
 *                   'callback' => 'midcom_admin_parameters_callbacks_storage',
 *            ),
 *            'type' => ..,
 *            'widget' => ..
 *       ),
 * </code>
 *
 * <b>Important</b>
 * It is only possible to define one storage callback per schema! If you want more than one,
 * encapsulate this in your class.
 *
 * @todo Complete documentation
 *
 * @package midcom.helper.datamanager2
 */
class midcom_helper_datamanager2_schema extends midcom_baseclasses_components_purecode
{
    /**
     * The general field listing, indexed by their name. It contains the full field record
     * which has been completed with all defaults.
     *
     * You may changes settings outlined here, be aware though, that types and/or widgets
     * spawned based on this schema do normally not reference these configuration block
     * directly, but instead they usually merge the settings made into their own internal
     * default config. You can still adjust the types, which usually have their settings
     * available as public members though.
     *
     * @var Array
     */
    var $fields = Array();

    /**
     * The title of this schema, used to display schemas when
     *
     * @var string
     */
    var $description = '';

    /**
     * The name of the schema ("identifier").
     *
     * @var string
     */
    var $name = '';

    /**
     * The primary L10n DB to use for schema translation.
     *
     * @var midcom_services__i18n_l10n
     */
    var $l10n_schema = null;

    /**
     * The raw schema array as read by the system. This is a reference
     * into the schema database.
     *
     * @access private
     * @var Array
     */
    var $_raw_schema = null;

    /**
     * The raw schema database as read by the system.
     *
     * @access private
     * @var Array
     */
    var $_raw_schemadb = null;

    /**
     * A simple array holding the fields in the order they should be rendered identified
     * by their name.
     *
     * @var Array
     */
    var $field_order = Array();

    /**
     * The operations to add to the form. This is an simple array of commands, valid entries
     * are 'save', 'cancel', 'next' and 'previous', 'edit' is forbidden, other values are not
     * interpreted by the DM infrastructure.
     *
     * @var Array
     */
    var $operations = Array('save' => '', 'cancel' => '');

    /**
     * This array holds custom information attached to this schema. Its exact usage is component
     * dependant.
     *
     * @var Array
     */
    var $customdata = Array();

    /**
     * Form-wide validation callbacks, executed by QuickForm. This is a list of arrays. Each
     * array defines a single callback, along with a snippet or file location that should be
     * auto-loaded in case the function is missing.
     *
     * @var Array
     */
    var $validation = Array();

    /**
     * Custom data filter rules. This is a list of arrays. Each array defines a single callback,
     * a field list according to HTML_QuickForm::applyFilter along with a snippet or file location
     * that should be auto-loaded in case the function is missing.
     *
     * @var Array
     */
    var $filters = Array();

    /**
     * Construct a schema, takes a schema snippet URI resolveable through the
     * midcom_get_snippet_content() helper function.
     *
     * @param mixed $schemapath Either the path or the already loaded schema database
     *     to use.
     * @param string $name The name of the Schema to use. It must be a member in the
     *     specified schema database. If unspecified, the default schema is used.
     * @see midcom_get_snippet_content()
     */
    function midcom_helper_datamanager2_schema($schemadb, $name = null)
    {
         $this->_component = 'midcom.helper.datamanager2';
         parent::midcom_baseclasses_components_purecode();

        $this->_load_schemadb($schemadb);

        if ($name === null)
        {
            reset($this->_raw_schemadb);
            $name = key($this->_raw_schemadb);
        }

        $this->_load_schema($name);
    }

    /**
     * This functnio loads the schema database into the class, either from a copy
     * already in memory, or from an URL resolvable by midcom_get_snippet_content.
     *
     * @param mixed $schemapath Either the path or the already loaded schema database
     *     to use.
     * @see midcom_get_snippet_content()
     */
    function _load_schemadb($schemadb)
    {
        if (is_string($schemadb))
        {
            $data = midcom_get_snippet_content($schemadb);
            $result = eval ("\$this->_raw_schemadb = Array ( {$data}\n );");
            if ($result === false)
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                    "Failed to parse the schema definition in '{$schemadb}', see above for PHP errors.");
                // This will exit.
            }
        }
        else if (is_array($schemadb))
        {
            $this->_raw_schemadb = $schemadb;
        }
        else
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_print_r('Passed schema db was:', $schemadb);
            debug_pop();
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Failed to access the schema database: Invalid variable type while constructing.');
            // This will exit.
        }
    }

    /**
     * This function parses the schema and populates all members with the corresponding
     * information, completing defaults where neccessary.
     *
     * It will automatically translate all descriptive fields according to the rules
     * outlined in the translate_schema_field() helper function.
     *
     * @param string $name The name of the schema to load.
     */
    function _load_schema($name)
    {
        // Setup the raw schema reference
        if (! array_key_exists($name, $this->_raw_schemadb))
        {
            $_MIDCOM->generate_error("The schema {$name} was not found in the schema database.",
                MIDCOM_ERRCRIT);
            // This will exit.
        }
        $this->_raw_schema =& $this->_raw_schemadb[$name];

        // Populate the l10n_schema member
        if (array_key_exists('l10n_db', $this->_raw_schema))
        {
            $l10n_name = $this->_raw_schema['l10n_db'];
        }
        else
        {
            $l10n_name = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_COMPONENT);
        }
        $this->_l10n_schema = $_MIDCOM->i18n->get_l10n($l10n_name);

        if (array_key_exists('operations', $this->_raw_schema))
        {
            $this->operations = $this->_raw_schema['operations'];
        }
        if (array_key_exists('customdata', $this->_raw_schema))
        {
            $this->customdata = $this->_raw_schema['customdata'];
        }
        if (array_key_exists('validation', $this->_raw_schema))
        {
            $this->validation = $this->_raw_schema['validation'];
        }
        if (array_key_exists('filters', $this->_raw_schema))
        {
            $this->filters = $this->_raw_schema['filters'];
        }

        $this->description = $this->_raw_schema['description'];
        $this->name = $name;

        foreach ($this->_raw_schema['fields'] as $name => $data)
        {
            $data['name'] = $name;
            $this->append_field($name, $data);
        }
    }

    /**
     * This function adds a new field to the schema, appending it at the end of the
     * current field listing. This is callable after the construction of the object,
     * to allow you to add additional fields like component required fields to the list.
     *
     * This can also be used to merge schemas together.
     *
     * It will complete the field's default and set the corresponding type and widget
     * setups.
     *
     * @param string $name The name of the field to add
     * @param Array $config The fields' full configuration set.
     */
    function append_field($name, $config)
    {
        if (array_key_exists($name, $this->fields))
        {
            $_MIDCOM->generate_error("Duplicate field {$name} encountered, schema operation is invalid. Aborting.",
                MIDCOM_ERRCRIT);
            // This will exit.
        }

        $this->field_order[] = $name;
        $this->_complete_field_defaults($config);
        $this->fields[$name] = $config;
    }

    /**
     * Internal helper function which completes all missing field declaration members
     * so that all fields can be treated uniformly.
     *
     * @TODO Refactor in subfunctions for better readability.
     */
    function _complete_field_defaults(&$config)
    {
        if (! array_key_exists('description', $config))
        {
            $config['description'] = null;
        }
        if (! array_key_exists('helptext', $config))
        {
            $config['helptext'] = null;
        }
        if (! array_key_exists('static_prepend', $config))
        {
            $config['static_prepend'] = null;
        }
        if (! array_key_exists('static_append', $config))
        {
            $config['static_append'] = null;
        }

        if (! array_key_exists('readonly', $config))
        {
            $config['readonly'] = false;
        }
        if (! array_key_exists('hidden', $config))
        {
            $config['hidden'] = false;
        }
        if (! array_key_exists('aisonly', $config))
        {
            $config['aisonly'] = false;
        }
        if (! array_key_exists('read_privilege', $config))
        {
            $config['read_privilege'] = null;
        }
        if (! array_key_exists('write_privilege', $config))
        {
            $config['write_privilege'] = null;
        }

        if (! array_key_exists('required', $config))
        {
            $config['required'] = false;
        }

        if (! array_key_exists('storage', $config))
        {
            $config['storage'] = Array
            (
                'location' => 'parameter',
                'domain' => 'midcom.helper.datamanager2'
            );
        }
        else
        {
            if (is_string($config['storage']))
            {
                $config['storage'] = Array ( 'location' => $config['storage'] );
            }
            if ($config['storage']['location'] == 'parameter')
            {
                if (! array_key_exists('domain', $config['storage']))
                {
                    $config['storage']['domain'] = 'midcom.helper.datamanager2';
                }
                
                if (! array_key_exists('multilang', $config['storage']))
                {
                    $config['storage']['multilang'] = false;
                }
            }
        }
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
            $config['type_config'] = Array();
        }
        if (   ! array_key_exists('widget_config', $config)
            || ! is_array($config['type_config']))
        {
            $config['widget_config'] = Array();
        }
        if (! array_key_exists('customdata', $config))
        {
            $config['customdata'] = Array();
        }

        if (   ! array_key_exists('validation', $config)
            || ! $config['validation'])
        {
            $config['validation'] = Array();
        }
        else if (! is_array($config['validation']))
        {
            $config['validation'] = Array($config['validation']);
        }
        foreach ($config['validation'] as $key => $rule)
        {
            if (! is_array($rule))
            {
                if ($rule['type'] == 'compare')
                {
                    $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                        "Missing compare_with option for compare type rule {$key} on field {$config['name']}, this is a required option.");
                    // This will exit.
                }
                $config['validation'][$key] = Array
                (
                    'type' => $rule,
                    'message' => "validation failed: {$rule}",
                    'format' => ''
                );
            }
            else
            {
                if (! array_key_exists('type', $rule))
                {
                    $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                        "Missing validation rule type for rule {$key} on field {$config['name']}, this is a required option.");
                    // This will exit.
                }
                if (! array_key_exists('message', $rule))
                {
                    $config['validation'][$key]['message'] = "validation failed: {$rule['type']}";
                }
                if (! array_key_exists('format', $rule))
                {
                    $config['validation'][$key]['format'] = '';
                }
                if ($rule['type'] == 'compare')
                {
                    if (! array_key_exists('compare_with', $rule))
                    {
                        $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                            "Missing compare_with option for compare type rule {$key} on field {$config['name']}, this is a required option.");
                        // This will exit.
                    }
                }
            }
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
    function translate_schema_string ($string)
    {
        $translate_string = strtolower($string);

        if (   $this->_l10n_schema !== null
            && $this->_l10n_schema->string_available($translate_string))
        {
            return $this->_l10n_schema->get($translate_string);
        }
        else if ($this->_l10n->string_available($translate_string))
        {
            return $this->_l10n->get($translate_string);
        }
        else if ($this->_l10n_midcom->string_available($translate_string))
        {
            return $this->_l10n_midcom->get($translate_string);
        }

        return $string;
    }

    /**
     * Helper function which transforms a raw schema database (either already parsed or
     * based on an URL to a schemadb) into a list of schema class instances.
     *
     * This function may (and usually will) be called statically.
     *
     * @param mixed $raw_db Either an already created raw schema array, or a midgard_get_snippet_content
     *     compatible URL to a snippet / file from which the db should be loaded.
     * @return Array An array of midcom_helper_datamanager2_schema class instances.
     * @see midcom_get_snippet_content()
     */
    function load_database($raw_db)
    {
        if (is_string($raw_db))
        {
            $data = midcom_get_snippet_content($raw_db);
            $result = eval ("\$raw_db = Array ( {$data}\n );");
            if ($result === false)
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                    "Failed to parse the schema database loaded from '{$raw_db}', see above for PHP errors.");
                // This will exit.
            }
        }

        $schemadb = Array();

        foreach ($raw_db as $name => $raw_schema)
        {
            $schemadb[$name] = new midcom_helper_datamanager2_schema($raw_db, $name);
        }
        return $schemadb;
    }

    /**
     * Registers a schema into the session so it is readable by the imagepopup.
     * @return string the form sessionkey
     * @throws none
     * @access public
     *
     */
     function register_to_session($guid)
     {
        $session =& $_MIDCOM->get_service('session');
        $key = $this->name .  $guid;

        $session->set('midcom.helper.datamanager2', $key, $this->_raw_schema);
        return $key;

     }
}

?>
