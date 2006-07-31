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
    function midcom_helper__dbfactory()
    {
        parent::midcom_baseclasses_core_object();
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
        $tmp = mgd_get_new_object_by_guid($guid);
        if (! $tmp)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("The Midgard core failed to resolve the GUID {$guid}: " . mgd_errstr(), MIDCOM_LOG_INFO);
            debug_pop();
            return null;
        }
        return $this->convert_midgard_to_midcom($tmp);
    }

    /**
     * This function will determine the correct type of MidgardQueryBuilder that
     * has to be created. It will also call the _on_prepare_new_query_builder event handler.
     *
     * Since this is to be called statically, it will take a class name, not a instance
     * as argument.
     *
     * @param string $classname The name of the class for which you want to create a query builder.
     * @return The initialized instance of the query builder.
     * @see midcom_core_querybuilder
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
     * @param MidgardQueryBuilder $qb An instance of the Query builder obtained by the new_query_builder
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
     * @param MidgardObject $object Pre-MgdSchema Midgard Object
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

        if ($_MIDCOM->dbclassloader->is_legacy_midgard_object($object))
        {
            $classname = $_MIDCOM->dbclassloader->get_midcom_class_name_for_legacy_midgard_object($object);

            if (! array_key_exists('id', $object))
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_print_type("Cannot cast the object to a MgdSchema type, there is no ID property, we got this type:",
                    $object, MIDCOM_LOG_ERROR);
                debug_print_r("Object dump:", $object);
                debug_pop();
                return null;
            }

            $result = new $classname($object->id);
        }
        else if ($_MIDCOM->dbclassloader->is_mgdschema_object($object))
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
     * GUID according to the MidCOM conten tree rules.
     *
     * It tries to look up the GUID in the memory cache, only if this fails, the regular
     * content getters are invoked.
     *
     * @param mixed $object Either a MidCOM DBA object instance, or a GUID string.
     * @return string The parent GUID, or null, if this is a top level object.
     */
    function get_parent_guid($object)
    {
        if (is_object($object))
        {
            $object_guid = $object->guid;
            $the_object =& $object;
        }
        else
        {
            $object_guid = $object;
            $the_object = null;
        }

        if (! mgd_is_guid($object_guid))
        {
            if ($the_object === null)
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                    'Tried to resolve an invalid GUID without an object being present. This cannot be done.');
                // This will exit.
            }
            $guid = false;
        }
        else
        {
            $guid = $_MIDCOM->cache->memcache->lookup_parent_guid($object_guid);
        }

        if ($guid === false)
        {
            // No cache hit, retrieve the actual object and update the cache.
            if ($the_object === null)
            {
                $the_object = $this->get_object_by_guid($object);
                if (! is_object($the_object))
                {
                    return null;
                   }
            }

            $guid = $the_object->get_parent_guid_uncached();
            if (is_object($guid))
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add('Warning, get_parent_guid_uncached should not return an object. This feature is deprecated.',
                    MIDCOM_LOG_INFO);
                debug_pop();
                $guid = $guid->guid;
            }

            if (mgd_is_guid($object_guid))
            {
                $_MIDCOM->cache->memcache->update_parent_guid($object_guid, $guid);
            }
        }

        if (! mgd_is_guid($guid))
        {
            return null;
        }
        else
        {
            return $guid;
        }
    }
}

?>