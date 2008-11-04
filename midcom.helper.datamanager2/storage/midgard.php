<?php
/**
 * @package midcom.helper.datamanager2
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Datamanager 2 Data storage implementation: Pure Midgard object.
 *
 * This class is aimed to encapsulate storage to regular Midgard objects.
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
     * @param midcom_helper_datamanager2_schema &$schema The data schema to use for processing.
     * @param MidCOMDBAObject &$object A reference to the DBA object to user for Data I/O.
     */
    function __construct(&$schema, &$object)
    {
        parent::__construct($schema);
        if (! $_MIDCOM->dbclassloader->is_mgdschema_object($object))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_print_r('Object passed:', $object);
            debug_pop();
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'The midgard storage backend requires a MidCOM DBA object.');
            // This will exit.
        }
        $this->object =& $object;
    }

    function _on_store_data($name, $data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_print_r("Store to field '{$name}' data", $data);
        debug_pop();
        
        switch ($this->_schema->fields[$name]['storage']['location'])
        {
            case 'parameter':
                if (   array_key_exists('multilang', $this->_schema->fields[$name]['storage'])
                    && $this->_schema->fields[$name]['storage']['multilang']
                    && $_MIDCOM->i18n->get_midgard_language() != 0)
                {
                    $this->object->set_parameter
                    (
                        $this->_schema->fields[$name]['storage']['domain'],
                        $name . '_' . $_MIDCOM->i18n->get_content_language(),
                        $data
                    );
                }
                else
                {
                    $this->object->set_parameter
                    (
                        $this->_schema->fields[$name]['storage']['domain'],
                        $name,
                        $data
                    );
                }
                break;

            case 'configuration':
                if (   array_key_exists('multilang', $this->_schema->fields[$name]['storage'])
                    && $this->_schema->fields[$name]['storage']['multilang']
                    && $_MIDCOM->i18n->get_midgard_language() != 0)
                {
                    $this->object->set_parameter
                    (
                        $this->_schema->fields[$name]['storage']['domain'],
                        $this->_schema->fields[$name]['storage']['name'] . '_' . $_MIDCOM->i18n->get_content_language(),
                        $data
                    );
                }
                else
                {
                    $this->object->set_parameter
                    (
                        $this->_schema->fields[$name]['storage']['domain'],
                        $this->_schema->fields[$name]['storage']['name'],
                        $data
                    );
                }
                break;

            case 'metadata':
                if (!property_exists($this->object->__object->metadata, $name)) 
                {
                    throw new Exception("Missing {$name} field in object: " . get_class($this->object->metadata));
                }
                $this->object->metadata->$name = $data;
                break;

            default:
                $fieldname = $this->_schema->fields[$name]['storage']['location'];
                if (   !property_exists($this->object, $fieldname)
                    && !property_exists($this->object->__object, $fieldname)) 
                {
                    throw new Exception("Missing {$fieldname} field in object: " . get_class($this->object));
                }
                $this->object->$fieldname = $data;
                break;
        }
    }


    function _on_load_data($name)
    {
        // Cache parameter queries so we get them once
        static $loaded_domains = array();

        switch ($this->_schema->fields[$name]['storage']['location'])
        {
            case 'parameter':
                if (!isset($loaded_domains[$this->_schema->fields[$name]['storage']['domain']]))
                {
                    // Run the list here so all parameters of the domain go to cache
                    $loaded_domains[$this->_schema->fields[$name]['storage']['domain']] = $this->object->list_parameters($this->_schema->fields[$name]['storage']['domain']);
                }

                if (   array_key_exists('multilang', $this->_schema->fields[$name]['storage'])
                    && $this->_schema->fields[$name]['storage']['multilang']
                    && $_MIDCOM->i18n->get_midgard_language() != 0)
                {
                    // Try to get a translated parameter
                    $translated_value = $this->object->get_parameter
                    (
                        $this->_schema->fields[$name]['storage']['domain'],
                        $name . '_' . $_MIDCOM->i18n->get_content_language()
                    );
                    if ($translated_value)
                    {
                        return $translated_value;
                    }
                    // Otherwise fall back to the lang0 version
                }
                return $this->object->get_parameter
                (
                    $this->_schema->fields[$name]['storage']['domain'],
                    $name
                );

            case 'configuration':
                if (!isset($loaded_domains[$this->_schema->fields[$name]['storage']['domain']]))
                {
                    // Run the list here so all parameters of the domain go to cache
                    $loaded_domains[$this->_schema->fields[$name]['storage']['domain']] = $this->object->list_parameters($this->_schema->fields[$name]['storage']['domain']);
                }

                if (   array_key_exists('multilang', $this->_schema->fields[$name]['storage'])
                    && $this->_schema->fields[$name]['storage']['multilang']
                    && $_MIDCOM->i18n->get_midgard_language() != 0)
                {
                    // Try to get a translated parameter
                    $translated_value = $this->object->get_parameter
                    (
                        $this->_schema->fields[$name]['storage']['domain'],
                        $this->_schema->fields[$name]['storage']['name'] . '_' . $_MIDCOM->i18n->get_content_language()
                    );
                    if ($translated_value)
                    {
                        return $translated_value;
                    }
                    // Otherwise fall back to the lang0 version
                }

                return $this->object->get_parameter
                (
                    $this->_schema->fields[$name]['storage']['domain'],
                    $this->_schema->fields[$name]['storage']['name']
                );

            case 'metadata':
                return $this->object->get_metadata()->get($name);
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