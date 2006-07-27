<?php
/**
 * @package midcom.helper.datamanager2
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Datamanger 2 Data storage implementation: Pure Midgard object.
 *
 * This class is aimed to encaspulate storage to regular Midgard objects.
 *
 * @package midcom.helper.datamanager2
 */
class midcom_helper_datamanager2_storage_midgard extends midcom_helper_datamanager2_storage
{

    /**
     * Optional callback that will be used if the storage variant
     * "callback" has been called.
     * @var object
     * @access private
     */
    var $_callback = null;

    /**
     * Start up the storage manager and bind it to a given MidgardObject.
     * The passed object must be a MidCOM DBA object, otherwise the system bails with
     * generate_error. In this case, no automatic conversion is done, as this would
     * destroy the reference.
     *
     * @param midcom_helper_datamanager2_schema $schema The data schema to use for processing.
     * @param MidCOMDBAObject $object A reference to the DBA object to user for Data I/O.
     */
    function midcom_helper_datamanager2_storage_midgard(&$schema, &$object)
    {
        parent::midcom_helper_datamanager2_storage($schema);
        if (! $_MIDCOM->dbclassloader->is_mgdschema_object($object))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_print_r('Object passed:', $object);
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'The midgard storage backend requires a MidCOM DBA object.');
            // This will exit.
        }
        $this->object =& $object;
    }

    function _on_store_data($name, $data)
    {
        switch ($this->_schema->fields[$name]['storage']['location'])
        {
            case 'parameter':
                $this->object->set_parameter
                (
                    $this->_schema->fields[$name]['storage']['domain'],
                    $name,
                    $data
                );
                break;

            case 'configuration':
                $this->object->set_parameter
                (
                    $this->_schema->fields[$name]['storage']['domain'],
                    $this->_schema->fields[$name]['storage']['name'],
                    $data
                );
                break;

            case 'metadata':
                $fieldname = $this->_schema->fields[$name]['storage']['field'];
                $this->object->metadata->$fieldname = $data;
                break;

            default:
                $fieldname = $this->_schema->fields[$name]['storage']['location'];
                $this->object->$fieldname = $data;
                break;
        }
    }


    function _on_load_data($name)
    {
        switch ($this->_schema->fields[$name]['storage']['location'])
        {
            case 'parameter':
                return $this->object->get_parameter
                (
                    $this->_schema->fields[$name]['storage']['domain'],
                    $name
                );

            case 'configuration':
                return $this->object->get_parameter
                (
                    $this->_schema->fields[$name]['storage']['domain'],
                    $this->_schema->fields[$name]['storage']['name']
                );

            case 'metadata':
                $fieldname = $this->_schema->fields[$name]['storage']['field'];
                return $this->object->metadata->$fieldname;
                break;

            default:
                $fieldname = $this->_schema->fields[$name]['storage']['location'];
                return $this->object->$fieldname;
        }
    }

    function _on_update_object()
    {
        return $this->object->update();
    }
}

?>