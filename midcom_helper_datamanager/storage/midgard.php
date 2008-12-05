<?php
/**
 * @package midcom_helper_datamanager
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Datamanager storage implementation: Pure Midgard object.
 *
 * This class is aimed to encapsulate storage to regular Midgard objects.
 *
 * @package midcom_helper_datamanager
 */
class midcom_helper_datamanager_storage_midgard extends midcom_helper_datamanager_storage
{
    /**
     * Optional callback that will be used if the storage variant
     * "callback" has been called.
     * @var object
     */
    private $callback = null;

    /**
     * Start up the storage manager and bind it to a given MidgardObject.
     * The passed object must be a MidCOM DBA object, otherwise the system bails with
     * generate_error. In this case, no automatic conversion is done, as this would
     * destroy the reference.
     *
     * @param midcom_helper_datamanagerschema &$schema The data schema to use for processing.
     * @param MidCOMDBAObject &$object A reference to the DBA object to user for Data I/O.
     */
    public function __construct(&$schema, &$object)
    {
        parent::__construct($schema);

        $this->object =& $object;
    }

    /**
     * Stores given value to field based on schema
     *
     * @param string $name name of field
     * @param mixed $data data to store
     * @see on_load_data()
     */
    public function on_store_data($name, $data)
    {
        // TODO: rethink
        switch ($this->schema->fields[$name]['storage']['location'])
        {
            case 'parameter':
                $this->object->parameter
                (
                    $this->schema->fields[$name]['storage']['domain'],
                    $name,
                    $data
                );
                break;

            case 'configuration':
                $this->object->parameter
                (
                    $this->schema->fields[$name]['storage']['domain'],
                    $this->schema->fields[$name]['storage']['name'],
                    $data
                );
                break;

            // TODO: allow the have different field name than metadata property name
            case 'metadata':
                if (!property_exists($this->object->metadata, $name)) 
                {
                    $this->object->parameter
                    (
                        'midcom_helper_metadata', 
                        $name,
                        $data
                    );
                }
                else
                {
                    $this->object->metadata->$name = $data;
                }
                break;

            default:
                $fieldname = $this->schema->fields[$name]['storage']['location'];
                if (!property_exists($this->object, $fieldname)) 
                {
                    throw new midcom_helper_datamanager_exception_storage("Missing $fieldname field in object: " . get_class($this->object));
                }
                $this->object->$fieldname = $data;
                break;
        }
    }


    /**
     * Loads database value of given field based on schema
     *
     * @param string $name name of field
     * @see on_store_data()
     * @return mixed data
     */
    protected function on_load_data($name)
    {
        // TODO: rethink
        switch ($this->schema->fields[$name]['storage']['location'])
        {
            case 'parameter':
                return $this->object->parameter
                (
                    $this->schema->fields[$name]['storage']['domain'],
                    $name
                );

            case 'configuration':
                return $this->object->parameter
                (
                    $this->schema->fields[$name]['storage']['domain'],
                    $this->schema->fields[$name]['storage']['name']
                );

            case 'metadata':
                if (!property_exists($this->object->metadata, $name))
                {
                    return $this->object->parameter
                    (
                        'midcom_helper_metadata', 
                        $name
                    );
                }
                else
                {
                    return $this->object->metadata->$name;
                }

            default:
                $fieldname = $this->schema->fields[$name]['storage']['location'];
                return $this->object->$fieldname;
        }
    }

    protected function on_update_object()
    {
        return $this->object->update();
    }
}

?>