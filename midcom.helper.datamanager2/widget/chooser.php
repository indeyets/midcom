<?php
/**
 * @package midcom.helper.datamanager2
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: textarea.php 10966 2007-06-15 07:00:37Z adrenalin $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Datamanager 2 Chooser widget
 *
 * As with all subclasses, the actual initialization is done in the initialize() funciton,
 * not in the constructor, to allow for error handling.
 *
 * It can only be bound to a select type (or subclass thereoff), and inherits the configuration
 * from there as far as possible.
 *
 * <b>Available configuration options:</b>
 *
 *
 * Example: (The simplest ones)
'contacts' => Array
(
    'title' => 'contacts',
    'storage' => null,
    'type' => 'select',
    'type_config' => array
    (
          'require_corresponding_option' => false,
         'allow_multiple' => true,
         'multiple_storagemode' => 'array',
    ),
    'widget' => 'chooser',
    'widget_config' => array
    (
        'clever_class' => 'contact',
    ),
),
 * OR
 'buddies' => Array
 (
     'title' => 'buddies',
     'storage' => null,
     'type' => 'select',
     'type_config' => array
     (
          'require_corresponding_option' => false,
          'allow_multiple' => true,
          'multiple_storagemode' => 'array',
     ),
     'widget' => 'chooser',
     'widget_config' => array
     (
         'clever_class' => 'buddy',
     ),
 ),
 * OR
 'styles' => Array
 (
     'title' => 'styles',
     'storage' => null,
     'type' => 'select',
     'type_config' => array
     (
          'require_corresponding_option' => false,
          'allow_multiple' => true,
          'multiple_storagemode' => 'array',
     ),
     'widget' => 'chooser',
     'widget_config' => array
     (
         'clever_class' => 'style',
     ),
 ),
 *
 * @package midcom.helper.datamanager2
 */
class midcom_helper_datamanager2_widget_chooser extends midcom_helper_datamanager2_widget
{
    /**
     * id of the input element
     *
     * @var String
     * @access private
     */    
    var $_element_id = "chooser_widget";
    
    /**
     * Array of options that are passed to javascript widget
     *
     * @var Array
     * @access private
     */
    var $_js_widget_options = array();

    var $_input_element = null;

    /**
     * Class to search for
     *
     * @var string
     */
    var $class = null;

    /**
     * Which component the searched class belongs to
     *
     * @var string
     */
    var $component = null;
    
    /**
     * Clever class
     *
     * @var string
     */
    var $clever_class = null;

    /**
     * Associative array of constraints (besides the search term), always AND
     *
     * Example:
     *     'constraints' => array (
     *         array(
     *             'field' => 'username',
     *             'op' => '<>',
     *             'value' => '',
     *         ),
     *     ),
     *
     * @var array
     */
    var $constraints = array();

    /**
     * Fields/properties to show on results
     *
     * Example:
     *      'result_headers' => array('firstname', 'lastname'),
     *
     * @var array
     */
    var $result_headers = array();

    /**
     * Fields/properties to search the keyword for, always OR and specified after the constaints above
     *
     * Example:
     *      'searchfields' => array('firstname', 'lastname', 'email', 'username'),
     *
     * @var array
     */
    var $searchfields = array();
    
    /**
     * associative array of ordering info, always added last
     *
     * Example: 
     *     'orders' => array(array('lastname' => 'ASC'), array('firstname' => 'ASC')),
     *
     * @var array
     */
    var $orders = array();

    /**
     * Field/property to use as the key/id
     *
     * @var string
     */
    var $id_field = 'guid';

    /**
     * These options are always visible
     */
    var $static_options = array();

    /**
     * Whether to automatically append/prepend wildcards to the query
     * 
     * Valid values: 'both', 'start', 'end' and <empty> (0, '', false & null)
     *
     * Example: 
     *     'auto_wildcards' => 'both',
     *
     * $var string
     */
    var $auto_wildcards = 'end';
    
    /**
     * The javascript to append to the page
     *
     * @var string
     */    
    var $_jscript = '';
    
    /**
     * In case the options are returned by a callback, this member holds the class.
     *
     * @var class
     */    
    var $_callback = false;

    /**
     * In case the options are returned by a callback, this member holds the name of the
     * class.
     *
     * @var string
     * @access public
     */
    var $_callback_class = null;

    /**
     * The argument to pass to the option callback constructor.
     *
     * @var mixed
     * @access public
     */
    var $_callback_args = null;

    /**
     * Renderer
     *
     * @var mixed
     */    
    var $renderer = null;

    /**
     * Renderer callback
     *
     * @var class
     */    
    var $_renderer_callback = false;

    /**
     * Renderer callback class name
     *
     * @var string
     */    
    var $_renderer_callback_class = null;

    /**
     * Renderer callback arguments
     *
     * @var array
     */    
    var $_renderer_callback_args = array();

    /**
     * The group of widgets items as QuickForm elements
     */
    var $widget_elements = array();
    
    var $allow_multiple = true;
    
    var $reflector_key = null;

    var $creation_mode_enabled = null;
    var $creation_handler = null;
    var $creation_default_key = null;
            
    /**
     * The initialization event handler post-processes the maxlength setting.
     *
     * @return bool Indicating Success
     */
    function _on_initialize()
    {
        if (   is_a('midcom_helper_datamanager2_type_select', $this->_type)
            || is_a('midcom_helper_datamanager2_type_mnrelation', $this->_type))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Warning, the field {$this->name} is not a select type or subclass thereoff, you cannot use the chooser widget with it.",
                MIDCOM_LOG_WARN);
            debug_pop();
            return false;
        }
        
        $this->_callback_class = $this->_type->option_callback;
        $this->_callback_args = $this->_type->option_callback_arg;
        
        $this->allow_multiple = $this->_type->allow_multiple;
        
        if (   empty($this->class)
            && empty($this->component)
            && empty($this->clever_class))
        {
            if (   !isset($this->_callback_class)
                || empty($this->_callback_class))
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("Warning, the field {$this->name} does not have proper class definitions set.",
                    MIDCOM_LOG_WARN);
                debug_pop();
            }
            return false;
        }
        
        if (   !empty($this->renderer)
            && !$this->_check_renderer())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Warning, the field {$this->name} renderer wasn't found or not set properly, thus widget can never show results.",
                MIDCOM_LOG_WARN);
            debug_pop();
            return false;
        }
        
        if (!$this->_check_class())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Warning, cannot load class {$this->class}.",
                MIDCOM_LOG_WARN);
            debug_pop();
            return false;
        }

        if (empty($this->class))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Warning, the field {$this->name} does not have class defined.",
                MIDCOM_LOG_WARN);
            debug_pop();
            return false;
        }

        if (empty($this->component))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Warning, the field {$this->name} does not have component the class {$this->class} belongs to defined.",
                MIDCOM_LOG_WARN);
            debug_pop();
            return false;
        }

        if (empty($this->searchfields))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Warning, the field {$this->name} does not have searchfields defined, it can never return results.",
                MIDCOM_LOG_WARN);
            debug_pop();
            return false;
        }
        
        $_MIDCOM->enable_jquery();

        $_MIDCOM->add_link_head(
            array(
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL . '/midcom.helper.datamanager2/chooser/jquery.chooser_widget.css'
            )
        );
        
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL . '/midcom.helper.datamanager2/chooser/jquery.chooser_widget.pack.js');
        
        $this->_element_id = "{$this->_namespace}{$this->name}_chooser_widget";

        if (! is_null($this->creation_handler))
        {
            $this->_enable_creation_mode();
        }

        $this->_init_widget_options();
        
        return true;
    }
    
    function _enable_creation_mode()
    {
        if (! empty($this->creation_handler))
        {
            $this->creation_mode_enabled = true;
        }
        
        if ($this->creation_mode_enabled)
        {
            $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL . '/midcom.helper.datamanager2/chooser/jquery.jqModal.pack.js');
            
            $script = "
                jQuery('#{$this->_element_id}_creation_dialog').jqm({
                    modal: false,
                    overlay: 40,
                    overlayClass: 'chooser_widget_creation_overlay'
                });
            ";
            
            $_MIDCOM->add_jquery_state_script($script);
        }
    }
    
    function _check_renderer()
    {
        if (!isset($this->renderer['class']))
        {
            return false;
        }

        $this->_renderer_callback_class = $this->renderer['class'];
        $this->_renderer_callback_args = array();
        if (isset($this->renderer['args'])
            && !empty($this->renderer['args']))
        {
            $this->_renderer_callback_args = $this->renderer['args'];            
        }
        
        if (! class_exists($this->_renderer_callback_class))
        {
            // Try auto-load.
            $path = MIDCOM_ROOT . '/' . str_replace('_', '/', $this->_renderer_callback_class) . '.php';
            if (! file_exists($path))
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("Auto-loading of the renderer callback class {$this->_renderer_callback_class} from {$path} failed: File does not exist.", MIDCOM_LOG_ERROR);
                debug_pop();
                return false;
            }
            require_once($path);
        }

        if (! class_exists($this->_renderer_callback_class))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("The renderer callback class {$this->_renderer_callback_class} was defined as option for the field {$this->name} but did not exist.", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        $this->_renderer_callback = new $this->_renderer_callback_class($this->_renderer_callback_args);
        
        return $this->_renderer_callback->initialize();
    }
    
    function _check_class()
    {
        if (!empty($this->clever_class))
        {
            return $this->_check_clever_class();
        }
        
        if (!empty($this->_callback_class))
        {
            return $this->_check_callback();
        }
        
        if (class_exists($this->class))
        {
            return true;
        }
        
        if (! empty($this->component))
        {
            $_MIDCOM->componentloader->load($this->component);
        }
        
        return class_exists($this->class);
    }
    
    function _check_callback()
    {
        // debug_push_class(__CLASS__, __FUNCTION__);        
        // debug_add("Checking callback class {$this->_callback_class}");
        
        if (! class_exists($this->_callback_class))
        {
            // Try auto-load.
            $path = MIDCOM_ROOT . '/' . str_replace('_', '/', $this->_callback_class) . '.php';
            if (! file_exists($path))
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("Auto-loading of the callback class {$this->_callback_class} from {$path} failed: File does not exist.", MIDCOM_LOG_ERROR);
                debug_pop();
                return false;
            }
            require_once($path);
        }

        if (! class_exists($this->_callback_class))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("The callback class {$this->_callback_class} was defined as option for the field {$this->name} but did not exist.", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        $this->_callback = new $this->_callback_class($this->_callback_args);
        
        // debug_pop();
        return $this->_callback->initialize();
    }
    
    function _check_clever_class()
    {
        // debug_push_class(__CLASS__, __FUNCTION__);
        
        $current_user = $_MIDCOM->auth->user->get_storage();
        
        $clever_classes = array
        (
            'buddy' => array
            (
                'class' => 'net_nehmer_buddylist_entry',
                'component' => 'net.nehmer.buddylist',
                'headers' => array
                (
                    'firstname',
                    'lastname',
                    'email',
                ),
                'constraints' => array
                (
                    array
                    (
                        'field' => 'account',
                        'op'    => '=',
                        'value' => $current_user->guid,
                    ),
                    array
                    (
                        'field' => 'blacklisted',
                        'op'    => '=',
                        'value' => false,
                    ),
                ),
                'searchfields' => array
                (
                    'buddy.firstname',
                    'buddy.lastname',
                    'buddy.username',
                ),
                'orders' => array
                (
                    array('buddy.lastname' => 'ASC'), 
                    array('buddy.firstname' => 'ASC'), 
                ),
                'reflector_key' => 'buddy',
            ),
            'contact' => array
            (
                'class' => 'org_openpsa_contacts_person',
                'component' => 'org.openpsa.contacts',
                'headers' => array
                (
                    'name',
                    'email',
                ),
                'constraints' => array
                (
                    array
                    (
                        'field' => 'username',
                        'op'    => '<>',
                        'value' => '',
                    ),
                ),
                'searchfields' => array
                (
                    'firstname',
                    'lastname',
                    'username',
                ),
                'orders' => array
                (
                    array('lastname' => 'ASC'), 
                    array('firstname' => 'ASC'), 
                ),
            ),
            'wikipage' => array
            (
                'class' => 'net_nemein_wiki_wikipage',
                'component' => 'net.nemein.wiki',
                'headers' => array
                (
                    'title',
                    'revised',
                ),
                'constraints' => array(),
                'searchfields' => array
                (
                    'title',
                ),
                'orders' => array
                (
                    array('title' => 'ASC'), 
                    array('metadata.published' => 'ASC'),
                ),
                'creation_default_key' => 'title',
            ),
            'article' => array
            (
                'class' => 'midcom_db_article',
                'component' => 'net.nehmer.static',
                'headers' => array
                (
                    'title',
                ),
                'constraints' => array(),
                'searchfields' => array
                (
                    'title'
                ),
                'orders' => array
                (
                    array('title' => 'ASC'), 
                    array('metadata.published' => 'ASC'),
                ),
                'id_field' => 'guid',
            ),
            'topic' => array
            (
                'class' => 'midcom_db_topic',
                'component' => 'midcom.admin.folder',
                'headers' => array
                (
                    'extra',
                    'component',
                ),
                'constraints' => array(),
                'searchfields' => array
                (
                    'extra',
                    'name',
                ),
                'orders' => array
                (
                    array('extra' => 'ASC'), 
                    array('metadata.published' => 'ASC'),
                ),
            ),
            'group' => array
            (
                'class' => 'midcom_db_group',
                'component' => 'midgard.admin.asgard',
                'headers' => array
                (
                    'name',
                    'official',
                ),
                'constraints' => array(),
                'searchfields' => array
                (
                    'name',
                    'official',
                ),
                'orders' => array
                (
                    array('extra' => 'ASC'), 
                    array('metadata.published' => 'ASC'),
                ),
                'id_field' => 'id',
            ),
        );
        
        if (array_key_exists($this->clever_class,$clever_classes))
        {
            // debug_add("clever class {$this->clever_class} found!");
            
            $this->class = $clever_classes[$this->clever_class]['class'];
            $this->component = $clever_classes[$this->clever_class]['component'];

            if (! empty($this->component))
            {
                $_MIDCOM->componentloader->load($this->component);
            }
            
            if (empty($this->result_headers))
            {
                $this->result_headers = array();
                foreach ($clever_classes[$this->clever_class]['headers'] as $header_key)
                {
                    $header = array();
                    $header['title'] = $this->_l10n_midcom->get($header_key);
                    $header['name'] = $header_key;
                    $this->result_headers[] = $header;
                }
            }
            if (empty($this->constraints))
            {
                $this->constraints = $clever_classes[$this->clever_class]['constraints'];
            }
            if (empty($this->searchfields))
            {
                $this->searchfields = $clever_classes[$this->clever_class]['searchfields'];
            }
            if (empty($this->orders))
            {
                $this->orders = $clever_classes[$this->clever_class]['orders'];
            }
            if (isset($clever_classes[$this->clever_class]['reflector_key']))
            {
                $this->reflector_key = $clever_classes[$this->clever_class]['reflector_key'];
            }
            if (isset($clever_classes[$this->clever_class]['id_field']))
            {
                $this->id_field = $clever_classes[$this->clever_class]['id_field'];
            }
                                                
            // debug_pop();
            return true;
        }
        else
        {
            // debug_add("clever class {$this->clever_class} not found in predefined list. Trying to use reflector");
            $_MIDCOM->componentloader->load_graceful('midgard.admin.asgard');
            
            $matching_type = false;
            $matched_types = array();
            foreach ($_MIDGARD['schema']['types'] as $schema_type => $dummy)
            {
                //debug_add("schema type: {$schema_type}");
                $pos = strpos($schema_type, $this->clever_class);        
                if ($pos !== false)
                {
                    // debug_add("found possible match: {$schema_type}");
                    $matched_types[] = $schema_type;
                }
            }
            
            // debug_print_r('$matched_types',$matched_types);
            
            if (count($matched_types) == 1)
            {
                $matching_type = $matched_types[0];
            }
            else
            {
                if ($this->clever_class == 'event')
                {
                    $matching_type = 'net_nemein_calendar_event_db';//'org_openpsa_event';
                    $this->creation_default_key = 'title';
                }
                else if ($this->clever_class == 'person')
                {
                    $matching_type = 'midgard_person';
                }
                else
                {
                    if (count($matched_types) > 0)
                    {
                        $matching_type = $matched_types[0];                        
                    }
                }
            }
            
            // debug_print_r('Decided to go with',$matching_type);
            
            if (! $matching_type)
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("no matches found for {$this->clever_class}!");
                debug_pop();
                return false;
            }

            $maa_reflector =& new midgard_admin_asgard_reflector($matching_type);
            $mgd_reflector = new midgard_reflection_property($matching_type);
                        
            $labels = array();
            
            $dummy_object = new $matching_type();
            $type_fields = array_keys(get_object_vars($dummy_object));     
            // debug_print_r('type_fields',$type_fields);
            
            unset($type_fields['metadata']);
            foreach ($type_fields as $key)
            {
                // debug_add("processing type field {$key}");
                if ($mgd_reflector->is_link($key))
                {
                    // debug_add("type field {$key} is link");
                }
                
                if (in_array($key, array('title','firstname','lastname','name','email','start','end','location')))
                {
                    if (! in_array($key, $labels))
                    {
                        $labels[] = $key;
                    }
                }
            }
            
            if (empty($labels))
            {
                $label_properties = $maa_reflector->get_label_property();
                // debug_print_r('$label_properties',$label_properties);
                if (is_array($label_properties))
                {
                    foreach ($label_properties as $key)
                    {
                        if (! in_array($key,array('id','guid')))
                        {
                            if (! in_array($key, $labels))
                            {
                                $labels[] = $key;
                            }
                        }
                    }
                }
            }
            
            $this->class = $_MIDCOM->dbclassloader->get_midcom_class_name_for_legacy_midgard_class($matching_type);
            //midgard_admin_asgard_reflector::resolve_baseclass($matching_type);
            $this->component = $_MIDCOM->dbclassloader->_mgdschema_class_handler[$this->class];
            //$matching_type;
            
            if (empty($this->constraints))
            {
                $this->constraints = array();
            }
            if (empty($this->searchfields))
            {
                $this->searchfields = $maa_reflector->get_search_properties();
                if (empty($this->searchfields))
                {
                    //Special rules for objects that need them
                }
            }
            if (empty($this->orders))
            {
                $this->orders = array();
            }

            $reflection_l10n =& $maa_reflector->get_component_l10n();
            if (empty($this->result_headers))
            {
                $this->result_headers = array();
                foreach ($labels as $label)
                {
                    $header = array();
                    $header['title'] = $reflection_l10n->get($label);
                    $header['name'] = $label;
                    $this->result_headers[] = $header;
                }
                
                if (empty($this->result_headers))
                {
                    //Special rules for objects that need them
                }
            }
            
            if (   $this->creation_mode_enabled
                && empty($this->creation_default_key))
            {
                $this->creation_default_key = $this->result_headers[0]['name'];
            }
            
            /*debug_add("using class: {$this->class}");
            debug_add("using component: {$this->component}");
            debug_print_r('$this->searchfields',$this->searchfields);
            debug_print_r('$this->result_headers',$this->result_headers);
            
            debug_pop();*/
            return true;
        }
        
        //debug_pop();
        return false;
    }
    
    function _init_widget_options()
    {
        $this->_js_widget_options['widget_id'] = "'{$this->_element_id}'";
        $this->_js_widget_options['min_chars'] = 3;
        $this->_js_widget_options['result_limit'] = 10;
        $this->_js_widget_options['renderer_callback'] = 'false';
        $this->_js_widget_options['result_headers'] = '[]';
        $this->_js_widget_options['allow_multiple'] = 'true';
        $this->_js_widget_options['id_field'] = "'$this->id_field'";
        
        if ($this->creation_mode_enabled)
        {
            $this->_js_widget_options['creation_mode'] = 'true';
            // Ponder: Should we add prefix here? How should we handle multilang sites.
            $this->_js_widget_options['creation_handler'] = "'{$this->creation_handler}'";
            $this->_js_widget_options['creation_default_key'] = "'{$this->creation_default_key}'";
        }

        if (isset($this->max_results))
        {
            $this->_js_widget_options['result_limit'] = $this->result_limit;
        }
        if (isset($this->renderer_callback))
        {
            $this->_js_widget_options['renderer_callback'] = $this->renderer_callback;
        }
        if (isset($this->allow_multiple))
        {
            $this->_js_widget_options['allow_multiple'] = 'false';
            if ($this->allow_multiple)
            {
                $this->_js_widget_options['allow_multiple'] = 'true';
            }
        }
                
        $headers = "[ ";
        $header_count = count($this->result_headers);
        foreach ($this->result_headers as $k => $header_item)
        {
            $headers .= "{ ";
            
            $headers .= "title: '{$header_item['title']}', ";
            $headers .= "name: '{$header_item['name']}' ";
            
            if (($k+1) == $header_count)
            {
                $headers .= " }";
            }
            else
            {
                $headers .= " }, ";
            }
        }
        $headers .= " ]";
        $this->_js_widget_options['result_headers'] = $headers;
        
        /*debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("js result_headers: {$headers}");
        debug_pop();*/
        
        $this->_generate_extra_params();        
    }
    
    function _generate_extra_params()
    {
        $map = array
        (
            'component', 'class',
            '_callback_class', '_callback_args',
            '_renderer_callback_class', '_renderer_callback_args',
            'constraints', 'searchfields', 'orders',
            'result_headers',
            'auto_wildcards',
            'reflector_key'
        );
        
        $params = array();
        $mk_cnt = count($map);
        foreach ($map as $k => $map_key)
        {
            $params[$map_key] = $this->$map_key;
        }
        
        $this->_js_widget_options['extra_params'] = "'" . base64_encode(serialize($params)) . "'";
    }
    
    /**
     * Adds a simple search form and place holder for results.
     * Also adds static options to results.
     */
    function add_elements_to_form()
    {
        // debug_push_class(__CLASS__, __FUNCTION__);
        
        // Get url to search handler
        $nav = new midcom_helper_nav();
        $root_node = $nav->get_node($nav->get_root_node());
        $this->_handler_url = $root_node[MIDCOM_NAV_FULLURL] . 'midcom-exec-midcom.helper.datamanager2/chooser_handler.php';
        
        
        $this->widget_elements[] =& HTML_QuickForm::createElement
        (
            'hidden',
            "{$this->_element_id}_handler_url",
            $this->_handler_url,
            array
            (
                'id' => "{$this->_element_id}_handler_url",
            )
        );
        foreach ($this->_js_widget_options as $key => $value)
        {
            $this->widget_elements[] =& HTML_QuickForm::createElement
            (
                'hidden',
                "{$this->_element_id}_{$key}",
                $value,
                array
                (
                    'id' => "{$this->_element_id}_{$key}",
                )
            );
        }
        
        // Text input for the search box
        $this->widget_elements[] =& HTML_QuickForm::createElement
        (
            'text',
            "{$this->_element_id}_search_input",
            $this->_translate($this->_field['title']),
            array
            (
                'class'         => 'shorttext chooser_widget_search_input',
                'id'            => "{$this->_element_id}_search_input",
            )
        );
        
        if ($this->creation_mode_enabled)
        {
            $dialog_id = $this->_element_id . '_creation_dialog';
            
            $dialog_js = '<script type="text/javascript">';
            $dialog_js .= "function close_dialog(){jQuery('#{$dialog_id}').jqmHide();};";
            $dialog_js .= "function add_item(data){jQuery('#{$this->_element_id}_search_input').midcom_helper_datamanager2_widget_chooser_add_result_item(data);};";
            $dialog_js .= '</script>';
            
            $dialog_html = '<div class="chooser_widget_creation_dialog" id="' . $dialog_id . '">';
            $dialog_html .= '<div class="chooser_widget_creation_dialog_content_holder">';
            $dialog_html .= $dialog_js;
            $dialog_html .= '</div>';
            $dialog_html .= '</div>';
            
            $button_html = '<div class="chooser_widget_create_button" id="' . $this->_element_id . '_create_button">';
            $button_html .= '</div>';

            $html = $button_html . $dialog_html;

            $this->widget_elements[] =& HTML_QuickForm::createElement
            (
                'static',
                "{$this->_element_id}_creation_dialog_holder",
                '',
                $html
            );
        }
        
        $this->_jscript .= '<script type="text/javascript">';
        $this->_jscript .= 'jQuery().ready(function(){';
        
        $script = "jQuery('#{$this->_element_id}_search_input').midcom_helper_datamanager2_widget_chooser_widget('{$this->_handler_url}', {\n";
        if (!empty($this->_js_widget_options))
        {
            $opt_cnt = count($this->_js_widget_options);
            $i = 0;
            foreach ($this->_js_widget_options as $key => $value)
            {
                $i++;
                $script .= "{$key}: {$value}";
                if ($i < $opt_cnt)
                {
                    $script .= ",\n";
                }
            }        
        }
        $script .= "});";
        $this->_jscript .= $script;
        
        // Add existing and static selections
        $existing_elements = $this->_type->selection;
        
        // debug_print_r('existing_elements',$existing_elements);

        // debug_print_r('static_options',$this->static_options);
        
        $elements = array_merge($this->static_options, $existing_elements);
        // debug_print_r('all elements to be added',$elements);
                
        $ee_script = '';
        if ($this->_renderer_callback)
        {
            foreach ($elements as $key)
            {
                // debug_add("Passing key to renderer {$key}");
                $data = $this->_get_key_data($key);
                if ($data)
                {
                    // debug_add("Got data: {$data}");
                    $item = $this->_renderer_callback->render_data($data);
                    // debug_add("Got item: {$item}");
                    $ee_script .= "jQuery('#{$this->_element_id}_search_input').midcom_helper_datamanager2_widget_chooser_add_result_item({$data},'{$item}');\n";                    
                }
            }
        }
        else
        {
            foreach ($elements as $key)
            {
                // debug_add("Processing key {$key}");
                $data = $this->_get_key_data($key);
                if ($data)
                {
                    // debug_add("Got data: {$data}");
                    $ee_script .= "jQuery('#{$this->_element_id}_search_input').midcom_helper_datamanager2_widget_chooser_add_result_item({$data});\n";                    
                }
            }
        }
        $this->_jscript .= $ee_script;
        
        $this->_jscript .= '});';
        $this->_jscript .= '</script>';
        
        //$this->_form->addElement('static', "{$this->_element_id}_initscripts", '', $this->_jscript);        

        $this->widget_elements[] =& HTML_QuickForm::createElement
        (
            'static',
            "{$this->_element_id}_initscripts",
            '',
            $this->_jscript
        );
        
        $group =& $this->_form->addGroup($this->widget_elements, $this->name, $this->_translate($this->_field['title']), '', array('class' => 'midcom_helper_datamanager2_widget_chooser'));
    }
    
    function _resolve_object_name(&$object)
    {
        // debug_push_class(__CLASS__, __FUNCTION__);
        // debug_add("resolving object name from id {$object->id}");
        
        $name = @$object->get_label();
        
        if (empty($name))
        {
            foreach ($this->result_headers as $header_item)
            {
                $item_name = $header_item['name'];
                $value = @$object->$item_name;
                $value = rawurlencode($value);
                $name .= "{$item_name}: '{$value}'";
                
                if ($i < $hi_count)
                {
                    $name .= ", ";
                }
                
                $i++;
            }
        }
        
        return $name;
    }
    
    function _object_to_jsdata(&$object)
    {
        // debug_push_class(__CLASS__, __FUNCTION__);        
        // debug_add("converting object with id {$object->id} to jsdata");
        
        $id = @$object->id;
        $guid = @$object->guid;
        
        $jsdata = "{";
        
        $jsdata .= "id: '{$id}',";
        $jsdata .= "guid: '{$guid}',";
        $jsdata .= "pre_selected: true,";
                        
        if (   !empty($this->reflector_key)
            && !$this->result_headers)
        {
            $value = @$object->get_label();
            $value = rawurlencode($value);
            // debug_add("adding header item: name=label value={$value}");
            $jsdata .= "label: '{$value}'";
        }
        else
        {
            $hi_count = count($this->result_headers);
            $i = 1;
            foreach ($this->result_headers as $header_item)
            {
                $item_name = $header_item['name'];
                $value = @$object->$item_name;
                $value = rawurlencode($value);
                // debug_add("adding header item: name={$item_name} value={$value}");
                $jsdata .= "{$item_name}: '{$value}'";
                
                if ($i < $hi_count)
                {
                    $jsdata .= ", ";
                }
                
                $i++;
            }    
        }        

        $jsdata .= "}";
        
        return $jsdata;        
        
        // debug_pop();
    }
    
    function _get_key_data($key, $in_render_mode=false)
    {
        // debug_push_class(__CLASS__, __FUNCTION__);        
        // debug_add("get_key_data for key: {$key}");
        
        if ($this->_callback)
        {
            // debug_add("Using callback to fetch key data");

            if ($in_render_mode)
            {
                // debug_pop();
                return $_callback->resolve_object_name($key);
            }
            
            $results = $_callback->get_key_data($key);
            
            if (! $results)
            {
                return false;
            }
            
            // debug_pop();
            
            if ($this->_renderer_callback)
            {
                return $results;
            }            
            
            return $this->_object_to_jsdata(&$results);
        }
        
        // debug_add("Using clever class or predefined class");
        
        $_MIDCOM->auth->request_sudo();
        
        if (   isset($this->reflector_key)
            && !empty($this->reflector_key))
        {
            if ($this->reflector_key == 'buddy')
            {
                $this->class = 'org_openpsa_contacts_person';
                $this->component = 'org.openpsa.contacts';
            }
        }

        if (!class_exists($this->class))
        {
            $_MIDCOM->componentloader->load_graceful($this->component);
        }
        
        $qb = @call_user_func(array($this->class, 'new_query_builder'));
        if (! $qb)
        {
            // debug_add("use midgard_query_builder");
            $qb = new midgard_query_builder($this->class);
        }

        //$qb->begin_group('OR');
        $qb->add_constraint($this->id_field, '=', $key);
        //$qb->add_constraint('guid', '=', $key);
        //$qb->end_group();
        
        $results = $qb->execute();        
        
        // debug_print_r("Got results:",$results);
        
        if (count($results) == 0)
        {
            // debug_add("Fetching data for key '{$key}' failed.");
            return false;
        }
        
        $object = $results[0];
        
        $_MIDCOM->auth->drop_sudo();
        
        // debug_pop();
        
        if ($in_render_mode)
        {
            return $this->_resolve_object_name(&$object);
        }
        
        if ($this->_renderer_callback)
        {
            return $object;
        }
        
        return $this->_object_to_jsdata(&$object);
    }
    
    /**
     * TODO: Implement freezing and unfreezing
     */
    function freeze()
    {
        //We should freeze the inputs and results here
    }
    function unfreeze()
    {
        //We should unfreeze the inputs and results here
    }
    function is_frozen()
    {
        return false;
    }

    /**
      * The defaults of the widget are mapped to the current selection.
      */
     function get_default()
     {
         // debug_push_class(__CLASS__, __FUNCTION__);         
         //debug_print_r('this->_type',$this->_type);
         
         $defaults = array();
         foreach ($this->_type->selection as $key)
         {
             $defaults[$key] = true;
         }
         
         // debug_print_r('defaults',$defaults);         
         // debug_pop();
         
         return Array($this->name => $defaults);
     }

    /**
     * Reads the given get/post data and puts to type->selection
     */
    function sync_type_with_widget($results)
    {
        // debug_push_class(__CLASS__, __FUNCTION__);        
        // debug_print_r('results:',$results);
        
        $this->_type->selection = array();
        if (!isset($results["{$this->_element_id}_selections"]))
        {
            return;
        }
        $real_results =& $results["{$this->_element_id}_selections"];
        if (is_array($real_results))
        {
            foreach ($real_results as $key => $value)
            {
                // debug_add("checking key {$key} with value ".var_dump($value));
                if (   $value != "0"
                    || $value != 0)
                {
                    // debug_add("adding key {$key} to selection");
                    $this->_type->selection[] = $key;                
                }
            }
        }
        elseif (!$this->allow_multiple)
        {
            $this->_type->selection[] = $real_results;
        }
        
        // debug_print_r('real_results', $real_results);
        // debug_print_r('_type->selection', $this->_type->selection);                
        // debug_pop();
    }

    function render_content()
    {
        // debug_push_class(__CLASS__, __FUNCTION__);
        
        echo '<ul>';
        if (count($this->_type->selection) == 0)
        {
            echo '<li>' . $this->_translate('type select: no selection') . '</li>';
        }
        else
        {
            // debug_add("We have selections!");
            
            foreach ($this->_type->selection as $key)
            {
                $data = $this->_get_key_data($key, true);
                echo '<li>' . $data . '</li>';
            }
        }
        echo '</ul>';
        
        // debug_pop();
    }

}

?>