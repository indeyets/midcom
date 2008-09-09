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
    function __construct($src)
    {
        $this->_component = 'midcom.helper.reflector';
        parent::__construct();
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
     * @return midcom_services__i18n_l10n  Localization library for the reflector object class
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
        $midcom_dba_classname = $_MIDCOM->dbclassloader->get_midcom_class_name_for_mgdschema_object($this->_dummy_object);
        if (empty($midcom_dba_classname))
        {
            // Could not resolve MidCOM DBA class name, fallback early to our own l10n
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Could not get MidCOM DBA classname for type {$this->mgdschema_class}, using our own l10n", MIDCOM_LOG_INFO);
            debug_pop();
            $GLOBALS['midcom_helper_reflector_get_component_l10n_cache'][$this->mgdschema_class] = $this->_l10n;
            return $this->_l10n;
        }
        if (   !isset($_MIDCOM->dbclassloader->mgdschema_class_handler[$midcom_dba_classname])
            || empty($_MIDCOM->dbclassloader->mgdschema_class_handler[$midcom_dba_classname]))
        {
            // Cannot resolve component, fallback early to our own l10n
            debug_push_class(__CLASS__, __FUNCTION__);
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
            $GLOBALS['midcom_helper_reflector_get_component_l10n_cache'][$this->mgdschema_class] =& $component_l10n;
            return $component_l10n;
        }

        // Could not get anything else, use our own l10n
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("Everything else failed, using our own l10n for type {$this->mgdschema_class}", MIDCOM_LOG_WARN);
        debug_pop();
        
        $GLOBALS['midcom_helper_reflector_get_component_l10n_cache'][$this->mgdschema_class] = $this->_l10n;
        return $this->_l10n;
    }
    
    /**
     * Get the localized label of the class
     * 
     * @access public
     * @return string Class label
     */
    public function get_class_label()
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

    /**
     * Get the object label property value
     * 
     * @access public
     * @param mixed $obj    MgdSchema object
     * @return String       Label of the object
     */
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

    /**
     * Get the name of the create icon image
     * 
     * @static
     * @access public
     * @param string $type  Name of the type
     * @return string       URL name of the image
     */
    static public function get_create_icon($type)
    {
        static $config = null;
        static $config_icon_map = array();
        
        // Get the component configuration
        if (is_null($config))
        {
            $config =& $GLOBALS['midcom_component_data']['midcom.helper.reflector']['config'];
        }
        
        if (empty($config_icon_map))
        {
            $icons2classes = $config->get('create_type_magic');
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

    /**
     * Get the name of the icon image
     * 
     * @static
     * @access public
     * @param mixed $obj          MgdSchema object
     * @param boolean $url_only   Get only the URL location instead of full <img /> tag
     * @return string             URL name of the image
     */
    static public function get_object_icon(&$obj, $url_only = false)
    {
        static $config = null;
        static $config_icon_map = array();
        
        // Get the component configuration
        if (is_null($config))
        {
            $config =& $GLOBALS['midcom_component_data']['midcom.helper.reflector']['config'];
        }
        
        if (empty($config_icon_map))
        {
            $icons2classes = $config->get('object_icon_magic');
            //sanity
            if (!is_array($icons2classes))
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add('Config key "object_icon_magic" is not an array', MIDCOM_LOG_ERROR);
                debug_print_r("\$config->get('object_icon_magic')", $icons2classes, MIDCOM_LOG_INFO);
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
        if ($url_only)
        {
            $icon = MIDCOM_STATIC_URL . "/stock-icons/16x16/{$icon}";
        }
        else
        {
            $icon = "<img src=\"" . MIDCOM_STATIC_URL . "/stock-icons/16x16/{$icon}\" align=\"absmiddle\" border=\"0\" alt=\"{$object_class}\" /> ";
        }
        
        debug_pop();
        return $icon;
    }

    /**
     * Get headers to be used with chooser
     * 
     * @access public
     * @return array
     */
    public function get_result_headers()
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
    public function get_search_properties()
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
    public function get_link_properties()
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
     * Get an object, deleted or not
     * 
     * @static
     * @access public
     * @param string $guid    GUID of the object
     * @param string $type    MgdSchema type
     * @return mixed          MgdSchema object
     */
    static public function get_object($guid, $type)
    {
        static $objects = array();

        if (!isset($objects[$guid]))
        {
            $qb = new midgard_query_builder($type);
            $qb->add_constraint('guid', '=', $guid);
            $qb->include_deleted();
            $results = $qb->execute();
            if (count($results) == 0)
            {
                $objects[$guid] = null;
            }
            else
            {
                $objects[$guid] = $results[0];
            }
        }

        return $objects[$guid];
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
     * Copy an object tree. Both source and parent may be liberally filled. Source can be either
     * MgdSchema or MidCOM db object or GUID of the object and parent can be
     * 
     * - MgdSchema object
     * - MidCOM db object
     * - predefined target array (@see get_target_properties())
     * - ID or GUID of the object
     * - left empty to copy as a parentless object
     * 
     * This method is self-aware and will refuse to perform any infinite loops (e.g. to copy
     * itself to its descendant, copying itself again and again and again).
     * 
     * Eventually this method will return the first root object that was created, i.e. the root
     * of the new tree.
     *
     * @static
     * @access public
     * @param mixed $source        GUID or MgdSchema object that will be copied
     * @param mixed $parent        MgdSchema or MidCOM db object, predefined array or ID of the parent object
     * @param array $exclude       IDs that will be excluded from the copying
     * @param boolean $parameters  Switch to determine if the parameters should be copied
     * @param boolean $metadata    Switch to determine if the metadata should be copied (excluding created and published)
     * @param boolean $attachments Switch to determine if the attachments should be copied (creates only a new link, doesn't duplicate the content)
     * @return mixed               False on failure, newly created MgdSchema root object on success
     */
    static public function copy_object_tree($source, $parent, $exclude = array(), $parameters = true, $metadata = true, $attachments = true)
    {
        // Copy the root object
        $root = midcom_helper_reflector::copy_object($source, $parent, $parameters, $metadata);
        
        // Add the newly copied object to the exclusion list to prevent infinite loops
        $exclude[] = $root->guid;
        
        // Get the children
        $children = midcom_helper_reflector_tree::get_child_objects($source);
        
        if (   !$children
            || count($children) === 0)
        {
            return $root;
        }
        
        // Loop through the children and copy them to their corresponding parents
        foreach ($children as $type => $children)
        {
            // Get the children of each type
            foreach ($children as $child)
            {
                // Skip the excluded child
                if (in_array($child->guid, $exclude))
                {
                    continue;
                }
                
                midcom_helper_reflector::copy_object_tree($child, $root, $exclude, $parameters, $metadata, $attachments);
            }
        }
        
        // Return the newly created root object
        return $root;
    }
    
    /**
     * Copy an object. Both source and parent may be liberally filled. Source can be either
     * MgdSchema or MidCOM db object or GUID of the object and parent can be
     * 
     * - MgdSchema object
     * - MidCOM db object
     * - predefined target array (@see get_target_properties())
     * - ID or GUID of the object
     * - left empty to copy as a parentless object
     *
     * @static
     * @access public
     * @param mixed $source        GUID or MgdSchema object that will be copied
     * @param mixed $parent        MgdSchema or MidCOM db object, predefined array or ID of the parent object
     * @param boolean $parameters  Switch to determine if the parameters should be copied
     * @param boolean $metadata    Switch to determine if the metadata should be copied (excluding created and published)
     * @param boolean $attachments Switch to determine if the attachments should be copied (creates only a new link, doesn't duplicate the content)
     * @return mixed               False on failure, newly created MgdSchema object on success
     */
    static public function copy_object($source, $parent, $parameters = true, $metadata = true, $attachments = true)
    {
        // Get the baseclass of the object
        if (is_object($source))
        {
            switch (true)
            {
                // This is a MidCOM db object
                case $_MIDCOM->dbclassloader->is_midcom_db_object($source):
                    $source_object =& $source;
                    break;
                
                // This is a MgdSchema object
                case $_MIDCOM->dbclassloader->is_legacy_midgard_object($source):
                    $midcom_dba_classname = $_MIDCOM->dbclassloader->get_midcom_class_name_for_mgdschema_object($source);
                    $source_object = new $midcom_dba_classname($source->guid);
                    break;
                
                // Unable to determine, force the result out
                default:
                    // Get the MidCOM dba classname for the element
                    $classname = midcom_helper_reflector::resolve_baseclass(get_class($source));
                    $temp = new $classname($source->guid);
                    
                    $midcom_dba_classname = $_MIDCOM->dbclassloader->get_midcom_class_name_for_mgdschema_object($source_object);
                    $source_object = new $midcom_dba_classname($source->guid);
            }
        }
        else
        {
            $source_object = $_MIDCOM->dbfactory->get_object_by_guid($source);
        }
        
        // Check the source object validity
        if (   !$source_object
            || !$source_object->guid)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Failed to get the source by GUID even though mgd_is_guid returned true", MIDCOM_LOG_ERROR);
            debug_pop();
            
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Unexpected error: unable to get the source object');
        }
        
        // Get the MgdSchema class
        $mgdschema_class = midcom_helper_reflector::resolve_baseclass(get_class($source_object));
        static $targets = array();
        
        // Try to get the cached properties
        if (isset($targets[$mgdschema_class]))
        {
            $properties = $targets[$mgdschema_class];
        }
        else
        {
            $properties = midcom_helper_reflector::get_target_properties($source_object);
        }
        
        // Check the type of the requested parent
        switch (true)
        {
            case is_object($parent):
                $parent_object =& $parent;
                break;
            case mgd_is_guid($parent):
                $parent_object = $_MIDCOM->dbfactory->get_object_by_guid($parent);
                break;
            case is_array($parent):
                // parent properties were already parsed, skip this phase
                $properties = $parent;
                $mgdschema_class = $properties['class'];
                $parent_object = new $mgdschema_class($properties['id']);
                break;
            default:
                $mgdschema_class = $properties['class'];
                $parent_object = new $mgdschema_class($parent);
        }        
        
        // Duplicate the object
        $class_name = get_class($source_object);
        $target = new $class_name();
        
        // Copy the object properties
        foreach (array_keys(get_object_vars($source_object)) as $property)
        {
            // Skip certain fields
            if (preg_match('/^(_|metadata|guid|id)/', $property))
            {
                continue;
            }
            
            $target->$property = $source_object->$property;
        }
        
        // Copy to the requested target
        if (isset($properties['parent']))
        {
            $property = $properties['parent'];
            $target->$property = $parent_object->id;
        }
        
        // Copy the requested metadata
        if ($metadata)
        {
            // Copied metadata fields
            $skip_fields = array
            (
                'locker',
                'locked',
                'revision',
                'approver',
                'approved',
                'size',
            );
            
            foreach (array_keys(get_object_vars($source_object->metadata)) as $property)
            {
                if (   in_array($property, $skip_fields)
                    || !isset($target->metadata->$property))
                {
                    continue;
                }
                
                $target->metadata->$property = $source_object->metadata->$property;
            }
        }
        
        // Attachments special case
        if (is_a($target, 'midcom_baseclasses_database_attachment'))
        {
            $target->_duplicate = true;
        }
        
        if (!$target->create())
        {
            // Copying failed
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_print_r('Failed to create this object:', $target, MIDCOM_LOG_ERROR);
            debug_add('Last Midgard error was ' . mgd_errstr(), MIDCOM_LOG_ERROR);
            debug_pop();
            
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Failed to copy the object. See error level log for details');
            // This will exit
        }
        
        // Copy the parameters
        if ($parameters)
        {
            foreach ($source_object->list_parameters() as $domain => $array)
            {
                foreach ($array as $name => $value)
                {
                    $target->set_parameter($domain, $name, $value);
                }
            }
        }
        
        if ($attachments)
        {
            foreach ($source_object->list_attachments() as $attachment)
            {
                $duplicate = midcom_helper_reflector::copy_object($attachment, $target, $parameters, $metadata, false);
                $duplicate->parentguid = $target->guid;
                $duplicate->update();
            }
        }
        
        return $target;
    }
    
    /**
     * Get the target properties and return an array that is used e.g. in copying
     * 
     * @static
     * @access public
     * @param mixed $object     MgdSchema object or MidCOM db object
     * @return array            id, parent property, class and label of the object
     */
    static public function get_target_properties($object)
    {
        $mgdschema_class = midcom_helper_reflector::resolve_baseclass(get_class($object));
        $mgdschema_object = new $mgdschema_class($object->guid);
        
        static $targets = array();
        
        // Return the cached results
        if (isset($targets[$mgdschema_class]))
        {
            return $targets[$mgdschema_class];
        }
        
        // Empty result set for the current class
        $target = array
        (
            'id' => null,
            'parent' => '',
            'class' => $mgdschema_class,
            'label' => '',
            'reflector' => new midcom_helper_reflector($object),
        );
        
        // Try to get the parent property for determining, which property should be
        // used to point the parent of the new object. Attachments are a special case.
        if (!is_a($object, 'midcom_baseclasses_database_attachment'))
        {
            $parent_property = midgard_object_class::get_property_parent($mgdschema_object);
        }
        else
        {
            $parent_property = 'parentobject';
        }
        
        // Get the class label
        $target['label'] = $target['reflector']->get_label_property();
        
        // Try once more to get the parent property, but now try up as a backup
        if (!$parent_property)
        {
            $up_property = midgard_object_class::get_property_up($mgdschema_object);
            
            if (!$up_property)
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Failed to get the parent property for copying');
            }
            
            $target['parent'] = $up_property;
        }
        else
        {
            $target['parent'] = $parent_property;
        }
        
        // Cache the results
        $targets[$mgdschema_class] = $target;
        return $targets[$mgdschema_class];
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