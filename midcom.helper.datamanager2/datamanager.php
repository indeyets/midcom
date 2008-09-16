<?php
/**
 * @package midcom.helper.datamanager2
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Datamanager 2 Data Manager core class.
 *
 * This class controls all type I/O operations, including entering and exiting
 * editing operations and creation support. It brings Types, Schemas and Storage objects
 * together.
 *
 * @package midcom.helper.datamanager2
 */
class midcom_helper_datamanager2_datamanager extends midcom_baseclasses_components_purecode
{
    /**
     * The schema database to use for operation. This variable will always contain a parsed
     * representation of the schema, so that one can swiftly switch between individual schemas
     * of the Database. This is a list of midcom_helper_datamanager2_schema
     * instances, indexed by their name.
     *
     * @var Array
     * @access private
     */
    var $_schemadb = null;

    /**
     * This variable holds the schema currently in use, it has been created from the array
     * stored in the $_schemadb member.
     *
     * This object can be modified as long as the types are not initialized. If you change
     * the schema afterwards, the changes will not propagate to any dependant object until
     * you reinitialize the class.
     *
     * @var midcom_helper_datamanager2_schema
     */
    var $schema = null;

    /**
     * The id (array index) of the current schema
     * @var string schema_name
     */
    var $schema_name = '';

    /**
     * This is the storage implementation which is used for operation on the types. It encapsulates
     * the storage target.
     *
     * @var midcom_helper_datamanager2_storage
     * @access public
     */
    var $storage = null;

    /**
     * This is a listing of all types that have been loaded from the storage object. You may
     * manipulate these types and their values at will, and then store them back to the database
     * using the functions available in this class.
     *
     * @var Array
     */
    var $types = null;

    /**
     * This variable contains an Array of all validation errors that have occurred
     * during saving. As outlined in the type, these messages my have inline-html
     * in it and it is assumed to be localized.
     *
     * The errors are indexed by field name.
     *
     * @var Array
     * @see midcom_helper_datamanager2_type::$validation_error
     */
    var $validation_errors = Array();

    /**
     * Reference to the form manager instance which is currently in use. Usually, it is created and referenced here by the controller
     * class during initialization.
     *
     * @var midcom_helper_datamanager2_formmanager
     */
    var $formmanager = null;

    /**
     * The constructor loads the schema database to use but does nothing else
     * so far.
     *
     * @param Array &$schemadb A list of midcom_helper_datamanager2_schema instances,
     *     indexed by their schema name. This member is taken by reference.
     * @see midcom_helper_datamanager2_schema::load_database()
     */
    function __construct(&$schemadb)
    {
         $this->_component = 'midcom.helper.datamanager2';
         parent::__construct();
         $this->_schemadb =& $schemadb;
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
    function set_schema($name = null)
    {
        if (!is_array($this->_schemadb))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("The active schema database is invalid.", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        if (   $name !== null
            && ! array_key_exists($name, $this->_schemadb))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("The schema {$name} was not found in the active schema database.", MIDCOM_LOG_INFO);
            debug_pop();
            return false;
        }

        if ($name === null)
        {
            reset($this->_schemadb);
            $name = key($this->_schemadb);
        }

        $this->schema =& $this->_schemadb[$name];
        $this->schema_name = $name;

        return $this->_load_types();
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
     *     midcom_helper_datamanager2_storage.
     * @return boolean Indicating success.
     */
    function set_storage(&$object)
    {
        if ($this->schema === null)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('Cannot initialize to a storage object if the schema is not yet set.', MIDCOM_LOG_INFO);
            debug_pop();
            return false;
        }

        if (! is_a($object, 'midcom_helper_datamanager2_storage'))
        {
            $this->storage = new midcom_helper_datamanager2_storage_midgard($this->schema, $object);
        }
        else
        {
            $this->storage =& $object;
        }

        // For reasons I do not completely comprehend, PHP drops the storage references into the types
        // in the lines above. Right now the only solution (except debugging this 5 hours long line
        // by line) I see is explicitly setting the storage references in the types.
        foreach ($this->types as $type => $copy)
        {
            $this->types[$type]->set_storage($this->storage);
        }

        $this->storage->load($this->types);

        return true;
    }

    /**
     * This function will create all type objects for the current schema. It will load class
     * files where necessary (using require_once), and then create a set of instances
     * based on the schema.
     *
     * @return boolean Indicating success
     * @access private
     */
    function _load_types()
    {
        $this->types = Array();

        foreach ($this->schema->fields as $name => $config)
        {
            if (!isset($config['type']) )
            {
                throw new Exception("The field {$name} is missing type");
            }

            $filename = MIDCOM_ROOT . "/midcom/helper/datamanager2/type/{$config['type']}.php";
            $classname = "midcom_helper_datamanager2_type_{$config['type']}";
            require_once($filename);

            $this->types[$name] = new $classname();
            if (! $this->types[$name]->initialize($name, $config['type_config'], $this->storage))
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("Failed to initialize the type for {$name}, see the debug level log for full details.",
                    MIDCOM_LOG_INFO);
                debug_pop();
                return false;
            }
        }

        return true;
    }


    /**
     * This function is a shortcut that combines set_schema and set_storage together.
     * The schema name is looked up in the parameter 'midcom.helper.datamanager2/schema_name',
     * if it is not found, the first schema from the schema database is used implicitly.
     *
     * @see set_schema()
     * @see set_storage()
     * @param mixed &$object A reference to either a MidCOM DBA class or a subclass of
     *     midcom_helper_datamanager2_storage.
     * @param boolean $strict Whether we should strictly use only the schema given by object params
     * @return boolean Indicating success.
     */
    function autoset_storage(&$object, $strict = false)
    {
        if (is_a($object, 'midcom_helper_datamanager2_storage'))
        {
            $schema = $object->object->get_parameter('midcom.helper.datamanager2', 'schema_name');
        }
        else
        {
            $schema = $object->get_parameter('midcom.helper.datamanager2', 'schema_name');
        }

        if (! $schema)
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
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("Given schema name {$schema} was not found, reverting to default.", MIDCOM_LOG_INFO);
                debug_pop();
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
    function save()
    {
        if (! $this->validate())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add(count($this->validation_errors) . ' fields have failed validation, cannot save.',
                MIDCOM_LOG_WARN);
            debug_print_r('Validation errors:', $this->validation_errors);
            debug_pop();
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
        $this->validation_errors = Array();
        $validated = true;
        foreach ($this->schema->fields as $name => $config)
        {
            if (! $this->types[$name]->validate())
            {
                $this->validation_errors[$name] = $this->types[$name]->validation_error;
                $validated = false;
            }
        }
        return $validated;
    }

    /**
     * Little helper function returning an associative array of all field values converted to HTML
     * using their default convert_to_html option.
     *
     * @return Array All field values in their HTML representation indexed by their name.
     */
    function get_content_html()
    {
        $result = Array();
        foreach ($this->schema->field_order as $name)
        {
            $result[$name] = $this->types[$name]->convert_to_html();
        }
        return $result;
    }

    /**
     * Little helper function returning an associative array of all field values converted to XML
     * using their default convert_to_csv or convert_to_raw options.
     *
     * @return Array All field values in their XML representation indexed by their name.
     */
    function get_content_xml()
    {
        $result = Array();
        foreach ($this->schema->field_order as $name)
        {
            if (is_a($this->types[$name], 'midcom_helper_datamanager2_type_blobs'))
            {
                $result[$name] = explode(',', $this->types[$name]->convert_to_csv());
            }
            elseif (is_a($this->types[$name], 'midcom_helper_datamanager2_type_select'))
            {
                $this->types[$name]->csv_export_key = true;
                $this->types[$name]->multiple_storagemode = 'array';
                $result[$name] = $this->types[$name]->convert_to_storage();
            }
            else
            {
                $result[$name] = $this->types[$name]->convert_to_storage();
            }
        }
        return $result;
    }

    /**
     * Little helper function returning an associative array of all field values converted to CSV
     * using their default convert_to_csv option.
     *
     * @return Array All field values in their CSV representation indexed by their name.
     */
    function get_content_csv()
    {
        $result = Array();
        foreach ($this->schema->field_order as $name)
        {
            $result[$name] = $this->types[$name]->convert_to_csv();
        }
        return $result;
    }

    /**
     * Little helper function returning an associative array of all field values converted to email-friendly format
     * using their default convert_to_email option.
     *
     * @return Array All field values in their CSV representation indexed by their name.
     */
    function get_content_email()
    {
        $result = Array();
        foreach ($this->schema->field_order as $name)
        {
            $result[$name] = $this->types[$name]->convert_to_email();
        }
        return $result;
    }

    /**
     * Little helper function returning an associative array of all field values converted to
     * their raw storage representation..
     *
     * @return Array All field values in their raw storage representation indexed by their name.
     */
    function get_content_raw()
    {
        $result = Array();
        foreach ($this->schema->field_order as $name)
        {
            if (!isset($this->types[$name]))
            {
                continue;
            }
            $result[$name] = $this->types[$name]->convert_to_raw();
        }
        return $result;
    }

    /**
     * This function displays a quick view of the record, using some simple div based layout,
     * which can be formatted using CSS.
     *
     * Be aware that this is only geared for simple administration interfaces, it will provide
     * *no* editing capabilities (like AJAX) etc. If you want that to work, you need a formmanger
     * instance instead.
     */
    function display_view()
    {
        // iterate over all types so that they can add their piece to the form
        echo "<div class=\"midcom_helper_datamanager2_view\">\n";
        foreach ($this->schema->fields as $name => $config)
        {
            if (   isset($config['hidden'])
                && $config['hidden'])
            {
                continue;
            }
            echo "<div class=\"field\">\n";
            echo '<div class="title" style="font-weight: bold;">' . $this->schema->translate_schema_string($this->schema->fields[$name]['title']) . "</div>\n";
            echo '<div class="value" style="margin-left: 5em; min-height: 1em;">';

            if ($config['widget'] == 'chooser')
            {
                $this->formmanager =& new midcom_helper_datamanager2_formmanager($this->schema, $this->types);
                $this->formmanager->initialize();
                $this->formmanager->widgets[$name]->render_content();
            }
            else
            {
                echo $this->types[$name]->convert_to_html();
            }

            echo "</div>\n";
            echo "</div>\n";
        }
        echo "</div>\n";
    }
}
?>