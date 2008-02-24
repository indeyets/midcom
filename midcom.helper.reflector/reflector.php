<?php
/**
 * @package midcom.helper.reflector
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: acl_editor.php 5538 2007-03-20 13:22:41Z rambo $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * The Grand Unified Reflector
 * @package midcom.helper.reflector
 */
class midcom_helper_reflector extends midcom_baseclasses_components_purecode
{

    var $mgdschema_class = false;
    var $_mgd_reflector = false;
    var $_dummy_object = false;
    var $_original_class = false;
    var $get_class_label_l10n_ok = false;

    /**
     * Constructor, takes classname or object, resolved MgdSchema root class automagically
     *
     * @param string/midgard_object $src classname or object
     */
    function midcom_helper_reflector($src)
    {
        $this->_component = 'midcom.helper.reflector';
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
        $this->mgdschema_class = midcom_helper_reflector::resolve_baseclass($this->_original_class);

        // Could not resolve root class name
        if (empty($this->mgdschema_class))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Could not determnine MgdSchema baseclass", MIDCOM_LOG_ERROR);
            debug_pop();
            $x = false;
            return $x;
        }

        // Instantiate midgard reflector
        if (!class_exists($this->mgdschema_class))
        {
            $x = false;
            return $x;
        }
        $this->_mgd_reflector = new midgard_reflection_property($this->mgdschema_class);
        if (!$this->_mgd_reflector)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Could not instantiate midgard_mgd_reflection_property for {$this->mgdschema_class}", MIDCOM_LOG_ERROR);
            debug_pop();
            $x = false;
            return $x;
        }

        // Instantiate dummy object
        $this->_dummy_object = new $this->mgdschema_class();
        if (!$this->_dummy_object)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Could not instantiate dummy object for {$this->mgdschema_class}", MIDCOM_LOG_ERROR);
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
        if (!isset($GLOBALS['midcom_helper_reflector_singletons'][$classname]))
        {
            $GLOBALS['midcom_helper_reflector_singletons'][$classname] =  new midcom_helper_reflector($src);
        }
        return $GLOBALS['midcom_helper_reflector_singletons'][$classname];
    }

    /**
     * Gets a midcom_helper_l10n instance for component governing the type
     *
     */
    function &get_component_l10n()
    {
        // Use cache if we have it
        if (!isset($GLOBALS['midcom_helper_reflector_get_component_l10n_cache']))
        {
            $GLOBALS['midcom_helper_reflector_get_component_l10n_cache'] = array();
        }
        if (isset($GLOBALS['midcom_helper_reflector_get_component_l10n_cache'][$this->mgdschema_class]))
        {
            return $GLOBALS['midcom_helper_reflector_get_component_l10n_cache'][$this->mgdschema_class];
        }
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("Trying to resolve good l10n for type {$this->mgdschema_class}");
        $midcom_dba_classname = $_MIDCOM->dbclassloader->get_midcom_class_name_for_mgdschema_object($this->_dummy_object);
        if (empty($midcom_dba_classname))
        {
            // Could not resolve MidCOM DBA class name, fallback early to our own l10n
            debug_add("Could not get MidCOM DBA classname for type {$this->mgdschema_class}, using our own l10n", MIDCOM_LOG_INFO);
            debug_pop();
            $GLOBALS['midcom_helper_reflector_get_component_l10n_cache'][$this->mgdschema_class] = $this->_l10n;
            return $this->_l10n;
        }
        if (   !isset($_MIDCOM->dbclassloader->mgdschema_class_handler[$midcom_dba_classname])
            || empty($_MIDCOM->dbclassloader->mgdschema_class_handler[$midcom_dba_classname]))
        {
            // Cannot resolve component, fallback early to our own l10n
            debug_add("Could not resolve component for DBA class {$midcom_dba_classname}, using our own l10n", MIDCOM_LOG_INFO);
            debug_pop();
            $GLOBALS['midcom_helper_reflector_get_component_l10n_cache'][$this->mgdschema_class] = $this->_l10n;
            return $this->_l10n;
        }
        // Got component, try to load the l10n helper for it
        $component = $_MIDCOM->dbclassloader->mgdschema_class_handler[$midcom_dba_classname];
        debug_add("Class {$midcom_dba_classname} is handled by component {$component}");
        $midcom_i18n = $_MIDCOM->get_service('i18n');
        $component_l10n = $midcom_i18n->get_l10n($component);
        if (!empty($component_l10n))
        {
            debug_add("Got l10n handler for component {$component}, returning that");
            debug_pop();
            $GLOBALS['midcom_helper_reflector_get_component_l10n_cache'][$this->mgdschema_class] =& $component_l10n;
            return $component_l10n;
        }

        // Could not get anything else, use our own l10n
        debug_add("Everything else failed, using our own l10n for type {$this->mgdschema_class}", MIDCOM_LOG_WARN);
        debug_pop();
        $GLOBALS['midcom_helper_reflector_get_component_l10n_cache'][$this->mgdschema_class] = $this->_l10n;
        return $this->_l10n;
    }

    function get_class_label()
    {

        static $component_l10n = false;
        $component_l10n = $this->get_component_l10n();
        $use_classname = $this->mgdschema_class;
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
            $use_label = preg_replace('/(openpsa|database|positioning|notifications|statusmessage)_/', '', implode('_', $classname_parts));
            $use_label = ucwords(str_replace("_"," ",$use_label));
            $label = $component_l10n->get($use_label);
            if ($use_label == $label)
            {
                $this->get_class_label_l10n_ok = false;
            }
        }
        debug_pop();
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
        if (   !isset($this->mgdschema_class)
            || empty($this->mgdschema_class)
            || !class_exists($this->mgdschema_class))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('May not be called statically', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }

        debug_push_class(__CLASS__, __FUNCTION__);
        $obj = new $this->mgdschema_class;
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

            case (method_exists($obj,'get_label_property')):
                $label = $obj->get_label();
                break;
            case (is_a($obj, 'midgard_topic')):
                $property = 'extra';
                break;
            case (is_a($obj, 'midgard_person')):
                $property = array
                (
                    'rname',
                    'username',
                    'id',
                );
                break;
            case (array_key_exists('title', $properties)):
                $property = 'title';
                break;
            case (array_key_exists('name', $properties)):
                $property = 'name';
                break;
            default:
                $property = 'guid';
        }
        debug_pop();
        return $property;
    }

    function get_object_label(&$obj)
    {
        // Check against static calling
        if (   !isset($this->mgdschema_class)
            || empty($this->mgdschema_class))
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
            case (method_exists($obj,'get_label')):
                $label = $obj->get_label();
                break;
            case (is_a($obj, 'midgard_person')):
                if ($obj->rname)
                {
                    $label = $obj->rname;
                }
                else
                {
                    $label = $obj->username;
                }
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
            case (is_a($obj, 'midgard_event')):
            case (is_a($obj, 'org_openpsa_event')):
                if ($obj->start == 0)
                {
                    $label = $obj->title;
                }
                else
                {
                    $label = strftime('%x', $obj->start) . " {$obj->title}";
                }
                break;
            case (is_a($obj, 'midgard_eventmember')):
                $person = new midcom_db_person($obj->uid);
                $event = new midcom_db_event($obj->eid);
                $label = sprintf($_MIDCOM->i18n->get_string('%s in %s', 'midcom'), $person->name, $event->title);
                break;
            case (is_a($obj, 'midgard_member')):
                $person = new midcom_db_person($obj->uid);
                $grp = new midcom_db_group($obj->gid);
                $label = sprintf($_MIDCOM->i18n->get_string('%s in %s', 'midcom'), $person->name, $grp->official);
                break;
            case (is_a($obj, 'midgard_host')):
                if (   $obj->port
                    && $obj->port != '80')
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
            case (array_key_exists('name', $properties)):
                $label = $obj->name;
                break;
            default:
                $label = $obj->guid;
        }
        debug_pop();
        return $label;
    }

    function get_create_icon($type)
    {
        static $config_icon_map = array();
        if (empty($config_icon_map))
        {
            $icons2classes = $this->_config->get('create_type_magic');
            //sanity
            if (!is_array($icons2classes))
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add('Config key "create_type_magic" is not an array', MIDCOM_LOG_ERROR);
                debug_print_r("\$this->_config->get('create_type_magic')", $icons2classes, MIDCOM_LOG_INFO);
                debug_pop();
                unset($icons2classes);
            }
            else
            {
                foreach ($icons2classes as $icon => $classes)
                {
                    foreach ($classes as $class)
                    {
                        $config_icon_map[$class] = $icon;
                    }
                }
                unset($icons2classes, $classes, $class, $icon);
            }
        }

        $icon_callback = array($type, 'get_create_icon');
        switch (true)
        {
            // class has static method to tell us the answer ? great !
            case (is_callable($icon_callback)):
                $icon = call_user_func($icon_callback);
            // configuration icon
            case (isset($config_icon_map[$type])):
                $icon = $config_icon_map[$type];
                break;

            // heuristics magic (in stead of adding something here, take a look at config key "create_type_magic")
            case (strpos($type, 'member') !== false):
            case (strpos($type, 'organization') !== false):
                $icon = 'stock_people-new.png';
                break;
            case (strpos($type, 'person') !== false):
            case (strpos($type, 'member') !== false):
                $icon = 'stock_person-new.png';
                break;
            case (strpos($type, 'event') !== false):
                $icon = 'stock_event_new.png';
                break;

            // Config default value 
            case (isset($config_icon_map['__default__'])):
                $icon = $config_icon_map['__default__'];
                break;
            // Fallback default value
            default:
                $icon = 'new-text.png';
                break;
        }
        return $icon;
    }

    function get_object_icon(&$obj)
    {
        static $config_icon_map = array();
        if (empty($config_icon_map))
        {
            $icons2classes = $this->_config->get('object_icon_magic');
            //sanity
            if (!is_array($icons2classes))
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add('Config key "object_icon_magic" is not an array', MIDCOM_LOG_ERROR);
                debug_print_r("\$this->_config->get('object_icon_magic')", $icons2classes, MIDCOM_LOG_INFO);
                debug_pop();
                unset($icons2classes);
            }
            else
            {
                foreach ($icons2classes as $icon => $classes)
                {
                    foreach ($classes as $class)
                    {
                        $config_icon_map[$class] = $icon;
                    }
                }
                unset($icons2classes, $classes, $class, $icon);
            }
        }

        $object_class = get_class($obj);
        $object_baseclass = midcom_helper_reflector::resolve_baseclass($object_class);
        switch(true)
        {
            // object knows it's icon, how handy!
            case (method_exists($obj,'get_icon')):
                $icon = $obj->get_icon();
                break;

            // configuration icon
            case (isset($config_icon_map[$object_class])):
                $icon = $config_icon_map[$object_class];
                break;
            case (isset($config_icon_map[$object_baseclass])):
                $icon = $config_icon_map[$object_baseclass];
                break;
            
            // heuristics magic (in stead of adding something here, take a look at config key "object_icon_magic")
            case (strpos($object_class, 'person') !== false):
                $icon = 'stock_person.png';
                break;
            case (strpos($object_class, 'event') !== false):
                $icon='stock_event.png';
                break;
            case (strpos($object_class, 'member') !== false):
            case (strpos($object_class, 'organization') !== false):
                $icon='stock_people.png';
                break;
            case (is_a($obj, 'midgard_host')):
                $icon='stock_internet.png';
                break;
            case (is_a($obj, 'midgard_snippet')):
                $icon='script.png';
                break;
            case (strpos($object_class, 'element') !== false):
                $icon = 'text-x-generic-template.png';
                break;

            // Config default value 
            case (isset($config_icon_map['__default__'])):
                $icon = $config_icon_map['__default__'];
                break;
            // Fallback default value
            default:
                $icon = 'document.png';
                break;
        }

        // TODO: What if the icon is not in stock-icons/16x16 ?? especially the ->get_icon should probably be able to specify components static path
        $icon = "<img src='" . MIDCOM_STATIC_URL . "/stock-icons/16x16/{$icon}' align='absmiddle' border='0' alt='{$object_class}'/> ";
        debug_pop();
        return $icon;
    }

    /**
     * Get headers to be used with chooser
     */
    function get_result_headers()
    {
        $headers = array();
        $properties = $this->get_search_properties();
        foreach ($properties as $property)
        {
            $headers[] = array
            (
                'name' => $property,
                'title' => ucfirst($this->_l10n->get($property)),
            );
        }
        return $headers;
    }

    /**
     * Get class properties to use as search fields in univeralchooser etc
     *
     * @return array of property names
     */
    function get_search_properties()
    {
        // Check against static calling
        if (   !isset($this->mgdschema_class)
            || empty($this->mgdschema_class))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('May not be called statically', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }

        // Return cached results if we have them
        static $cache = array();
        if (isset($cache[$this->mgdschema_class]))
        {
            return $cache[$this->mgdschema_class];
        }
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("Starting analysis for class {$this->mgdschema_class}");
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
        if (isset($always_search_all[$this->mgdschema_class]))
        {
            $always_search = $always_search_all[$this->mgdschema_class];
        }
        foreach ($always_search as $property)
        {
            if (!array_key_exists($property, $properties))
            {
                debug_add("Property '{$property}' is set as always search, but is not a property in class '{$this->mgdschema_class}'", MIDCOM_LOG_WARN);
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
        if (isset($never_search_all[$this->mgdschema_class]))
        {
            $never_search = $never_search_all[$this->mgdschema_class];
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

        debug_print_r("Search properties for {$this->mgdschema_class}: ", $search_properties);
        debug_pop();
        $cache[$this->mgdschema_class] = $search_properties;
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
        if (   !isset($this->mgdschema_class)
            || empty($this->mgdschema_class))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('May not be called statically', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }

        // Return cached results if we have them
        static $cache = array();
        if (isset($cache[$this->mgdschema_class]))
        {
            return $cache[$this->mgdschema_class];
        }
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("Starting analysis for class {$this->mgdschema_class}");

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

        debug_print_r("Links for {$this->mgdschema_class}: ", $links);
        debug_pop();
        $cache[$this->mgdschema_class] = $links;
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
            $extends = $GLOBALS['midcom_component_data']['midcom.helper.reflector']['config']->get('class_extends');
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
        $one = midcom_helper_reflector::resolve_baseclass($class_one);
        $two = midcom_helper_reflector::resolve_baseclass($class_two);
        if ($one == $two)
        {
            return true;
        }
        if (midcom_helper_reflector::class_rewrite($one) == $two)
        {
            return true;
        }
        if ($one == midcom_helper_reflector::class_rewrite($two))
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
    static public function resolve_baseclass($classname)
    {
        $parent = $classname;
        do
        {
            $baseclass = $parent;
            $parent = get_parent_class($baseclass);
            
            if (empty($parent))
            {
                break;
            }
        }
        while ($parent !== false);
        return $baseclass;
    }
    
    /**
     * Copy an object tree. Usage:
     * 
     * - Choose the source and target for copying the object
     * - It's possible to pass exlusion list, which will stop the tree being copied from that point onwards.
     *   Exclusion list shall be an array of GUIDs that will not be copied.
     * 
     * @static
     * @access public
     * @param mixed $source        GUID or MgdSchema object that will be copied
     * @param mixed $target        Array with the given details or ID of 'up' field
     * @param array $exclude       IDs that will be excluded from the copying
     * @param boolean $parameters  Switch to determine if the parameters should be copied
     * @param boolean $metadata    Switch to determine if the metadata should be copied (excluding created and published)
     * @return mixed               False on failure, newly created MgdSchema root object on success
     */
    static public function copy_object_tree($source, $target, $exclude = array(), $parameters = true, $metadata = true, $root_object = null)
    {
        // Copy the root object
        if (!$root_object)
        {
            $root_object = midcom_helper_reflector::copy_object($source, $target, $parameters, $metadata);
            
            // Check if the copying was successful
            if (   !$root_object
                || !$root_object->guid)
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Failed to copy the root object of the tree, this is fatal.');
                // This will exit
            }
            
            // Get the reflector for the parent, which will contain all the created objects
            $reflector = new midcom_helper_reflector($root_object);
            $label = $reflector->get_label_property();
            $properties = $reflector->get_link_properties();
            
            // Get the parent property
            $mgdschema_class = midcom_helper_reflector::resolve_baseclass($root_object);
            $mgdschema_object = new $mgdschema_class();
            
            $parent_property = midgard_object_class::get_property_parent($mgdschema_class);
            
            // Get the parent property
            if ($parent_property)
            {
                $target['id'] = $root_object->$parent_property;
            }
            elseif (   isset($properties['up'])
                    && isset($properties['up']['class']))
            {
                $parent_property = 'up';
            }
            else
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Could not get the parent details');
                // This will exit
            }
            
            $target['id'] = $root_object->$parent_property;
        }
        
        $siblings = midcom_helper_reflector_tree::get_child_objects($source);
        
        // No siblings found, return to the previous state
        if (   !is_array($siblings)
            || count($siblings) === 0)
        {
            return $root_object;
        }
        
        // Loop through the siblings and generate a copy of each
        foreach ($siblings as $type => $children)
        {
            // Get the reflector for each different type
            $reflector = new midcom_helper_reflector($children[0]);
            $label = $reflector->get_label_property();
            $properties = $reflector->get_link_properties();
            
            // Get the parent property
            $mgdschema_class = midcom_helper_reflector::resolve_baseclass($children[0]);
            $mgdschema_object = new $mgdschema_class();
            
            $parent_property = midgard_object_class::get_property_parent($mgdschema_class);
            
            // Get the parent property
            if ($parent_property)
            {
                $target['parent'] = $parent_property;
                $target['class'] = $properties[$parent_property]['class'];
            }
            elseif (   isset($properties['up'])
                    && isset($properties['up']['class']))
            {
                $target['parent'] = 'up';
                $target['class'] = $properties['up']['class'];
            }
            else
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Could not get the parent details');
                // This will exit
            }
            
            // Stop the execution if parent property is not available
            if (   !isset($children[0]->$parent_property)
                || !$children[0]->$parent_property)
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_print_r("Failed to get the parent property for a children[0]", $children[0], MIDCOM_LOG_ERROR);
                debug_add("The parent property was {$parent}", MIDCOM_LOG_ERROR);
                debug_pop();
                
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Failed to copy the tree, see error level log for details');
            }
            
            // Loop through the children and generate a copy of each
            foreach ($children as $child)
            {
                // This object is in the exlusion list, skip it.
                if (in_array($child->guid, $exclude))
                {
                    debug_push_class(__CLASS__, __FUNCTION__);
                    debug_add("Object {$child->guid} ({$type}) was in the exclusion list, no copying allowed.", MIDCOM_LOG_INFO);
                    debug_pop();
                    continue;
                }
                
                // Get the parent value
                $parent = $target['parent'];
                
                // Get the object required type information
                $object = midcom_helper_reflector::copy_object($child, $target, $parameters, $metadata);
                
                if (   !$object
                    || !$object->guid)
                {
                    $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Failed to copy an object in the tree, see error level log for details');
                }
                
                $target['id'] = $object->$parent;
                
                // Check if the current object has its own children
                midcom_helper_reflector::copy_object_tree($child, $target, $exclude, $parameters, $metadata, $root_object);
            }
        }
        
        return $root_object;
    }
    
    /**
     * Copy an object
     *
     * @static
     * @access public
     * @param mixed $source        GUID or MgdSchema object that will be copied
     * @param mixed $target        Array with the given details or ID of 'up' field
     * @param boolean $parameters  Switch to determine if the parameters should be copied
     * @param boolean $metadata    Switch to determine if the metadata should be copied (excluding created and published)
     * @return mixed               False on failure, newly created MgdSchema object on success
     */
    static public function copy_object($source, $target, $parameters = true, $metadata = true)
    {
        if (!is_object($source))
        {
            $source_object = $_MIDCOM->dbfactory->get_object_by_guid($source);
        }
        else
        {
            $source_object =& $source;
        }
        
        // Get the property and id for the future owner
        if (is_array($target))
        {
            // Check the validity of the object
            if (   !array_key_exists('id', $target)
                || !array_key_exists('parent', $target))
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Wrong up arguments passed for object copy');
                // This will exit
            }
            
            $up_link = $target['id'];
            $up_property = $target['parent'];
        }
        else
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('Target not properly formatted, trying the default settings');
            debug_pop();
            
            $up_link = $target;
            $up_property = 'up';
        }
        
        // Copied metadata properties
        $metadata_fields = array
        (
            'score',
            'owner',
            'authors',
            'schedule_start',
            'schedule_end',
            'hidden',
            'nav_noentry',
        );
        
        // Create a new object
        $new_class_name = get_class($source_object);
        $new_object = new $new_class_name();
        
        // Copy the keys
        foreach (array_keys(get_object_vars($source_object)) as $key)
        {
            // Skip fields that may not be copied in any case
            if (   $key === 'id'
                || $key === 'guid')
            {
                continue;
            }
            
            $new_object->$key = $source_object->$key;
        }
        
        // Copy the metadata
        if ($metadata)
        {
            // Copy only the specified fields
            foreach ($metadata_fields as $key)
            {
                if (!isset($source_object->metadata->$key))
                {
                    continue;
                }
                
                $new_object->metadata->$key = $source_object->metadata->$key;
            }
        }
        
        $new_object->$up_property = $up_link;
        
        if (!$new_object->create())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_print_r('Failed to create a new object', $new_object, MIDCOM_LOG_ERROR);
            debug_pop();
            
            return false;
        }
        
        // Copy the parameters
        if ($parameters)
        {
            // Get the domain name first
            foreach ($source_object->list_parameters() as $domain => $array)
            {
                // Get the name and value fields
                foreach ($array as $name => $value)
                {
                    $new_object->set_parameter($domain, $name, $value);
                }
            }
        }
        
        return $new_object;
    }
}

/**
 * I hope we don't need these workarounds but in case we do, keep them handy
function midcom_helper_reflector_get_property_parent(&$src)
{
    return midgard_object_class::get_property_parent($src);
}

function midcom_helper_reflector_get_property_up(&$src)
{
    return midgard_object_class::get_property_up($src);
}
*/
?>