<?php
/**
 * @package midcom
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id:configuration.php 3765 2006-07-31 08:51:39 +0000 (Mon, 31 Jul 2006) tarjei $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This class is designed to ease MidCOM Configuration management.
 *
 * Basically it
 * supports key/value pairs of data, which can be retrieved out of Midgard
 * Parameters. In this case it would make the key/values a string/string pair with
 * a length limit of 255 characters. Since the current implementation only supports
 * read-access to the configuration data, this is a neglible fact, in reality it
 * supports all valid PHP data types as key or data values, as long it is allowed
 * to use the keys as array index elements.
 *
 * This class is designed to manage parameter like key/value configuration data.
 * The class makes no assumption about the value type of keys or values, any valid
 * PHP data type is allowed. Two different sets of configuration options are stored
 * within the class, the "global" and the "local" configuration.
 *
 * The global configuration must include all possible configuration parameters with
 * their default values. These data is fixed and cannot be changed after object
 * instantination. Aimed specifically at MidCOM is the second set of configuration
 * data, the "local" parameters. It gives you a way of explicitly overwrite a part
 * of the configuration data with localized values. This customization data can be
 * overwritten at wish by deliberatly resetting it to the defaults or by importing
 * a new one over the existing local configuration.
 *
 * Configuration data can be delivered in two ways: The easiest way is using a
 * acciociative array that will be used as configuration. Alternativly you can
 * specify both a MidgardObject and a MidCOM Path which is used to fetch
 * configuration data.
 *
 * Any configuration key in the local configuration, which is not present in the
 * global "template", will be logged as a warning into the MidCOM log. This should
 * normally not happen. Originally, this case threw a critical error, but that
 * made upgrading configurations quite difficult.
 *
 * @package midcom
 */
class midcom_helper_configuration
{

    /**
     * Globally assigned configuration data.
     *
     * @var Array
     * @access private
     */
    var $_global;

    /**
     * Locally overriden configuration data.
     *
     * @var Array
     * @access private
     */
    var $_local;

    /**
     * Merged, current configuration state.
     *
     * @var Array
     * @access private
     */
    var $_merged;

    /**
     * The constructor initializes the global configuration.
     *
     * Two sources can be specified:
     *
     * First, if passed a single associative array to the constructor,
     * it will use its contents as global configuration.
     *
     * Alternativly you can specify any Midgard object and a parameter
     * domain. It will then use the contents of this domain as global
     * configuration.
     *
     * @param mixed $param1        Either an associative array or a reference to a Midgard object.
     * @param mixed $param2        Either null or the name of a Parameter domain.
     */
    function midcom_helper_configuration($param1 = null, $param2 = null)
    {
        if (! is_null($param2))
        {
            $object = &$param1;
            $path = &$param2;
            $this->_local = array();
            $this->_store_from_object ($object, $path, true);
        }
        else if (! is_null($param1))
        {
            $global_params = &$param1;
            $this->_global = $global_params;
            $this->_local = array();
            $this->_merged = $global_params;
        }
        else
        {
            $GLOBALS['midcom']->generate_error(MIDCOM_ERRCRIT, 'midcom_helper_configuration: Default constructor not allowed.');
        }
    }

    /**
     * This function will fetch the configuration data stored in the parameter domain
     * $path of the Midgard Object $object.
     *
     * The flag $global controls whether the
     * global or the local configuration should be updated. No control whether an
     * update of the global data is allowed is done here, the caller has to do this.
     * This function will update the config data cache array. If it stores global
     * configuration data it will automatically erase the local configuration data.
     *
     * Any error such as invalid configuration data will trigger an MidCOM error.
     *
     * @param MidgardObject $object        The object from which to retrieve the configuration.
     * @param string        $path        The Parameter domain to query.
     * @param bool            $global        Set to true to replace the global configuration.
     * @access private
     */
    function _store_from_object($object, $path, $global = false)
    {
        $array = array();

        // Cast to DBA type.
        if (! $_MIDCOM->dbclassloader->is_midcom_db_object($object))
        {
            $object = $_MIDCOM->dbfactory->convert_midgard_to_midcom($object);
        }

        $array = $object->list_parameters($path);
        
        /*
        if ($params) {
            while ($params->fetch())
            {
                $array[$params->name] = $params->value;
            }
        }
        */


        if ($global)
        {
            $this->_global = $array;
            $this->_local = array();
            $this->_merged = $array;
            debug_pop();
        }
        $this->_check_local_array($array);
        $this->_local = $array;
        $this->_update_cache();
    }

    /**
     * This method will merge the local and the global configuration arrays into the
     * cache array.
     *
     * @access private
     */
    function _update_cache()
    {
        $this->_merged = $this->_global;
        if ( !empty($this->_local) )
        {
            foreach ($this->_local as $key => $value)
            {
                $this->_merged[$key] = $value;
            }            
        }
    }

    /**
     * Check local data array for validity
     *
     * Since the local array must only include configuration parameters that are
     * included in the global configuration, this function is used to check a local
     * array against the current global configuration. true/false is returned
     * accordingly.
     *
     * On any inconsistency a WARNING level message is logged, but the error
     * is silently ignored as of 2.4.0.
     *
     * @access private
     */
    function _check_local_array($array)
    {
        if ( !empty($array) )
        {
            foreach ($array as $key => $value)
            {
                if (! array_key_exists($key, $this->_global))
                {
                    debug_push_class(__CLASS__, __FUNCTION__);
                    debug_add("The key {$key} is not present in the global configuration array.", MIDCOM_LOG_INFO);
                    debug_print_r("Current global configuration:", $this->_global);
                    debug_pop();
                }
            }            
        }
    }

    /**
     * The method store will write the parameters in $params into the local
     * configuration.
     *
     * If $reset is set, the local configuration will be cleared before
     * the new set is imported, if not, the new data is merged with the old local
     * configuration, overwriting duplicates. During import each configuration key will
     * be checked against the global configuration values. If an unkown value is found,
     * import will be aborted and no changes to the configuration is done.
     *
     * After import the cache array will be updated, reset is done by reset_local.
     *
     * @param Array    $params        The new local parameters
     * @param bool    $reset        If set to true, the current local configuration will be discarded first.
     * @return bool                Indicating success.
     * @see midcom_helper_configuration::reset_local()
     */
    function store($params, $reset = true)
    {
        $this->_check_local_array($params);
        if ($reset == true)
        {
            $this->reset_local();
        }
        foreach ($params as $key => $value)
        {
            $this->_local[$key] = $value;
        }
        $this->_update_cache();
        return true;
    }

    /**
     * Import data from a Midgard object.
     *
     * To import configuration data from an Midgard Object, use this method. As in the
     * respecitve constructor it will retrieve the configuration data in the parameter
     * domain $path of $object. Unlike the constructor this function will store the
     * data in the local configuration.
     *
     * @param MidgardObject    $object    The object from which to import data.
     * @param string        $path    The parameter domain to query.
     * @return bool            Indicating success
     */
    function store_from_object($object, $path)
    {
        return $this->_store_from_object ($object, $path, false);
    }

    /**
     * Clear the local configuration data, effectively reverting to the global
     * default.
     */
    function reset_local()
    {
        $this->_local = array();
        $this->_merged = $this->_global;
    }

    /**
     * Retrieve a configuration key
     *
     * If $key exists in the configuration data, its value is returned to the caller.
     * If the value does not exist, the boolean value false will be returned. Be aware
     * that this is not always good for error checking, since "FALSE" is a perfectly good
     * value in the configuration data. Do errorchecking with the function exists (see
     * below).
     *
     * @param mixed    $key    The configuration key to query.
     * @return mixed        Its value of FALSE, if the key doesn't exist.
     * @see midcom_helper_configuration::exists()
     */
    function get($key)
    {
        if ($this->exists($key))
        {
            return $this->_merged[$key];
        }
        else
        {
            return false;
        }
    }

    /**
     * Retrieve a copy the complete configuration array.
     *
     * @return Array    The complete current configuration.
     */
    function get_all()
    {
        // Copy-By-Value is PHPs default, so don't bother copying it by hand...
        return $this->_merged;
    }

    /**
     * Checks for the existence of a configuration key.
     *
     * @param string    $key    The configuration key to check for.
     * @return bool                True, if the key is available, false otherwise.
     */
    function exists($key)
    {
        return array_key_exists ($key, $this->_merged);
    }

}

?>