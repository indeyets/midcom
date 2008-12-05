<?php
/**
 * @package midcom_helper_datamanager
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Datamanager storage base class.
 *
 * It implements the basic interface required for data storage operations. Naturally,
 * only the construction of storage backends is suspect to class specific code, the actual
 * operation should be completely transparent across all storage implementations.
 *
 * See the individual subclasses for details about their operation.
 *
 * @package midcom_helper_datamanager
 */
class midcom_helper_datamanager_storage extends midcom_core_component_baseclass
{
    /**
     * A reference to the data schema used for processing.
     *
     * @var midcom_helper_datamanager_schema
     * @access protected
     */
    private var $_schema = null;

    /**
     * This is a reference the storage object used by the subclass implementation.
     * Types <i>may use this reference only for attachment operations and parameter
     * operations related to attachment operations.</i>
     *
     * It can be safely considered that a MidCOM DBA object is available here if the
     * reference is non-null.
     *
     * Since this member is not necessarily populated in all cases, the base API
     * provides callers with a create_temporary_object helper function: It will put
     * a temporary object into $object, so that attachment operations can still
     * be done. The storage object user must take care of the information stored
     * on that object.
     *
     * @var MidCOMDBAObject
     */
    public var $object = null;

    /**
     * Creates the storage interface class, and initializes it to a given data schema.
     * Specific storage implementation subclasses will need to expand this constructor
     * to take care of linking to the right storage object, where applicable.
     *
     * @param midcom_helper_datamanager_schema &$schema The data schema to use for processing.
     */
    public function __construct(&$schema)
    {
        $this->_schema =& $schema;
    }

    /**
     * This function will populate the $object member with a temporary object obtained
     * by the MidCOM temporary object service.
     *
     * The code using this storage instance <b>must</b> take care of transitting this
     * temporary object into future sessions (which means switching to another storage
     * backend at that point).
     *
     * If the storage object is already populated, the method will exit silently.
     *
     * @see midcom_services_tmp
     * @see midcom_core_temporary_object
     */
    public function create_temporary_object()
    {
        if ($this->object === null)
        {
            $this->object = $_MIDCOM->tmp->create_object();
        }
    }
    
    /**
     * Stores a set of types to the configured storage object. This is done
     * by subclass implementations, where this function serves as a request
     * switch.
     *
     * Any types defined in the schema but not found in the passed type listing
     * are ignored unless they are flagged as required, in which case
     * generate_error is called.
     *
     * @param Array &$types A reference to an array of types matching the schema definition.
     * @return boolean Indicating success.
     */
    public function store(&$types)
    {
        foreach ($this->_schema->fields as $name => $type_definition)
        {
            if (! array_key_exists($name, $types))
            {
                if ($type_definition['required'] == true)
                {
                    throw new Exception(
                        "Failed to process the type array for the schema {$this->_schema->name}: " . 
                        "The type for the required field {$name} was not found."
                    );
                    // This will exit.
                }
                else
                {
                    continue;
                }
            }

            // Convert_to_storage is called always, the event handler can be used to manage
            // non-storage-backend driven storage operations as well (mainly for the blob type)
            $data = $types[$name]->convert_to_storage();
            if ($type_definition['storage']['location'] !== null)
            {
                if ($types[$name]->serialized_storage)
                {
                    $data = serialize($data);
                }
                $this->on_store_data($name, $data);
            }
        }

        // Update the storage object last
        if (! $this->on_update_object())
        {
            return false;
        }

        return true;
    }

    /**
     * Override this function to implement the storage method for your backend.
     * It has to store the given data to the schema field identified by the given
     * name.
     *
     * @param string $name The name of the field to save to.
     * @param mixed $data The data to save to.
     */
    private function on_store_data($name, $data)
    {
        die ('The function ' . __CLASS__ . '::' . __FUNCTION__ . ' must be implemented in subclasses.');
    }

    /**
     * Loads a set of types to the configured storage object. This is done
     * by subclass implementations, where this function serves as a request
     * switch.
     *
     * Any types defined in the schema but not found in the passed type listing
     * are ignored unless they are flagged as required, in which case
     * generate_error is called.
     *
     * @param Array &$types A reference to an array of types matching the schema definition.
     */
    public function load(&$types)
    {
        foreach ($this->_schema->fields as $name => $type_definition)
        {
            if (! array_key_exists($name, $types))
            {
                if ($type_definition['required'] == true)
                {
                    throw new Exception(
                        "Failed to process the type array for the schema {$this->_schema->name}: " . 
                        "The type for the required field {$name} was not found."
                    );
                    // This will exit.
                }
                else
                {
                    continue;
                }
            }
            if ($type_definition['storage']['location'] !== null)
            {
                $data = $this->on_load_data($name);
                if ($types[$name]->serialized_storage)
                {
                    // Hide unserialization errors, but log them.
                    $data = @unserialize($data);
                }
            }
            else
            {
                $data = null;
            }

            // Convert_from_storage is called always, the event handler can be used to manage
            // non-storage-backend driven storage operations as well (mainly for the blob type)
            $types[$name]->convert_from_storage($data);
        }
    }

    /**
     * Override this function to implement the storage method for your backend.
     * It has to store the given data to the schema field identified by the given
     * name.
     *
     * @param string $name The name of the field to load from.
     * @return mixed $data The data which has been loaded.
     */
    private function on_load_data($name)
    {
        die ('The function ' . __CLASS__ . '::' . __FUNCTION__ . ' must be implemented in subclasses.');
    }

    /**
     * This callback is invoked once the storage object has been completely updated with
     * the information from all types. You need to store it to the database at this point.
     *
     * @return boolean Indicating success.
     */
    private function on_update_object()
    {
        die ('The function ' . __CLASS__ . '::' . __FUNCTION__ . ' must be implemented in subclasses.');
    }

    /**
     * Checks whether the current user has the given privilege on the storage backend.
     * If there is no valid storage backend, a can_user_do is performed. Subclasses
     * may overwrite this method to incorporate for creation mode stuff.
     *
     * @param string $privilege The privilege to check against.
     * @return boolean true if the user has the permission, false otherwise.
     */
    public function can_do($privilege)
    {
        if ($this->object === null)
        {
            return $_MIDCOM->auth->can_user_do($privilege);
        }
        else
        {
            return $_MIDCOM->auth->can_do($privilege, $this->object);
        }
    }

}

?>