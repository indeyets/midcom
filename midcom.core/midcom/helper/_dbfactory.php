<?php
/**
 * @package midcom
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id:_dbfactory.php 3765 2006-07-31 08:51:39 +0000 (Mon, 31 Jul 2006) tarjei $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This class contains various factory methods to retrieve objects from the database.
 * The only instance of this class you should ever use is available through
 * $midcom->dbfactory.
 *
 * @package midcom
 */
class midcom_helper__dbfactory extends midcom_baseclasses_core_object
{
    /**
     * Calls parent constructor only.
     */
    function __construct()
    {
        parent::__construct();
    }

    /**
     * Return DateTime object
     *
     * Usage example:
     *
     * $date = $_MIDCOM->dbfactory->from_core_date($object->date);
     * echo $date->format('Y-m-d');
     *
     * @param string $datetime datetime property as provided by Midgard core
     * @return DateTime The given datetime property as a PHP native DateTime object
     */
    function from_core_date($datetime)
    {
        // TODO: Once midgard-php maps datetime properties directly to DateTime we can just return the param here
        static $local_tz = null;
        if (is_null($local_tz))
        {
            // We need to use local time here as Midgard returns every datetime as UTC
            // TODO: Would be great to make the timezone configurable
            $local_tz = new DateTimeZone(date_default_timezone_get());
        }
        $midcom_datetime = new DateTime("{$datetime}+0000");
        $midcom_datetime->setTimeZone($local_tz);
        return $midcom_datetime;
    }

    /**
     * Return DateTime in format preferred by Midgard Core
     *
     * Usage example:
     *
     * $date = new DateTime('yesterday');
     * $qb->add_constraint('date', '>=', $_MIDCOM->dbfactory->to_core_date($date));
     *
     * @param DateTime Datetime property as a PHP native DateTime object
     * @return string The given datetime property as a UTC ISO date as preferred by Midgard core
     */
    function to_core_date($datetime)
    {
        // TODO: Once midgard-php maps datetime properties directly to DateTime we can just return the param here
        static $mgd_tz = null;
        if (is_null($mgd_tz))
        {
            // Midgard internally keeps everything in UTC
            $mgd_tz = new DateTimeZone('UTC');
        }
        $midgard_datetime = clone($datetime);
        $midgard_datetime->setTimeZone($mgd_tz);
        return $midgard_datetime->format(DateTime::ISO8601);
    }

    /**
     * This is a replacement for the original mgd_get_object_by_guid function, which takes
     * the MidCOM DBA system into account.
     *
     * @param string $guid The object GUID.
     * @return object A MidCOM DBA object if the set GUID is known, NULL on any error.
     */
    function get_object_by_guid($guid)
    {
        if (!mgd_is_guid($guid))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("The given GUID ({$guid}) is not valid ", MIDCOM_LOG_WARN);
            debug_pop();
            return null;
        }

        try
        {
            $tmp = midgard_object_class::get_object_by_guid($guid);
        }
        catch(midgard_error_exception $e)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("The Midgard core failed to resolve the GUID {$guid}: " . $e->getMessage(), MIDCOM_LOG_INFO);
            debug_pop();
            return null;
        }

        return $this->convert_midgard_to_midcom($tmp);
    }

    /**
     * This function will determine the correct type of midgard_collector that
     * has to be created. It will also call the _on_prepare_new_collector event handler.
     *
     * Since this is to be called statically, it will take a class name, not an instance
     * as argument.
     *
     * @param string $classname The name of the class for which you want to create a collector.
     * @param string $domain The domain property of the collector instance
     * @param mixed $value Value match for the collector instance
     * @return midcom_core_collector The initialized instance of the collector.
     * @see midcom_core_collector
     * @static
     */
    function new_collector($classname, $domain, $value)
    {
        $mc = new midcom_core_collector($classname, $domain, $value);
        $mc->initialize();
        return $mc;
    }

    /**
     * This function will determine the correct type of midgard_query_builder that
     * has to be created. It will also call the _on_prepare_new_query_builder event handler.
     *
     * Since this is to be called statically, it will take a class name, not an instance
     * as argument.
     *
     * @param string $classname The name of the class for which you want to create a query builder.
     * @return midcom_core_querybuilder The initialized instance of the query builder.
     * @see midcom_core_querybuilder
     * @static
     */
    function new_query_builder($classname)
    {
        $qb = new midcom_core_querybuilder($classname);
        $qb->initialize();
        return $qb;
    }

    /**
     * This function will execute the Querybuilder.
     *
     * @param midgard_query_builder $qb An instance of the Query builder obtained by the new_query_builder
     *     function of this class.
     * @return Array The result of the query builder. Note, that empty resultsets
     *     will return an empty array.
     * @see midcom_core_querybuilder::execute()
     */
    function exec_query_builder(&$qb)
    {
        return $qb->execute();
    }

    /**
     * Helper function, it takes a Legacy Midgard object (pre MgdSchema) and
     * converts it into one of the new MgdSchema based midcom_baseclasses_database_*
     * objects.
     *
     * It always returns a copy of the original object, even if it is already a MgdSchema object.
     *
     * If the conversion cannot be done for some reason, the function returns NULL and logs
     * an error.
     *
     * In case of MgdSchema objects we also ensure that the corresponding component has been
     * loaded.
     *
     * @param MidgardObject &$object Pre-MgdSchema Midgard Object
     * @return MgdSchemaObject One of the midcom_baseclasses_database_* MgdSchema objects or null on failure.
     */
    function convert_midgard_to_midcom (&$object)
    {
        if (! is_object($object))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_print_type("Cannot cast the object to a MgdSchema type, it is not an object, we got this type:",
                $object, MIDCOM_LOG_ERROR);
            debug_print_r("Object dump:", $object);
            debug_pop();
            return null;
        }
     
        if ($_MIDCOM->dbclassloader->is_mgdschema_object($object))
        {
            $classname = $_MIDCOM->dbclassloader->get_midcom_class_name_for_mgdschema_object($object);
            if (! $_MIDCOM->dbclassloader->load_mgdschema_class_handler($classname))
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("Failed to load the handling component for {$classname}, cannot convert.", MIDCOM_LOG_ERROR);
                debug_pop();
                return null;
            } 
            $result = new $classname($object);
        }
        else
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_print_type("Cannot cast the object to a MgdSchema type, it is not a regular Midgard object, we got this type:",
                $object, MIDCOM_LOG_ERROR);
            debug_print_r("Object dump:", $object);
            debug_pop();
            return null;
        }

        if ($result)
        {
            return $result;
        }
        else
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_print_type("Cannot cast the object to a MgdSchema type, construction of {$classname} for ID {$object->id} failed, we got this type:",
                $object, MIDCOM_LOG_ERROR);
            debug_print_r("Object dump:", $object);
            debug_pop();
            return null;
        }
    }

    /**
     * This is a helper function which determines the parent GUID for an existing
     * GUID according to the MidCOM content tree rules.
     *
     * It tries to look up the GUID in the memory cache, only if this fails, the regular
     * content getters are invoked.
     *
     * @param mixed $object Either a MidCOM DBA object instance, or a GUID string.
     * @param string $class class name of object if known (so we can use get_parent_guid_uncached_static and avoid instantiating full object)
     * @return string The parent GUID, or null, if this is a top level object.
     */
    function get_parent_guid($object, $class = null)
    {
        static $cached_parent_guids = array();
        
        if (is_object($object))
        {
            if (!property_exists($object, 'guid'))
            {
                $object_guid = null;
            }
            elseif (   isset($object->__guid)
                    && !$object->guid)
            {
                $object_guid = $object->__guid;
            }
            else
            {
                $object_guid = $object->guid;
            }
            $the_object =& $object;
        }
        else
        {
            $object_guid = $object;
            $the_object = null;
        }

        if (!mgd_is_guid($object_guid))
        {
            if ($the_object === null)
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                    'Tried to resolve an invalid GUID without an object being present. This cannot be done.');
                // This will exit.
            }
            $parent_guid = false;
        }
        else
        {
            if (array_key_exists($object_guid, $cached_parent_guids))
            {
                // We already got this either via query or memcache
                return $cached_parent_guids[$object_guid];
            }
            $parent_guid = $_MIDCOM->cache->memcache->lookup_parent_guid($object_guid);
        }

        if (!$parent_guid)
        {
            // No cache hit, retrieve guid and update the cache
            if ($class)
            {
                // Class defined, we can use the static method for fetching parent and avoiding full object instantiate
                $parent_guid = call_user_func(array($class, 'get_parent_guid_uncached_static'), $object_guid);
            }
            else
            {
                // class not defined, retrieve the full object by guid
                if ($the_object === null)
                {
                    $the_object = $this->get_object_by_guid($object_guid);
                    if (! is_object($the_object))
                    {
                        return null;
                    }
                }
                $parent_guid = $the_object->get_parent_guid_uncached();
            }
            if (is_object($parent_guid))
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add('Warning, get_parent_guid_uncached should not return an object. This feature is deprecated.',
                    MIDCOM_LOG_INFO);
                debug_pop();
                $parent_guid = $parent_guid->guid;
            }

            if (mgd_is_guid($object_guid))
            {
                $_MIDCOM->cache->memcache->update_parent_guid($object_guid, $parent_guid);
            }
        }

        // Remember this so we don't need to get it again
        $cached_parent_guids[$object_guid] = $parent_guid;

        if (!mgd_is_guid($parent_guid))
        {
            return null;
        }
        else
        {
            return $parent_guid;
        }
    }

    /**
     * Import object unserialized with midgard_replicator::unserialize()
     *
     * This method does ACL checks and triggers watchers etc.
     *
     * @param object $unserialized_object object gotten from midgard_replicator::unserialize()
     * @param boolean $use_force set use of force for the midcom_helper_replicator_import_object() call
     * @return boolean indicating success/failure
     * @todo refactor to smaller methods
     * @todo Add some magic to prevent importing of replication loops (see documentation/TODO for details about the potential problem)
     * @todo Verify support for the special cases of privilege and virtual_group
     * @todo Make sure older version is not imported over newer one (maybe configurable override ?)
     */
    function import(&$unserialized_object, $use_force = false)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        if (is_a($unserialized_object, 'midgard_blob'))
        {
            debug_add("You must use import_blob method to import BLOBs", MIDCOM_LOG_ERROR);
            debug_pop();
            mgd_set_errno(MGD_ERR_ERROR);
            return false;
        }
        // We need this helper (workaround Zend bug)
        if (!function_exists('midcom_helper_replicator_import_object'))
        {
            $_MIDCOM->componentloader->load('midcom.helper.replicator');
        }

        if (!$_MIDCOM->dbclassloader->is_mgdschema_object($unserialized_object))
        {
            debug_add("Unserialized object " . get_class($unserialized_object) . " is not recognized as supported MgdSchema class.", MIDCOM_LOG_ERROR);
            debug_pop();
            mgd_set_errno(MGD_ERR_ERROR);
            return false;
        }

        // Load the required component for DBA class
        $midcom_dba_classname = $_MIDCOM->dbclassloader->get_midcom_class_name_for_mgdschema_object($unserialized_object);
        if (! $_MIDCOM->dbclassloader->load_mgdschema_class_handler($midcom_dba_classname))
        {
            debug_add("Failed to load the handling component for {$midcom_dba_classname}, cannot import.", MIDCOM_LOG_ERROR);
            debug_pop();
            mgd_set_errno(MGD_ERR_ERROR);
            return false;
        }

        // Get an object for ACL checks, use existing one if possible
        $acl_object = new $midcom_dba_classname($unserialized_object->guid);
        if (   is_object($acl_object)
            && $acl_object->id)
        {
            if (!is_a($acl_object, get_class($unserialized_object)))
            {
                $acl_class = get_class($acl_object);
                $unserialized_class = get_class($unserialized_object);
                debug_add("The local object we got is not of compatible type ({$acl_class} vs {$unserialized_class}), this means duplicate GUID", MIDCOM_LOG_ERROR);
                mgd_set_errno(MGD_ERR_DUPLICATE);
                debug_pop();
                return false;
            }
            // Got an existing object
            $acl_object_in_db = true;
            $actual_object_in_db = true;
            /* PONDER: should we copy values from unserialized here as well ?? likely we should (think moving article)
            midcom_baseclasses_core_dbobject::cast_object($acl_object, $unserialized_object)
            */
        }
        else
        {
            $error_code = mgd_errno(); // switch is a loop, get the value only once this way
            switch ($error_code)
            {
                case MGD_ERR_ACCESS_DENIED:
                    $actual_object_in_db = true;
                    debug_add("Could not instaniate ACL object due to ACCESS_DENIED error, this means we can abort early", MIDCOM_LOG_ERROR);
                    debug_pop();
                    return false;
                    break;
                case MGD_ERR_OBJECT_DELETED:
                case MGD_ERR_OBJECT_PURGED:
                    $actual_object_in_db = true;
                    break;
                default:
                    $actual_object_in_db = false;
                    break;
            }
            // Copy-construct
            $acl_object_in_db = false;
            $acl_object = new $midcom_dba_classname();
            if (!midcom_baseclasses_core_dbobject::cast_object($acl_object, $unserialized_object))
            {
                debug_add('Failed to cast MidCOM DBA object for ACL checks from $unserialized_object', MIDCOM_LOG_ERROR);
                debug_print_r('$unserialized_object: ', $unserialized_object);
                debug_pop();
                return false;
            }
        }

        // Magic to check for various corner cases to determine the action to take later on
        switch(true)
        {
            case ($unserialized_object->action == 'purged'):
                // Purges not supported yet
                debug_add("Purges not supported yet (they require extra special love)", MIDCOM_LOG_ERROR);
                mgd_set_errno(MGD_ERR_OBJECT_PURGED);
                debug_pop();
                return false;
                break;
            // action is created but object is already in db, cast to update
            case (   $unserialized_object->action == 'created'
                  && $actual_object_in_db):
                $handle_action = 'updated';
                break;
            // Core bug in earlier 1.8 branch versions set action to none
            case (   $unserialized_object->action == 'none'
                  && $actual_object_in_db):
                $handle_action = 'updated';
                break;
            case (   $unserialized_object->action == 'none'
                  && !$actual_object_in_db):
                // Fall-through intentional
            case (   $unserialized_object->action == 'updated'
                  && !$actual_object_in_db):
                $handle_action = 'created';
                break;
            default:
                if ($unserialized_object->action)
                {
                    $handle_action = $unserialized_object->action;
                }
                else
                {
                    $handle_action = 'updated';
                }
                break;
        }

        if (!$acl_object->_on_importing())
        {
            debug_add("The _on_importing event handler returned false.", MIDCOM_LOG_ERROR);
            // set errno if not set
            if (mgd_errno() === MGD_ERR_OK)
            {
                mgd_set_errno(MGD_ERR_ERROR);
            }
            debug_pop();
            return false;
        }

        switch ($handle_action)
        {
            case 'deleted':
                if (!$actual_object_in_db)
                {
                    // we don't have the object locally created yet, so we just import via core and be done with it
                    midcom_helper_replicator_import_object($unserialized_object, $use_force);
                    break;
                }
                if (!midcom_baseclasses_core_dbobject::delete_pre_checks($acl_object))
                {
                    debug_add('delete pre-flight check returned false', MIDCOM_LOG_ERROR);
                    debug_pop();
                    return false;
                }
                // Actual import
                if (!midcom_helper_replicator_import_object($unserialized_object, $use_force))
                {
                    /**
                     * BEGIN workaround
                     * For http://trac.midgard-project.org/ticket/200
                     */
                    if (mgd_errno() === MGD_ERR_OBJECT_IMPORTED)
                    {
                        debug_add('Trying to workaround problem importing deleted action, calling $acl_object->delete()', MIDCOM_LOG_WARN);
                        if ($acl_object->delete())
                        {
                            debug_add('$acl_object->delete() succeeded, returning true early', MIDCOM_LOG_INFO);
                            debug_pop();
                            return true;
                        }
                        debug_add("\$acl_object->delete() failed for {$acl_object->guid}, errstr: " . mgd_errstr(), MIDCOM_LOG_ERROR);
                        // reset errno()
                        mgd_set_errno(MGD_ERR_OBJECT_IMPORTED);
                    }
                    /** END workaround */
                    debug_add('midcom_helper_replicator_import_object returned false, errstr: ' . mgd_errstr(), MIDCOM_LOG_ERROR);
                    debug_pop();
                    return false;
                }
                midcom_baseclasses_core_dbobject::delete_post_ops($acl_object);
                break;
            case 'updated':
                if (!midcom_baseclasses_core_dbobject::update_pre_checks($acl_object))
                {
                    debug_add('update pre-flight check returned false', MIDCOM_LOG_ERROR);
                    debug_pop();
                    return false;
                }
                // Actual import
                if (!midcom_helper_replicator_import_object($unserialized_object, $use_force))
                {
                    debug_add('midcom_helper_replicator_import_object returned false, errstr: ' . mgd_errstr(), MIDCOM_LOG_ERROR);
                    debug_pop();
                    return false;
                }
                // "refresh" acl_object
                if (!midcom_baseclasses_core_dbobject::cast_object($acl_object, $unserialized_object))
                {
                    // this shouldn't happen, but shouldn't be fatal either...
                }
                midcom_baseclasses_core_dbobject::update_post_ops($acl_object);
                break;
            case 'created':
                if (!midcom_baseclasses_core_dbobject::create_pre_checks($acl_object))
                {
                    debug_add('creation pre-flight check returned false', MIDCOM_LOG_ERROR);
                    debug_pop();
                    return false;
                }
                // Actual import
                if (!midcom_helper_replicator_import_object($unserialized_object, $use_force))
                {
                    debug_add('midcom_helper_replicator_import_object returned false, errstr: ' . mgd_errstr(), MIDCOM_LOG_ERROR);
                    debug_pop();
                    return false;
                }
                // refresh object to avoid issues with _on_created requiring ID
                $acl_object_refresh = new $midcom_dba_classname($unserialized_object->guid);
                if (   is_object($acl_object_refresh)
                    && $acl_object_refresh->id)
                {
                    $acl_object = $acl_object_refresh;
                    midcom_baseclasses_core_dbobject::create_post_ops($acl_object);
                }
                else
                {
                    // refresh failed (it really shouldn't), what to do ??
                }
                break;
            default:
                debug_add("Do not know how to handle action '{$handle_action}'", MIDCOM_LOG_ERROR);
                mgd_set_errno(MGD_ERR_ERROR);
                debug_pop();
                return false;
                break;
        }

        $acl_object->_on_imported();
        $_MIDCOM->componentloader->trigger_watches(MIDCOM_OPERATION_DBA_IMPORT, $acl_object);
        debug_pop();
        return true;
    }

    /**
     * Import midgard_blob unserialized with midgard_replicator::unserialize()
     *
     * This method does ACL checks and triggers watchers etc.
     *
     * @param midgard_blob $unserialized_object midgard_blob gotten from midgard_replicator::unserialize()
     * @param string $xml XML the midgard_blob was unserialized from
     * @param boolean $use_force set use of force for the midcom_helper_replicator_import_from_xml() call
     * @return boolean indicating success/failure
     */
    function import_blob(&$unserialized_object, &$xml, $use_force = false)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        if (!is_a($unserialized_object, 'midgard_blob'))
        {
            debug_add("You should use the *import* method to import normal objects, passing control there", MIDCOM_LOG_WARNING);
            debug_pop();
            return $this->import($unserialized_object, $use_force);
        }
        // We need this helper (workaround Zend bug)
        if (!function_exists('midcom_helper_replicator_import_object'))
        {
            $_MIDCOM->componentloader->load('midcom.helper.replicator');
        }

        $acl_object = $_MIDCOM->dbfactory->get_object_by_guid($unserialized_object->parentguid);
        if (   empty($acl_object)
            || !is_object($acl_object))
        {
            debug_add("Could not get parent object (GUID: {$unserialized_object->parentguid}), aborting", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }

        // midgard_blob has no action, update is the best check for allowing import of blob data
        if (!midcom_baseclasses_core_dbobject::update_pre_checks($acl_object))
        {
            $parent_class = get_class($acl_object);
            debug_add("parent ({$parent_class} {$parent->guid}) update pre-flight check returned false", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        // Actual import
        // NOTE: midgard_replicator::import_from_xml returns void, which evaluates to false, check mgd_errno instead
        midcom_helper_replicator_import_from_xml($xml, $use_force);
        if (mgd_errno() !== MGD_ERR_OK)
        {
            debug_add('midcom_helper_replicator_import_from_xml returned false, errstr: ' . mgd_errstr(), MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        // Trigger parent updated
        midcom_baseclasses_core_dbobject::update_post_ops($acl_object);
        // And also imported
        $_MIDCOM->componentloader->trigger_watches(MIDCOM_OPERATION_DBA_IMPORT, $acl_object);

        debug_pop();
        return true;
    }
}

?>