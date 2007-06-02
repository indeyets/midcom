<?php
/**
 * @package midgard.admin.asgard
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id: acl_editor.php 5538 2007-03-20 13:22:41Z rambo $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * The Grand Unified Reflector
 * @package midgard.admin.asgard
 */
class midgard_admin_asgard_reflector extends midcom_baseclasses_components_purecode
{

    var $_mgdschema_class = false;
    var $_mgd_reflector = false;
    var $_dummy_object = false;
    var $_original_class = false;
    var $get_class_label_l10n_ok = false;

    /**
     * Constructor, takes classname or object, resolved MgdSchema root class automagically
     *
     * @param string/midgard_object $src classname or object
     */
    function midgard_admin_asgard_reflector($src)
    {
        $this->_component = 'midgard.admin.asgard';
        parent::midcom_baseclasses_components_purecode();
        // Handle object vs string
        if (is_object($src))
        {
            $this->_original_class = get_class($src);
        }
        else
        {
            $this->_original_class = $src;
        }

        // Resolve root class name
        $this->_mgdschema_class = midgard_admin_asgard_reflector::resolve_baseclass($this->_original_class);

        // Could not resolve root class name
        if (empty($this->_mgdschema_class))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Could not determnine MgdSchema baseclass", MIDCOM_LOG_ERROR);
            debug_pop();
            $x = false;
            return $x;
        }

        // Instantiate midgard reflector
        $this->_mgd_reflector = new midgard_reflection_property($this->_mgdschema_class);
        if (!$this->_mgd_reflector)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Could not instantiate midgard_mgd_reflection_property for {$this->_mgdschema_class}", MIDCOM_LOG_ERROR);
            debug_pop();
            $x = false;
            return $x;
        }

        // Instatiate dummy object
        $this->_dummy_object = new $this->_mgdschema_class();
        if (!$this->_dummy_object)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Could not instantiate dummy object for {$this->_mgdschema_class}", MIDCOM_LOG_ERROR);
            debug_pop();
            $x = false;
            return $x;
        }
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
        if (!isset($GLOBALS['midgard_admin_asgard_reflector_singletons'][$classname]))
        {
            $GLOBALS['midgard_admin_asgard_reflector_singletons'][$classname] =  new midgard_admin_asgard_reflector($src);
        }
        return $GLOBALS['midgard_admin_asgard_reflector_singletons'][$classname];
    }

    /**
     * Gets a midcom_helper_l10n instance for component governing the type 
     *
     */
    function &get_component_l10n()
    {
        // Use cache if we have it
        if (!isset($GLOBALS['midgard_admin_asgard_reflector_get_component_l10n_cache']))
        {
            $GLOBALS['midgard_admin_asgard_reflector_get_component_l10n_cache'] = array();
        }
        if (isset($GLOBALS['midgard_admin_asgard_reflector_get_component_l10n_cache'][$this->_mgdschema_class]))
        {
            return $GLOBALS['midgard_admin_asgard_reflector_get_component_l10n_cache'][$this->_mgdschema_class];
        }
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("Trying to resolve good l10n for type {$this->_mgdschema_class}");
        $midcom_dba_classname = $_MIDCOM->dbclassloader->get_midcom_class_name_for_mgdschema_object($this->_dummy_object);
        if (empty($midcom_dba_classname))
        {
            // Could not resolve MidCOM DBA class name, fallback early to our own l10n
            debug_add("Could not get MidCOM DBA classname for type {$this->_mgdschema_class}, using our own l10n", MIDCOM_LOG_INFO);
            debug_pop();
            $GLOBALS['midgard_admin_asgard_reflector_get_component_l10n_cache'][$this->_mgdschema_class] = $this->_l10n;
            return $this->_l10n;
        }
        if (   !isset($_MIDCOM->dbclassloader->_mgdschema_class_handler[$midcom_dba_classname])
            || empty($_MIDCOM->dbclassloader->_mgdschema_class_handler[$midcom_dba_classname]))
        {
            // Cannot resolve component, fallback early to our own l10n
            debug_add("Could not resolve component for DBA class {$midcom_dba_classname}, using our own l10n", MIDCOM_LOG_INFO);
            debug_pop();
            $GLOBALS['midgard_admin_asgard_reflector_get_component_l10n_cache'][$this->_mgdschema_class] = $this->_l10n;
            return $this->_l10n;
        }
        // Got component, try to load the l10n helper for it
        $component = $_MIDCOM->dbclassloader->_mgdschema_class_handler[$midcom_dba_classname];
        debug_add("Class {$midcom_dba_classname} is handled by component {$component}");
        $midcom_i18n =& $_MIDCOM->get_service('i18n');
        $component_l10n =& $midcom_i18n->get_l10n($component);
        if (!empty($component_l10n))
        {
            debug_add("Got l10n handler for component {$component}, returning that");
            debug_pop();
            $GLOBALS['midgard_admin_asgard_reflector_get_component_l10n_cache'][$this->_mgdschema_class] =& $component_l10n;
            return $component_l10n;
        }

        // Could not get anything else, use our own l10n
        debug_add("Everything else failed, using our own l10n for type {$this->_mgdschema_class}", MIDCOM_LOG_WARN);
        debug_pop();
        $GLOBALS['midgard_admin_asgard_reflector_get_component_l10n_cache'][$this->_mgdschema_class] = $this->_l10n;
        return $this->_l10n;
    }

    function get_class_label()
    {
        static $component_l10n = false;
        if (!$component_l10n)
        {
            $component_l10n = $this->get_component_l10n();
        }
        $use_classname = $this->_mgdschema_class;
        $midcom_dba_classname = $_MIDCOM->dbclassloader->get_midcom_class_name_for_mgdschema_object($this->_dummy_object);
        if (!empty($midcom_dba_classname))
        {
            $use_classname = $midcom_dba_classname;
        }
        $use_classname = preg_replace('/_(db|dba)$/', '', $use_classname);

        $this->get_class_label_l10n_ok = true;
        $label = $component_l10n->get($use_classname);
        if ($label == $use_classname)
        {
            // Class string not localized, try Bergies way to pretty-print
            $classname_parts = explode('_', $use_classname);
            if (count($classname_parts) >= 3)
            {
                // Drop first two parts of class name
                array_shift($classname_parts);
                array_shift($classname_parts);
            }
            $use_label = implode('_', $classname_parts);
            $label = $component_l10n->get($use_label);
            if ($use_label == $label)
            {
                $this->get_class_label_l10n_ok = false;
            }
        }
        return $label;
    }

    /**
     * Get property name to use as label
     *
     * @return string name of property to use as label (or false on failure)
     */
    function get_label_property()
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

        debug_push_class(__CLASS__, __FUNCTION__);
        $obj = new $this->_original_class();
        $properties = get_object_vars($obj);
        if (empty($properties))
        {
            debug_add("Could not list object properties, aborting", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }

        // TODO: less trivial implementation        
        switch(true)
        {
            case (is_a($obj, 'midgard_topic')):
                $property = 'extra';
                break;
            case (is_a($obj, 'midgard_person')):
                $property = 'name';
                break;
            case (array_key_exists('title', $properties)):
                $property = 'title';
                break;
            case (array_key_exists('official', $properties)):
                $property = 'official';
                break;  
            case (array_key_exists('icao', $properties)):
                $property = 'icao';
                break;              
            case (array_key_exists('name', $properties)):
                $property = 'name';
                break;
            default:
                $property = 'guid';
        }

        return $property;
    }

    function get_object_label($obj)
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

        debug_push_class(__CLASS__, __FUNCTION__);
        $properties = get_object_vars($obj);
        if (empty($properties))
        {
            debug_add("Could not list object properties, aborting", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }

        switch(true)
        {
        	case (method_exists($obj,'get_asgard_label')):
        		$label = $obj->get_label();
        		break;
        		
            case (is_a($obj, 'midgard_topic')):
                if ($obj->extra)
                {
                	$label = $obj->extra;
                }
                else
                {
                	$label = $obj->name;
                }
                break;
            case (is_a($obj, 'midgard_article')
            	|| is_a($obj, 'midgard_page')):
                if ($obj->title)
                {
                	$label = $obj->title;
                }
                else
                {
                	$label = $obj->name;
                }
                break;
            case (is_a($obj, 'midgard_host')):
                if ($obj->port && $obj->port != "80")
                {
                	$label = "{$obj->name}:{$obj->port}{$obj->prefix}";
                }
                else
                {
                	$label = "{$obj->name}{$obj->prefix}";
                }
                break;
            case (array_key_exists('title', $properties)):
                $label = $obj->title;
                break;
            case (array_key_exists('official', $properties)):
                $label = $obj->official;
                break;  
            case (array_key_exists('icao', $properties)):
                $label = $obj->icao;
                break;              
            case (array_key_exists('name', $properties)):
                $label = $obj->name;
                break;
            default:
                $label = $obj->guid;
        }

        return $label;
    }

    /**
     * Get class properties to use as search fields in univeralchooser etc
     *
     * @return array of property names
     */
    function get_search_properties()
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

        // Return cached results if we have them
        static $cache = array();
        if (isset($cache[$this->_mgdschema_class]))
        {
            return $cache[$this->_mgdschema_class]; 
        }
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("Starting analysis for class {$this->_mgdschema_class}");
        $obj =& $this->_dummy_object;

        // Get property list and start checking (or abort on error)
        $properties = get_object_vars($obj);
        if (empty($properties))
        {
            debug_add("Could not list object properties, aborting", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }

        $search_properties = array();
        foreach ($properties as $property => $dummy)
        {
            switch(true)
            {
                case (strpos($property, 'name') !== false):
                    // property contains 'name'
                    $search_properties[] = $property;
                    break;
                case ($property == 'title'):
                    $search_properties[] = $property;
                    break;
                // TODO: More per property heuristics
            }
        }
        // TODO: parent and up heuristics

        // Exceptions - always search these fields
        $always_search_all = $this->_config->get('always_search_fields');
        // safety against misconfiguration
        if (!is_array($always_search_all))
        {
            $always_search_all = array();
        }
        $always_search = array();
        if (isset($always_search_all[$this->_mgdschema_class]))
        {
            $always_search = $always_search_all[$this->_mgdschema_class];
        }
        foreach ($always_search as $property)
        {
            if (!array_key_exists($property, $properties))
            {
                debug_add("Property '{$property}' is set as always search, but is not a property in class '{$this->_mgdschema_class}'", MIDCOM_LOG_WARN);
                continue;
            }
            if (in_array($property, $search_properties))
            {
                // Already listed
                debug_add("Property '{$property}', already exists in \$search_properties");
                continue;
            }
            $search_properties[] = $property;
        }

        // Exceptions - never search these fields
        $never_search_all = $this->_config->get('never_search_fields');
        // safety against misconfiguration
        if (!is_array($never_search_all))
        {
            $never_search_all = array();
        }
        $never_search = array();
        if (isset($never_search_all[$this->_mgdschema_class]))
        {
            $never_search = $never_search_all[$this->_mgdschema_class];
        }
        foreach ($never_search as $property)
        {
            if (!in_array($property, $search_properties))
            {
                continue;
            }
            debug_add("Removing '{$property}' from \$search_properties", MIDCOM_LOG_INFO);
            $key = array_search($property, $search_properties);
            if ($key === false)
            {
                debug_add("Cannot find key for '{$property}' in \$search_properties", MIDCOM_LOG_ERROR);
                continue;
            }
            unset($search_properties[$key]);
        }
        
        debug_print_r("Search properties for {$this->_mgdschema_class}: ", $search_properties);
        debug_pop();
        $cache[$this->_mgdschema_class] = $search_properties;
        return $search_properties;
    }


    /**
     * Gets a list of link properties and the links target info
     *
     * Link info key specification
     *     'class' string link target class name
     *     'target' string link target property (of target class)
     *     'parent' boolean link is link to "parent" in object tree
     *     'up' boolean link is link to "up" in object tree
     *
     * @return array multidimensional array keyed by property, values are arrays with link info (or false in case of failure)
     */
    function get_link_properties()
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

        // Return cached results if we have them
        static $cache = array();
        if (isset($cache[$this->_mgdschema_class]))
        {
            return $cache[$this->_mgdschema_class]; 
        }
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("Starting analysis for class {$this->_mgdschema_class}");

        // Shorthands
        $ref =& $this->_mgd_reflector;
        $obj =& $this->_dummy_object;

        // Get property list and start checking (or abort on error)
        $properties = get_object_vars($obj);
        if (empty($properties))
        {
            debug_add("Could not list object properties, aborting", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }

        $links = array();
        $parent_property = midgard_object_class::get_property_parent($obj);
        $up_property = midgard_object_class::get_property_up($obj);
        foreach ($properties as $property => $dummy)
        {
            if (!$ref->is_link($property))
            {
                continue;
            }
            debug_add("Processing property '{$property}'");
            $linkinfo = array
            (
                'class' => null,
                'target' => null,
                'parent' => false,
                'up' => false,
            );
            if (   !empty($parent_property)
                && $parent_property === $property)
            {
                debug_add("Is 'parent' property");
                $linkinfo['parent'] = true;
            }
            if (   !empty($up_property)
                && $up_property === $property)
            {
                debug_add("Is 'up' property");
                $linkinfo['up'] = true;
            }

            $type = $ref->get_link_name($property);
            debug_add("get_link_name returned '{$type}'");
            if (!empty($type))
            {
                $linkinfo['class'] = $type;
            }
            unset($type);

            $target = $ref->get_link_target($property);
            debug_add("get_link_target returned '{$target}'");
            if (!empty($target))
            {
                $linkinfo['target'] = $target;
            }
            unset($target);

            $links[$property] = $linkinfo;
            unset($linkinfo);
        }

        debug_print_r("Links for {$this->_mgdschema_class}: ", $links);
        debug_pop();
        $cache[$this->_mgdschema_class] = $links;
        return $links;
    }

    /**
     * Statically callable method to map extended classes
     *
     * For example org.openpsa.* components often expand core objects,
     * in config we specify which classes we wish to substitute with which
     *
     * @param string $schema_type classname to check rewriting for
     * @return string new classname (or original in case no rewriting is to be done)
     */
    function class_rewrite($schema_type)
    {
        static $extends = false;
        if ($extends === false)
        {
            $extends = $GLOBALS['midcom_component_data']['midgard.admin.asgard']['config']->get('class_extends');
            // Safety against misconfiguration
            if (!is_array($extends))
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("config->get('class_extends') did not return array, invalid configuration ??", MIDCOM_LOG_ERROR);
                debug_pop();
                return $schema_type;
            }
        }
        if (   isset($extends[$schema_type])
            && class_exists($extends[$schema_type]))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Rewriting type {$schema_type} to {$extends[$schema_type]}");
            debug_pop();
            return $extends[$schema_type];
        }
        return $schema_type;
    }

    /**
     * Statically callable method to see if two MgdSchema classes are the same
     *
     * NOTE: also takes into account the various extended class scenarios
     *
     * @param string $class_one first class to compare
     * @param string $class_two second class to compare
     * @return boolean response
     */
    function is_same_class($class_one, $class_two)
    {
        $one = midgard_admin_asgard_reflector::resolve_baseclass($class_one);
        $two = midgard_admin_asgard_reflector::resolve_baseclass($class_two);
        if ($one == $two)
        {
            return true;
        }
        if (midgard_admin_asgard_reflector::class_rewrite($one) == $two)
        {
            return true;
        }
        if ($one == midgard_admin_asgard_reflector::class_rewrite($two))
        {
            return true;
        }

        return false;
    }

    /**
     * Get the root level classname for given class, statically callable
     *
     * @param string $classname to get the baseclass for
     * @return string the base class name
     */
    function resolve_baseclass($classname)
    {
        $parent = $classname;
        do
        {
            $baseclass = $parent;
            $parent = get_parent_class($baseclass);
        }
        while ($parent !== false);
        return $baseclass;
    }
}

/**
 * I hope we don't need these workarounds but in case we do, keep them handy
function midgard_admin_asgard_reflector_get_property_parent(&$src)
{
    return midgard_object_class::get_property_parent($src);
}

function midgard_admin_asgard_reflector_get_property_up(&$src)
{
    return midgard_object_class::get_property_up($src);
}
*/
?>