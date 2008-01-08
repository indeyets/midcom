<?php
/**
 * @package midcom.helper.datamanager2
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Datamanager 2 Data Type base class.
 *
 * As with all subclasses, the actual initialization is done in the initialize() function,
 * not in the constructor, to allow for error handling.
 *
 * <b>Type configuration:</b>
 *
 * - Now uses class members, which should use initializers (var $name = 'default_value';)
 *   for configuration defaults.
 * - The schema configuration ('type_config') is merged using the semantics
 *   $type->$key = $value;
 *
 * @package midcom.helper.datamanager2
 */
class midcom_helper_datamanager2_type extends midcom_baseclasses_components_purecode
{
    /**
     * Set this to true during one of the startup callbacks if you need to have the
     * datastorage (de)serialized automatically during I/O operations.
     *
     * @var boolean
     */
    var $serialized_storage = false;

    /**
     * This field contains the reason for the failed validation. The string can be safely
     * assumed to be localized, and is only valid if a validation has failed previously.
     * This field will be cleared prior to a new validation attempt. You may use simple
     * inline HTML in these errors.
     *
     * @var string
     */
    var $validation_error = '';

    /**
     * This variable contains configuration data which is not directly related to the
     * operation of the type, but required for the operation of external tools like the
     * storage manager. The type should never touch this variable, which is controlled
     * by a corresponding getter/setter pair.
     *
     * @var Array
     * @access private
     * @see set_external_config()
     * @see get_external_config()
     */
    var $_external_config = Array();

    /**
     * The name field holds the name of the field the datatype is encapsulating. This
     * maps to the schema's field name. You should never have to change them.
     *
     * @var string
     */
    var $name = '';

    /**
     * A reference to the storage object that this type is using. Use this for attachment
     * management. The variable may be null until actual processing starts. It may also
     * change during the lifetime of a type. You should therefore be careful.
     *
     * @var midcom_helper_datamanager2_storage
     * @access protected
     */
    var $storage = null;

    /**
     * Constructor.
     *
     * Nothing fancy, the actual startup work is done by the initialize call.
     */
    function midcom_helper_datamanager2_type()
    {
        $this->_component = 'midcom.helper.datamanager2';
        parent::midcom_baseclasses_components_purecode();
    }

    /**
     * Initializes and configures the type.
     *
     * @param string $name The name of the field to which this type is bound.
     * @param Array $config The configuration data which should be used to customize the type.
     * @param midcom_helper_datamanager2_storage $storage A reference to the storage object to use.
     * @return boolean Indicating success. If this is false, the type will be unusable.
     */
    function initialize($name, $config, &$storage)
    {
        $this->name = $name;
        $this->set_storage($storage);

        // Call the event handler for configuration in case we have some defaults that cannot
        // be covered by the class initializers.
        $this->_on_configuring($config);

        // Assign the configuration values.
        foreach ($config as $key => $value)
        {
            $this->$key = $value;
        }

        if (! $this->_on_initialize())
        {
            return false;
        }

        return true;
    }

    /**
     * This function, is called  before the configuration keys are merged into the types
     * configuration.
     *
     * @param Array $config The configuration passed to the type.
     */
    function _on_configuring($config) {}

    /**
     * Small helper which sets the current storage object to a new one. The
     * object is used by-reference.
     *
     * @var midcom_helper_datamanager2_storage $storage A reference to the storage object to use.
     */
    function set_storage(&$storage)
    {
        $this->storage =& $storage;
    }

    /**
     * This event handler is called after construction, so passing references to $this to the
     * outside is safe at this point.
     *
     * @return boolean Indicating success, false will abort the type construction sequence.
     * @access protected
     */
    function _on_initialize()
    {
        return true;
    }

    /**
     * Converts from storage format to "operational" format, which might include
     * more information then the pure storage format. Depending on the $serialized_storage member,
     * the framework will automatically deal with deserializaiton of the information.
     *
     * This function must be overwritten.
     *
     * @param mixed $source The storage data structure.
     */
    function convert_from_storage ($source)
    {
        die ('The function ' . __CLASS__ . '::' . __FUNCTION__ . ' must be implemented in subclasses.');
    }

    /**
     * Converts from "operational" format to from storage format. Depending on the $serialized_storage member,
     * the framework will automatically deal with deserializaiton of the information.
     *
     * This function must be overwritten.
     *
     * @return mixed The data to store into the object, or null on failure.
     */
    function convert_to_storage()
    {
        die ('The function ' . __CLASS__ . '::' . __FUNCTION__ . ' must be implemented in subclasses.');
    }

    /**
     * Constructs the object based on its CSV representation (which is already decoded in terms
     * of escaping.)
     *
     * This function must be overwritten.
     *
     * @param string $source The CSV representation that has to be parsed.
     */
    function convert_from_csv ($source)
    {
        die ('The function ' . __CLASS__ . '::' . __FUNCTION__ . ' must be implemented in subclasses.');
    }

    /**
     * Transforms the current object's state into a CSV string representation. Escaping
     * and other encoding is done by the caller, you just return the string.
     *
     * This function must be overwritten.
     *
     * @return mixed The data to store into the object, or null on failure.
     */
    function convert_to_csv()
    {
        die ('The function ' . __CLASS__ . '::' . __FUNCTION__ . ' must be implemented in subclasses.');
    }

    /**
     * Transforms the current object's state into HTML representation. This is used for displaying
     * type contents in an automatic fashion.
     *
     * This function must be overwritten.
     *
     * @return mixed The rendered content.
     */
    function convert_to_html()
    {
        die ('The function ' . __CLASS__ . '::' . __FUNCTION__ . ' must be implemented in subclasses.');
    }

    /**
     * Main validation interface, currently only calls the main type callback, but this
     * can be extended later by a configurable callback into the component.
     *
     * @return boolean Indicating value validity.
     */
    function validate()
    {
        $this->validation_error = '';
        return $this->_on_validate();
    }

    /**
     * Type-specific validation callback, this is executed before any custom validation
     * rules which apply through the customization interface.
     *
     * In case validation fails, you should assign an (already translated) error message
     * to the validation_error public member.
     *
     * @access protected
     * @return boolean Indicating value validity.
     */
    function _on_validate()
    {
        return true;
    }

    /**
     * Gets an external configuration option referenced by its key. Besides other parts
     * in the datamanager framework, nobody should ever have to touch this information.
     *
     * @param string $key The key by which this configuration option is referenced.
     * @return mixed The configuration value, which is null if the key wasn't found.
     */
    function get_external_config($key)
    {
        if (! array_key_exists($key, $this->_external_config))
        {
            return null;
        }
        return $this->_external_config[$key];
    }

    /**
     * Sets an external configuration option. Besides other parts
     * in the datamanager framework, nobody should ever have to touch this information.
     *
     * @param string $key The key by which this configuration option is referenced.
     * @param mixed $value The configuration value.
     */
    function set_external_config($key, $value)
    {
        $this->_external_config[$key] = $value;
    }

    /**
     * Checks whether the current user has the given privilege on the storage backend.
     * The storage backend is resposible for the actual execution of this operation,
     * so this is merely a shortcut.
     *
     * @param string $privilege The privilege to check against.
     * @return boolean true if the user has the permission, false otherwise.
     */
    function can_do($privilege)
    {
        return $this->storage->can_do($privilege);
    }
}

?>