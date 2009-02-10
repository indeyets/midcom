<?php
/**
 * @package midcom_helper_datamanager
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Datamanager Data Type interface.
 *
 *
 * @package midcom_helper_datamanager
 */
interface midcom_helper_datamanager_type
{
    /**
     * Initializes and configures the type.
     *
     * @see midcom_helper_datamanager_type_baseclass::__construct
     */
    public function initialize($name, $config, &$storage);
    
    /**
     * Small helper which sets the current storage object to a new one. The
     * object is used by-reference.
     *
     * @see midcom_helper_datamanager_type_baseclass::set_storage
     */
    public function set_storage(&$storage);
    
    /**
     * Converts from storage format to "operational" format, which might include
     * more information then the pure storage format. Depending on the $serialized_storage member,
     * the framework will automatically deal with deserializaiton of the information.
     *
     * This function must be overwritten.
     *
     * @see midcom_helper_datamanager_type_baseclass::convert_from_storage
     */
    function convert_from_storage($source);
    
    /**
     * Converts from "operational" format to from storage format. Depending on the $serialized_storage member,
     * the framework will automatically deal with deserializaiton of the information.
     *
     * This function must be overwritten.
     *
     * @see midcom_helper_datamanager_type_baseclass::convert_to_storage
     */
    function convert_to_storage();
    
    /**
     * Main validation interface, currently only calls the main type callback, but this
     * can be extended later by a configurable callback into the component.
     *
     * @see midcom_helper_datamanager_type_baseclass::validate
     */
    function validate();

    /**
     * Checks whether the current user has the given privilege on the storage backend.
     * The storage backend is resposible for the actual execution of this operation,
     * so this is merely a shortcut.
     *
     * @see midcom_helper_datamanager_type_baseclass::can_do
     */
    function can_do($privilege);
    
}

/**
 * Datamanager Data Type base class.
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
 * @package midcom_helper_datamanager
 */
class midcom_helper_datamanager_type_baseclass implements midcom_helper_datamanager_type
{

    /**
     * The name field holds the name of the field the datatype is encapsulating. This
     * maps to the schema's field name. You should never have to change them.
     *
     * @var string
     */
    public $name = '';

    /**
     * A reference to the storage object that this type is using. Use this for attachment
     * management. The variable may be null until actual processing starts. It may also
     * change during the lifetime of a type. You should therefore be careful.
     *
     * @var midcom_helper_datamanager_storage
     */
    protected $storage = null;

    /**
     * This field contains the reason for the failed validation. The string can be safely
     * assumed to be localized, and is only valid if a validation has failed previously.
     * This field will be cleared prior to a new validation attempt. You may use simple
     * inline HTML in these errors.
     *
     * @var string
     */
    public $validation_error = '';
    
    public $serialized_storage;

    /**
     * Initializes and configures the type.
     *
     * @param string $name The name of the field to which this type is bound.
     * @param Array $config The configuration data which should be used to customize the type.
     * @param midcom_helper_datamanager_storage $storage A reference to the storage object to use.
     * @return boolean Indicating success. If this is false, the type will be unusable.
     */
    public function initialize($name, $config, &$storage)
    {
        $this->name = $name;
        $this->set_storage($storage);

        // Call the event handler for configuration in case we have some defaults that cannot
        // be covered by the class initializers.
        $this->on_configuring($config);

        // Assign the configuration values.
        foreach ($config as $key => $value)
        {
            $this->$key = $value;
        }

        if (! $this->on_initialize())
        {
            return false;
        }

        return true;
    }
    
    /**
     * Small helper which sets the current storage object to a new one. The
     * object is used by-reference.
     *
     * @var midcom_helper_datamanager_storage $storage A reference to the storage object to use.
     */
    public function set_storage(&$storage)
    {
        $this->storage =& $storage;
    }
    
    /**
     * This function, is called  before the configuration keys are merged into the types
     * configuration.
     *
     * @param Array $config The configuration passed to the type.
     */
    protected function on_configuring($config) {}
    
    
    /**
     * This event handler is called after construction, so passing references to $this to the
     * outside is safe at this point.
     *
     * @return boolean Indicating success, false will abort the type construction sequence.
     */
    protected function on_initialize()
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
    public function convert_from_storage($source)
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
    public function convert_to_storage()
    {
        die ('The function ' . __CLASS__ . '::' . __FUNCTION__ . ' must be implemented in subclasses.');
    }
    
    public function convert_to_raw()
    {
        return $this->convert_to_storage();
    }

    /**
     * Main validation interface, currently only calls the main type callback, but this
     * can be extended later by a configurable callback into the component.
     *
     * @return boolean Indicating value validity.
     */
    public function validate()
    {
        $this->validation_error = '';
        return $this->on_validate();
    }

    /**
     * Type-specific validation callback, this is executed before any custom validation
     * rules which apply through the customization interface.
     *
     * In case validation fails, you should assign an (already translated) error message
     * to the validation_error public member.
     *
     * @return boolean Indicating value validity.
     */
    protected function on_validate()
    {
        return true;
    }
    
    /**
     * Checks whether the current user has the given privilege on the storage backend.
     * The storage backend is resposible for the actual execution of this operation,
     * so this is merely a shortcut.
     *
     * @param string $privilege The privilege to check against.
     * @return boolean true if the user has the permission, false otherwise.
     */
    public function can_do($privilege)
    {
        return $this->storage->can_do($privilege);
    }
    
    /**
     * Magic getters for the contents of the field in a given format
     */
    public function __get($key)
    {
        switch ($key)
        {
            case 'as_html':
                return $this->convert_to_html();
            case 'as_csv':
                if (!method_exists($this, 'convert_to_csv'))
                {
                    throw new midcom_helper_datamanager_exception_type('csv conversion not supported');
                }
                return $this->convert_to_csv();
            case 'as_raw':
                return $this->convert_to_raw();
        }
    }
    
    /**
     * Magic isset for the contents of the field in a given format
     */
    public function __isset($key)
    {
        switch ($key)
        {
            case 'as_html':
            case 'as_csv':
            case 'as_raw':
                return true;
            default:
                return false;
        }
    }
}

?>