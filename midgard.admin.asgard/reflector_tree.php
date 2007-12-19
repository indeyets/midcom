<?php
/**
 * @package midgard.admin.asgard
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id: acl_editor.php 5538 2007-03-20 13:22:41Z rambo $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

if (!class_exists('midgard_admin_asgard_reflector'))
{
    require_once('reflector.php');
}

/**
 * The Grand Unified Reflector, Tree information
 * @package midgard.admin.asgard
 */
class midgard_admin_asgard_reflector_tree extends midgard_admin_asgard_reflector
{

    function midgard_admin_asgard_reflector_tree($src)
    {
        parent::midgard_admin_asgard_reflector($src);
    }

    function &get($src)
    {
        if (is_object($src))
        {
            $classname = get_class($src);
        }
        else
        {
            $classname = $src;
        }
        if (!isset($GLOBALS['midgard_admin_asgard_reflector_tree_singletons'][$classname]))
        {
            $GLOBALS['midgard_admin_asgard_reflector_tree_singletons'][$classname] =  new midgard_admin_asgard_reflector_tree($src);
        }
        return $GLOBALS['midgard_admin_asgard_reflector_tree_singletons'][$classname];
    }


    /**
     * Creates a QB instance for get_root_objects and count_root_objects
     *
     * @access private
     */
    function &_root_objects_qb(&$deleted)
    {
        $schema_type =& $this->_mgdschema_class;
        $root_classes = midgard_admin_asgard_reflector_tree::get_root_classes();
        if (!in_array($schema_type, $root_classes))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Type {$schema_type} is not a \"root\" type", MIDCOM_LOG_ERROR);
            debug_pop();
            $x = false;
            return $x;
        }
        
        if ($deleted)
        {
            $qb = new midgard_query_builder($schema_type);
        }
        else
        {
            // Figure correct MidCOM DBA class to use and get midcom QB
            $qb = false;
            $midcom_dba_classname = $_MIDCOM->dbclassloader->get_midcom_class_name_for_mgdschema_object($this->_dummy_object);
            if (empty($midcom_dba_classname))
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("MidCOM DBA does not know how to handle {$schema_type}", MIDCOM_LOG_ERROR);
                debug_pop();
                $x = false;
                return $x;
            }
            if (!$_MIDCOM->dbclassloader->load_mgdschema_class_handler($midcom_dba_classname))
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("Failed to load the handling component for {$midcom_dba_classname}, cannot continue.", MIDCOM_LOG_ERROR);
                debug_pop();
                $x = false;
                return $x;
            }
            $qb_callback = array($midcom_dba_classname, 'new_query_builder');
            if (!is_callable($qb_callback))
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("Static method {$midcom_dba_classname}::new_query_builder() is not callable", MIDCOM_LOG_ERROR);
                debug_pop();
                $x = false;
                return $x;
            }
            $qb = call_user_func($qb_callback);
        }

        // Sanity-check
        if (!$qb)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Could not get QB for type '{$schema_type}'", MIDCOM_LOG_ERROR);
            debug_pop();
            $x = false;
            return $x;
        }
        // Deleted constraints
        if ($deleted)
        {
            $qb->include_deleted();
            $qb->add_constraint('metadata.deleted', '<>', 0);
        }
        
        if ($_MIDGARD['sitegroup'])
        {
            $qb->add_constraint('sitegroup', '=', $_MIDGARD['sitegroup']);
        }

        // Figure out constraint to use to get root level objects
        $upfield = midgard_object_class::get_property_up($schema_type);
        if (!empty($upfield))
        {
            $ref =& $this->_mgd_reflector;
            $uptype = $ref->get_midgard_type($upfield);
            switch ($uptype)
            {
                case MGD_TYPE_STRING:
                case MGD_TYPE_GUID:
                    $qb->add_constraint($upfield, '=', '');
                    break;
                case MGD_TYPE_INT:
                case MGD_TYPE_UINT:
                    $qb->add_constraint($upfield, '=', 0);
                    break;
                default:
                    debug_add("Do not know how to handle upfield '{$upfield}' has type {$uptype}", MIDCOM_LOG_ERROR);
                    debug_pop();
                    return false;
            }
        }
        return $qb;
    }

    /**
     * Get count of "root" objects for the class this reflector was instantiated for
     *
     * @param boolean $deleted whether to count (only) deleted or not-deleted objects
     * @return array of objects or false on failure
     * @see get_root_objects
     */
    function count_root_objects($deleted = false)
    {
        // Check against static calling
        if (   !isset($this->_mgdschema_class)
            || empty($this->_mgdschema_class))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('May not be called statically', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        // PONDER: Check for some generic user privilege instead  ??
        if (   $deleted
            && !$_MIDGARD['admin'])
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('Non-admins are not allowed to list deleted objects', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }

        $qb = $this->_root_objects_qb($deleted);
        if (!$qb)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('Could not get QB instance', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }

        $count = $qb->count();
        return $count;
    }

    function has_root_objects()
    {
        // TODO implement
    }

    /**
     * Get "root" objects for the class this reflector was instantiated for
     *
     * NOTE: deleted objects can only be listed as admin, also: they do not come
     * MidCOM DBA wrapped (since you cannot normally instantiate such object)
     *
     * @param boolean $deleted whether to get (only) deleted or not-deleted objects
     * @return array of objects or false on failure
     */
    function get_root_objects($deleted = false, $all = false)
    {
        // Check against static calling
        if (   !isset($this->_mgdschema_class)
            || empty($this->_mgdschema_class))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('May not be called statically', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        // PONDER: Check for some generic user privilege instead  ??
        if (   $deleted
            && !$_MIDGARD['admin'])
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('Non-admins are not allowed to list deleted objects', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }

        $qb = $this->_root_objects_qb($deleted);
        if (!$qb)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('Could not get QB instance', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        
        /*if (   !$all
            && $qb->count() > 25)
        {
            $qb->set_limit(25);
        }*/

        $objects = $qb->execute();

        return $objects;
    }

    function has_child_objects()
    {
        // TODO: implement
    }

    /**
     * Statically callable method to count children of given object
     *
     * @param midgard_object $object object to get children for
     * @param boolean $deleted whether to count (only) deleted or not-deleted objects
     * @return array multidimensional array (keyed by classname) of objects or false on failure
     */
    function count_child_objects(&$object, $deleted = false)
    {
        // PONDER: Check for some generic user privilege instead  ??
        if (   $deleted
            && !$_MIDGARD['admin'])
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('Non-admins are not allowed to list deleted objects', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        $resolver = new midgard_admin_asgard_reflector_tree($object);
        if (!$resolver)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Could not instantiate midgard_admin_asgard_reflector_tree from \$object", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        $child_classes = $resolver->get_child_classes();
        if (!$child_classes)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('resolver returned false (critical failure) from get_child_classes()', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }

        $child_counts = array();
        foreach ($child_classes as $schema_type)
        {
            $child_counts[$schema_type] = $this->_count_child_objects_type($schema_type, $object, $deleted);
        }
        return $child_counts;
    }

    /**
     * statically callable method to get the parent object of given object
     *
     * Tries to utilize MidCOM DBA features first but can fallback on pure MgdSchema
     * as necessary
     *
     * NOTE: since this might fall back to pure MgdSchema never trust that MidCOM DBA features 
     * are available, check for is_callable/method_exists first !
     *
     * @param midgard_object $object the object to get parent for
     */
    function get_parent(&$object)
    {
        $parent_object = false;
        $dba_parent_callback = array($object, 'get_parent');
        if (is_callable($dba_parent_callback))
        {
            $parent_object = $object->get_parent();
            /**
             * The object might have valid reasons for returning empty value here, but we can't know if it's
             * beacause it's valid or because the get_parent* methods have not been overridden in the actually
             * used class
             */
        }
        if (!empty($parent_object))
        {
            return $parent_object;
        }
        
        // Reflection magick
        $resolver = new midgard_admin_asgard_reflector_tree($object);
        $ref =& $resolver->_mgd_reflector;
        $schema_type =& $resolver->_mgdschema_class;

        // up takes precedence over parent
        $up_property = midgard_object_class::get_property_up($schema_type);
        if (   !empty($up_property)
            && !empty($object->$up_property))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Checking if we can get something with up property {$up_property} (value: {$object->$up_property})");
            $parent_object = $resolver->_get_parent_objectresolver($object, $up_property);
            if (!empty($parent_object))
            {
                debug_pop();
                return $parent_object;
            }
            debug_add('Could not get anything but since the property was defined we abort here');
            // We tried but failed, do not try more
            debug_pop();
            return false;
        }

        $parent_property = midgard_object_class::get_property_parent($schema_type);
        if (   !empty($parent_property)
            && !empty($object->$parent_property))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Checking if we can get something with parent property {$parent_property} (value: {$object->$parent_property})");
            $parent_object = $resolver->_get_parent_objectresolver($object, $parent_property);
            if (!empty($parent_object))
            {
                debug_pop();
                return $parent_object;
            }
            debug_add('Could not get anything but since the property was defined we abort here');
            // We tried but failed, do not try more
            debug_pop();
            return false;
        }

        debug_add('Nothing worked, returning false');
        debug_pop();
        return false;
    }

    function _get_parent_objectresolver(&$object, &$property)
    {
        $ref =& $this->_mgd_reflector;
        $target_class = $ref->get_link_name($property);
        $dummy_object = new $target_class();
        $midcom_dba_classname = $_MIDCOM->dbclassloader->get_midcom_class_name_for_mgdschema_object($dummy_object);
        if (!empty($midcom_dba_classname))
        {
            if (!$_MIDCOM->dbclassloader->load_mgdschema_class_handler($midcom_dba_classname))
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("Failed to load the handling component for {$midcom_dba_classname}, cannot continue.", MIDCOM_LOG_ERROR);
                debug_pop();
                return false;
            }
            // DBA classes can supposedly handle their own typecasts correctly
            $parent_object = new $midcom_dba_classname($object->$property);
            return $parent_object;
        }
        debug_add("MidCOM DBA does not know how to handle {$schema_type}, falling back to pure MgdSchema", MIDCOM_LOG_WARN);

        $linktype = $ref->get_midgard_type($property);
        switch ($linktype)
        {
            case MGD_TYPE_STRING:
            case MGD_TYPE_GUID:
                $parent_object = new $target_class((string)$object->$property);
                break;
            case MGD_TYPE_INT:
            case MGD_TYPE_UINT:
                $parent_object = new $target_class((int)$object->$property);
                break;
            default:
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("Do not know how to handle linktype {$linktype}", MIDCOM_LOG_ERROR);
                debug_pop();
                return false;
        }

        return $parent_object;
    }


    /**
     * Statically callable method to get children of given object
     *
     * @param midgard_object $object object to get children for
     * @param boolean $deleted whether to get (only) deleted or not-deleted objects
     * @return array multidimensional array (keyed by classname) of objects or false on failure
     */
    function get_child_objects(&$object, $deleted = false)
    {
        // PONDER: Check for some generic user privilege instead  ??
        if (   $deleted
            && !$_MIDGARD['admin'])
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('Non-admins are not allowed to list deleted objects', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        $resolver = new midgard_admin_asgard_reflector_tree($object);
        if (!$resolver)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Could not instantiate midgard_admin_asgard_reflector_tree from \$object", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        $child_classes = $resolver->get_child_classes();
        if (!$child_classes)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('resolver returned false (critical failure) from get_child_classes()', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }

        $child_objects = array();
        foreach ($child_classes as $schema_type)
        {
            $type_children = $resolver->_get_child_objects_type($schema_type, $object, $deleted);
            // PONDER: check for boolean false as result ??
            if (empty($type_children))
            {
                unset($type_children);
                continue;
            }
            $child_objects[$schema_type] = $type_children;
            unset($type_children);
        }
        return $child_objects;
    }

    /**
     * Creates a QB instance for _get_child_objects_type and _count_child_objects_type
     *
     * @access private
     */
    function _child_objects_type_qb(&$schema_type, &$for_object, &$deleted)
    {
        if ($deleted)
        {
            $qb = new midgard_query_builder($schema_type);
        }
        else
        {
            // Figure correct MidCOM DBA class to use and get midcom QB
            $qb = false;
            $dummy_object = new $schema_type();
            $midcom_dba_classname = $_MIDCOM->dbclassloader->get_midcom_class_name_for_mgdschema_object($dummy_object);
            if (empty($midcom_dba_classname))
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("MidCOM DBA does not know how to handle {$schema_type}", MIDCOM_LOG_ERROR);
                debug_pop();
                $x = false;
                return $x;
            }
            if (!$_MIDCOM->dbclassloader->load_mgdschema_class_handler($midcom_dba_classname))
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("Failed to load the handling component for {$midcom_dba_classname}, cannot continue.", MIDCOM_LOG_ERROR);
                debug_pop();
                $x = false;
                return $x;
            }
            $qb_callback = array($midcom_dba_classname, 'new_query_builder');
            if (!is_callable($qb_callback))
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("Static method {$midcom_dba_classname}::new_query_builder() is not callable", MIDCOM_LOG_ERROR);
                debug_pop();
                $x = false;
                return $x;
            }
            $qb = call_user_func($qb_callback);
        }

        // Sanity-check
        if (!$qb)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Could not get QB for type '{$schema_type}'", MIDCOM_LOG_ERROR);
            debug_pop();
            $x = false;
            return $x;
        }
        // Deleted constraints
        if ($deleted)
        {
            $qb->include_deleted();
            $qb->add_constraint('metadata.deleted', '<>', 0);
        }

        if ($_MIDGARD['sitegroup'])
        {
            $qb->add_constraint('sitegroup', '=', $_MIDGARD['sitegroup']);
        }

        // Figure out constraint to use to get child objects
        // TODO: Review this code
        $qb->begin_group('OR');
        $ref = new midgard_reflection_property($schema_type);
        $upfield = midgard_object_class::get_property_up($schema_type);

        if (!empty($upfield))
        {
            $uptype = $ref->get_midgard_type($upfield);
            $uptarget = $ref->get_link_target($upfield);
            if (!isset($for_object->$uptarget))
            {
                $qb->end_group();
                return false;
            }
            switch ($uptype)
            {
                case MGD_TYPE_STRING:
                case MGD_TYPE_GUID:
                    $qb->add_constraint($upfield, '=', (string)$for_object->$uptarget);
                    break;
                case MGD_TYPE_INT:
                case MGD_TYPE_UINT:
                    $qb->add_constraint($upfield, '=', (int)$for_object->$uptarget);
                    break;
                default:
                    debug_push_class(__CLASS__, __FUNCTION__);
                    debug_add("Do not know how to handle upfield '{$upfield}' has type {$uptype}", MIDCOM_LOG_ERROR);
                    debug_pop();
                    $qb->end_group();
                    return false;
            }
        }
        $parentfield = midgard_object_class::get_property_parent($schema_type);
        if (!empty($parentfield))
        {
            $parenttype = $ref->get_midgard_type($parentfield);
            $parenttarget = $ref->get_link_target($parentfield);
            switch ($parenttype)
            {
                case MGD_TYPE_STRING:
                case MGD_TYPE_GUID:
                    $qb->add_constraint($parentfield, '=', (string)$for_object->$parenttarget);
                    break;
                case MGD_TYPE_INT:
                case MGD_TYPE_UINT:
                        $qb->add_constraint($parentfield, '=', (int)$for_object->$parenttarget);
                    break;
                default:
                    debug_push_class(__CLASS__, __FUNCTION__);
                    debug_add("Do not know how to handle parentfield '{$parentfield}' has type {$parenttype}", MIDCOM_LOG_INFO);
                    debug_pop();
                    $qb->end_group();
                    return false;
            }
        }
        $qb->end_group();
        // TODO: /Review this code
        
        return $qb;
    }

    /**
     * Used by get_child_objects
     *
     * @access private
     * @return array of objects
     */
    function _get_child_objects_type(&$schema_type, &$for_object, &$deleted)
    {
        $qb = $this->_child_objects_type_qb($schema_type, $for_object, $deleted);
        if (!$qb)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('Could not get QB instance', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        $objects = $qb->execute();
        return $objects;
    }

    /**
     * Used by count_child_objects
     *
     * @access private
     * @return array of objects
     */
    function _count_child_objects_type(&$schema_type, &$for_object, &$deleted)
    {
        $qb = $this->_child_objects_type_qb($schema_type, $for_object, $deleted);
        if (!$qb)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('Could not get QB instance', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        $count = $qb->count();
        return $count;
    }

    /**
     * Get the child classes of the class this reflector was instantiated for
     *
     * @return array of class names (or false on critical failure)
     */
    function get_child_classes()
    {
        // Check against static calling
        if (   !isset($this->_mgdschema_class)
            || empty($this->_mgdschema_class))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('May not be called statically', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        static $child_classes_all = array();
        if (!isset($child_classes_all[$this->_mgdschema_class]))
        {
            $child_classes_all[$this->_mgdschema_class] = false;
        }
        $child_classes =& $child_classes_all[$this->_mgdschema_class];
        if ($child_classes === false)
        {
            $child_classes = $this->_resolve_child_classes();
        }
        return $child_classes;
    }

    /**
     * Resolve the child classes of the class this reflector was instantiated for, used by get_child_classes()
     *
     * @return array of class names (or false on critical failure)
     */
    function _resolve_child_classes()
    {
        // Check against static calling
        if (   !isset($this->_mgdschema_class)
            || empty($this->_mgdschema_class))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('May not be called statically', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        $child_classes = array();
        foreach ($_MIDGARD['schema']['types'] as $schema_type => $dummy)
        {
            $parent_property = midgard_object_class::get_property_parent($schema_type);
            $up_property = midgard_object_class::get_property_up($schema_type);
            if (   !$this->_resolve_child_classes_links_back($parent_property, $schema_type, $this->_mgdschema_class)
                && !$this->_resolve_child_classes_links_back($up_property, $schema_type, $this->_mgdschema_class))
            {
                continue;
            }
            $child_classes[] = $schema_type;
        }
        
        // TODO: handle exceptions
        
        return $child_classes;
    }

    function _resolve_child_classes_links_back($property, $prospect_type, $schema_type)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        if (empty($property))
        {
            debug_add('Given property is empty, aborting early');
            debug_pop();
            return false;
        }
        $ref = new midgard_reflection_property($prospect_type);
        $link_class = $ref->get_link_name($property);
        debug_add("got link_class '{$link_class}' for property '{$property}' in type '{$prospect_type}'");
        if (midgard_admin_asgard_reflector::is_same_class($link_class, $schema_type))
        {
            debug_pop();
            return true;
        }
        debug_add("link_class '{$link_class}' did not match '{$schema_type}' even after rewrites");
        debug_pop();
        return false;
    }

    /**
     * Get an array of "root level" classes, can (and should) be called statically
     *
     * @return array of classnames (or false on critical failure)
     */
    function get_root_classes()
    {
        static $root_classes = false;
        if (empty($root_classes))
        {
            $root_classes = midgard_admin_asgard_reflector_tree::_resolve_root_classes();
        }
        return $root_classes;
    }

    /**
     * Resolves the "root level" classes, used by get_root_classes()
     *
     * @access private
     * @return array of classnames (or false on critical failure)
     */
    function _resolve_root_classes()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $root_exceptions_notroot = $GLOBALS['midcom_component_data']['midgard.admin.asgard']['config']->get('root_class_exceptions_notroot');
        // Safety against misconfiguration
        if (!is_array($root_exceptions_notroot))
        {
            debug_add("config->get('root_class_exceptions_notroot') did not return array, invalid configuration ??", MIDCOM_LOG_ERROR);
            $root_exceptions_notroot = array();
        }
        $root_classes = array();
        foreach ($_MIDGARD['schema']['types'] as $schema_type => $dummy)
        {
            if (in_array($schema_type, $root_exceptions_notroot))
            {
                // Explicitly specified to not be root class, skip all heuristics
                debug_add("Type {$schema_type} is listed in exceptions to never be root");
                continue;
            }

            // Class extensions mapping
            $schema_type = midgard_admin_asgard_reflector::class_rewrite($schema_type);

            // Make sure we only add classes once
            if (in_array($schema_type, $root_classes))
            {
                // Already listed
                debug_add("Type {$schema_type} already exists in \$root_types");
                continue;
            }
            /* We might not need this afterall
            $ref = new midgard_reflection_property($schema_type);
            */
            $parent = midgard_object_class::get_property_parent($schema_type);
            if (!empty($parent))
            {
                // type has parent set, thus cannot be root type
                debug_add("Type {$schema_type} has parent property {$parent}, thus cannot be root class");
                continue;
            }


            // DBA types can provide 'noasgard' property for navigation hiding
            /*
             * Example:
             *
             * class pl_olga_test_dba extends __pl_olga_test_dba
             * {
             * 
             *     var $noasgard = true;
             * 
             *     function pl_olga_test_dba($id = null)    {
             *         return parent::__pl_olga_test_dba($id);
             *     }
             * 
             * }
             * 
             */

            if (class_exists("{$schema_type}_dba"))
            {
                $test_class_name= "{$schema_type}_dba";
                $test_class = new $test_class_name();

                if (   isset($test_class->noasgard)
                    && $test_class->noasgard)
                {
                    debug_add("Type {$schema_type} has 'noagsard' property set, thus cannot be root class");
                    continue;
                }
            }

            $root_classes[] = $schema_type;
        }
        unset($root_exceptions_notroot);
        $root_exceptions_forceroot = $GLOBALS['midcom_component_data']['midgard.admin.asgard']['config']->get('root_class_exceptions_forceroot');
        // Safety against misconfiguration
        if (!is_array($root_exceptions_forceroot))
        {
            debug_add("config->get('root_class_exceptions_forceroot') did not return array, invalid configuration ??", MIDCOM_LOG_ERROR);
            $root_exceptions_forceroot = array();
        }
        if (!empty($root_exceptions_forceroot))
        {
            foreach($root_exceptions_forceroot as $schema_type)
            {
                if (!class_exists($schema_type))
                {
                    // Not a valid class
                    debug_add("Type {$schema_type} has been listed to always be root class, but the class does not exist", MIDCOM_LOG_WARN);
                    continue;
                }
                if (in_array($schema_type, $root_classes))
                {
                    // Already listed
                    debug_add("Force type {$schema_type}, already exists in \$root_types");
                    continue;
                }
                debug_add("Forcing type {$schema_type} to be root type", MIDCOM_LOG_INFO);
                $root_classes[] = $schema_type;
            }
        }
        usort($root_classes, 'strnatcmp');
        debug_pop();
        return $root_classes;
    }
}
?>