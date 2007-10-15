<?php
/**
 * @package midgard.admin.asgard
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: manage.php 3757 2006-07-27 14:32:42Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Object management interface
 *
 * @package midgard.admin.asgard
 */
class midgard_admin_asgard_handler_object_manage extends midcom_baseclasses_components_handler
{
    /**
     * Some object
     *
     * @var midgard_object
     * @access private
     */
    var $_object = null;

    /**
     * Some newly created object
     *
     * @var midgard_object
     * @access private
     */
    var $_new_object = null;

    /**
     * Some MgdSchema class
     *
     * @var string
     * @access private
     */
    var $_new_type = null;

    /**
     * The Datamanager of the object to display.
     *
     * @var midcom_helper_datamanager2_datamanager
     * @access private
     */
    var $_datamanager = null;

    /**
     * The Controller of the object used for editing
     *
     * @var midcom_helper_datamanager2_controller_simple
     * @access private
     */
    var $_controller = null;

    /**
     * The schema database in use, available only while a datamanager is loaded.
     *
     * @var array
     * @access private
     */
    var $_schemadb = null;

    /**
     * Midgard reflection property instance for the current object's class.
     *
     * @var midgard_reflection_property
     * @access private
     */
    var $_reflector = null;

    /**
     * Simple default constructor.
     */
    function midgard_admin_asgard_handler_object_manage()
    {
        $this->_component = 'midgard.admin.asgard';
        parent::midcom_baseclasses_components_handler();
    }
    
    function _on_initialize()
    {
        // Ensure we get the correct styles
        $_MIDCOM->style->prepend_component_styledir('midgard.admin.asgard');
        $_MIDCOM->skip_page_style = true;
        
        $_MIDCOM->load_library('midcom.helper.datamanager2');
    }

    /**
     * Simple helper which references all important members to the request data listing
     * for usage within the style listing.
     */
    function _prepare_request_data()
    {
        $this->_request_data['object'] =& $this->_object;
        $this->_request_data['controller'] =& $this->_controller;
        $this->_request_data['schemadb'] =& $this->_schemadb;
        $this->_request_data['datamanager'] =& $this->_datamanager;
    }
    
    function sort_schema_fields($first, $second)
    {
        $preferred_fields = $this->_config->get('object_preferred_fields');
        $timerange_fields = $this->_config->get('object_timerange_fields');
        $address_fields = $this->_config->get('object_address_fields');
        $phone_fields = $this->_config->get('object_phone_fields');
        $location_fields = $this->_config->get('object_location_fields');
        
        // We handle the cases, and then their subcases
        if (   in_array($first, $preferred_fields)
            && $this->_reflector->get_midgard_type($first) != MGD_TYPE_LONGTEXT)
        {
            // This is one of the preferred fields, check subcases
            if (in_array($second, $preferred_fields))
            {
                return strnatcmp($first, $second);
            }

            return -1;
        }
        
        if ($this->_reflector->get_midgard_type($first) == MGD_TYPE_LONGTEXT)
        {
            // This is a longtext field, they come next
            if (   in_array($second, $preferred_fields)
                && $this->_reflector->get_midgard_type($second) != MGD_TYPE_LONGTEXT)
            {
                return 1;
            }
            if ($this->_reflector->get_midgard_type($second) == MGD_TYPE_LONGTEXT)
            {
                return strnatcmp($first, $second);
            }
            return -1;
        }
        
        if ($this->_reflector->is_link($first))
        {
            // This is a linked property, they come next
            if (   in_array($second, $preferred_fields)
                || $this->_reflector->get_midgard_type($second) == MGD_TYPE_LONGTEXT)
            {
                return 1;
            }
            if ($this->_reflector->is_link($second))
            {
                return strnatcmp($first, $second);
            }
            return -1;
        }

        if (in_array($first, $timerange_fields))
        {
            if (   in_array($second, $preferred_fields)
                || $this->_reflector->get_midgard_type($second) == MGD_TYPE_LONGTEXT
                || $this->_reflector->is_link($second))
            {
                return 1;
            }
            
            if (in_array($second, $timerange_fields))
            {
                // Both are phone fields, arrange them in proper order
                return (array_search($first, $timerange_fields) < array_search($second, $timerange_fields)) ? -1 : 1;
            }
            
            return -1;
        }

        if (in_array($first, $phone_fields))
        {
            if (   in_array($second, $preferred_fields)
                || $this->_reflector->get_midgard_type($second) == MGD_TYPE_LONGTEXT
                || $this->_reflector->is_link($second)
                || in_array($second, $timerange_fields))
            {
                return 1;
            }
            
            if (in_array($second, $phone_fields))
            {
                // Both are phone fields, arrange them in proper order
                return (array_search($first, $phone_fields) < array_search($second, $phone_fields)) ? -1 : 1;
            }
            
            return -1;
        }

        if (in_array($first, $address_fields))
        {
            if (   in_array($second, $preferred_fields)
                || $this->_reflector->get_midgard_type($second) == MGD_TYPE_LONGTEXT
                || $this->_reflector->is_link($second)
                || in_array($second, $timerange_fields)
                || in_array($second, $phone_fields))
            {
                return 1;
            }
            
            if (in_array($second, $address_fields))
            {
                // Both are address fields, arrange them in proper order
                return (array_search($first, $address_fields) < array_search($second, $address_fields)) ? -1 : 1;
            }
            
            return -1;
        }
        
        if (in_array($first, $location_fields))
        {
            if (   in_array($second, $preferred_fields)
                || $this->_reflector->get_midgard_type($second) == MGD_TYPE_LONGTEXT
                || $this->_reflector->is_link($second)
                || in_array($second, $timerange_fields)
                || in_array($second, $phone_fields)
                || in_array($second, $address_fields))
            {
                return 1;
            }
            
            if (in_array($second, $location_fields))
            {
                // Both are address fields, arrange them in proper order
                return (array_search($first, $location_fields) < array_search($second, $location_fields)) ? -1 : 1;
            }
            
            return -1;
        }


        if (   in_array($second, $preferred_fields)
            || $this->_reflector->get_midgard_type($second) == MGD_TYPE_LONGTEXT
            || $this->_reflector->is_link($second)
            || in_array($second, $timerange_fields)
            || in_array($second, $phone_fields)
            || in_array($second, $address_fields)
            || in_array($second, $location_fields))
        {
            // First field was not a preferred field, but second is
            return 1;
        }
        
        // Others come as they do
        return strnatcmp($first, $second);
    }

    /**
     * Generates, loads and prepares the schema database.
     *
     * The operations are done on all available schemas within the DB.
     */
    function _load_schemadb($type = null)
    {
        if ($type != null)
        {
            $dummy_object = new $type();
            $type_fields = array_keys(get_object_vars($dummy_object));
        }
        else
        {
            $type = get_class($this->_object);
            $type_fields = array_keys(get_object_vars($this->_object));
        }
    
        $this->_schemadb = midcom_helper_datamanager2_schema::load_database('file:/midgard/admin/asgard/config/schemadb_default.inc');
        $this->_reflector = new midgard_reflection_property($type);
        
        // Iterate through object properties

        unset($type_fields['metadata']);
        usort($type_fields, array($this, 'sort_schema_fields'));
        foreach ($type_fields as $key)
        {
            if (in_array($key, $this->_config->get('object_skip_fields')))
            {
                continue;
            }
            
            // Only hosts have lang field that we will actually display
            if (   $key == 'lang'
                && !is_a($this->_object, 'midgard_host'))
            {
                continue;
            }
            
            // Linked fields should use universalchooser
            if ($this->_reflector->is_link($key))
            {
                $linked_type = $this->_reflector->get_link_name($key);
                $linked_type_reflector = midgard_admin_asgard_reflector::get($linked_type);
                $type = $this->_reflector->get_midgard_type($key);
                
                if ($key == 'up')
                {
                    $field_label = sprintf($this->_l10n->get('under %s'), midgard_admin_asgard_plugin::get_type_label($linked_type));
                }
                else
                {
                    $type_label = midgard_admin_asgard_plugin::get_type_label($linked_type);
                    if (substr($type_label, 0, strlen($key)) == $key)
                    {
                        // Handle abbreviations like "lang" for "language"
                        $field_label = $type_label;
                    }
                    elseif ($key == $type_label)
                    {
                        $field_label = $key;
                    }
                    else
                    {
                        $field_label = sprintf($this->_l10n->get('%s (%s)'), $key, $type_label);
                    }
                }

                switch ($type)
                {
                    case MGD_TYPE_INT:
                    case MGD_TYPE_STRING:
                    	$class = $_MIDCOM->dbclassloader->get_midcom_class_name_for_legacy_midgard_class($linked_type);
                    	if (! $class)
                    	{
                    	    break;
                    	}
                    	$component = $_MIDCOM->dbclassloader->_mgdschema_class_handler[$class];
                        $this->_schemadb['object']->append_field
                        (
                            $key, 
                            array
                            (
                                'title'       => $field_label,
                                'storage'     => $key,
                                'type' => 'select',
                                'type_config' => array
                                (
                                    'require_corresponding_option' => false,
                                    'options' => array(),
                                    'allow_other' => true,
                                ),
                                'widget' => 'universalchooser',
                                'widget_config' => array
                                (
                                    'class' => $class,
                                    'component' => $component,
                                    'titlefield' => $linked_type_reflector->get_label_property(),
                                    'idfield' => $this->_reflector->get_link_target($key),
                                    'searchfields' => $linked_type_reflector->get_search_properties(),
                                    'orders' => array(),
                                ),
                            )
                        );
                        break;
                }
                
                // Skip rest of processing
                continue;
            }
            
            $type = $this->_reflector->get_midgard_type($key);
            switch ($type)
            {
                case MGD_TYPE_STRING:
                    $this->_schemadb['object']->append_field
                    (
                        $key, 
                        array
                        (
                            'title'       => $key,
                            'storage'     => $key,
                            'type'        => 'text',
                            'widget'      => 'text',
                        )
                    );
                    break;
                case MGD_TYPE_LONGTEXT:
                    // Figure out nice size for the editing field

                    $output_mode = '';
                    $widget = 'textarea';
                    $dm_type = 'text';

                    switch ($key)
                    {
                        case 'content':
                        case 'description':
                            $height = 30;
                            $output_mode = 'html';
                            $widget = 'tinymce';
                            break;
                        case 'value':
                        case 'code':
                            // These are typical "large" fields
                            $height = 30;
                            $dm_type = 'php';
                            $output_mode = 'code';
                            $widget = 'codepress';
                            break;
                            
                        default:
                            $height = 6;
                            break;
                    }
                    
                    $this->_schemadb['object']->append_field
                    (
                        $key, 
                        array
                        (
                            'title'       => $key,
                            'storage'     => $key,
                            'type'        => $dm_type,
                            'type_config' => Array
                            (
                                'output_mode' => $output_mode,
                            ),
                            'widget'      => $widget,
                            'widget_config' => Array
                            (
                                'height' => $height,
                                'width' => '100%',
                            ),
                        )
                    );
                    break;
                case MGD_TYPE_INT:
                    if (   $key == 'start'
                        || $key == 'end'
                        || $key == 'added'
                        || $key == 'date')
                    {
                        // We can safely assume that INT fields called start and end store unixtimes
                        $this->_schemadb['object']->append_field
                        (
                            $key, 
                            array
                            (
                                'title'       => $key,
                                'storage'     => $key,
                                'type' => 'date',
                                'type_config' => array
                                (
                                    'storage_type' => 'UNIXTIME'
                                ),
                                'widget' => 'jsdate',
                            )
                        );
                    }
                    else
                    {
                        $this->_schemadb['object']->append_field
                        (
                            $key, 
                            array
                            (
                                'title'       => $key,
                                'storage'     => $key,
                                'type'        => 'number',
                                'widget'      => 'text',
                            )
                        );
                    }
                    break;
                case MGD_TYPE_FLOAT:
                    $this->_schemadb['object']->append_field
                    (
                        $key, 
                        array
                        (
                            'title'       => $key,
                            'storage'     => $key,
                            'type'        => 'number',
                            'widget'      => 'text',
                        )
                    );
                    break;
                case MGD_TYPE_BOOLEAN:
                    $this->_schemadb['object']->append_field
                    (
                        $key, 
                        array
                        (
                            'title'       => $key,
                            'storage'     => $key,
                            'type'        => 'boolean',
                            'widget'      => 'checkbox',
                        )
                    );
                    break;
                case MGD_TYPE_TIMESTAMP:
                    $this->_schemadb['object']->append_field
                    (
                        $key, 
                        array
                        (
                            'title'       => $key,
                            'storage'     => $key,
                            'type' => 'date',
                            'type_config' => array
                            (
                                'storage_type' => 'UNIXTIME'
                            ),
                            'widget' => 'jsdate',
                        )
                    );
                    break;
            }
        }
    }

    /**
     * Object display
     */
    function _handler_view($handler_id, $args, &$data)
    {
        midgard_admin_asgard_plugin::init_language($handler_id, $args, &$data);

        $this->_object = $_MIDCOM->dbfactory->get_object_by_guid($args[0]);

        if (! $this->_object)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "The GUID '{$args[0]}' was not found.");
            // This will exit.
        }

        $this->_prepare_request_data();
        
        $this->_load_schemadb();
        $this->_datamanager = new midcom_helper_datamanager2_datamanager($this->_schemadb);
        $this->_datamanager->set_schema('object');
        if (!$this->_datamanager->set_storage($this->_object))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to create a DM2 instance for object {$this->_object->guid}.");
            // This will exit.
        }
        
        midgard_admin_asgard_plugin::bind_to_object($this->_object, $handler_id, &$data);

        midgard_admin_asgard_plugin::finish_language($handler_id, &$data);

        return true;
    }

    /**
     * Shows the loaded object.
     */
    function _show_view($handler_id, &$data)
    {
        $data['view_object'] = $this->_datamanager->get_content_html();
        midcom_show_style('midgard_admin_asgard_header');
        midcom_show_style('midgard_admin_asgard_middle');
        midcom_show_style('midgard_admin_asgard_object_view');
        midcom_show_style('midgard_admin_asgard_footer');
    }
    
    /**
     * Object editing view
     */
    function _handler_edit($handler_id, $args, &$data)
    {
        midgard_admin_asgard_plugin::init_language($handler_id, $args, &$data);
        $this->_object = $_MIDCOM->dbfactory->get_object_by_guid($args[0]);
        if (!$this->_object)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "The GUID '{$args[0]}' was not found.");
            // This will exit
        }
        $this->_object->require_do('midgard:update');

        $this->_load_schemadb();
        $this->_controller = midcom_helper_datamanager2_controller::create('simple');
        $this->_controller->schemadb =& $this->_schemadb;
        $this->_controller->set_storage($this->_object, 'object');
        if (! $this->_controller->initialize())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to initialize a DM2 controller instance for object {$this->_object->guid}.");
            // This will exit.
        }

        switch ($this->_controller->process_form())
        {
            case 'save':
                if (   is_a($this->_object, 'midgard_style')
                    || is_a($this->_object, 'midgard_element')
                    || is_a($this->_object, 'midgard_page')
                    || is_a($this->_object, 'midgard_pageelement'))
                {
                    mgd_cache_invalidate();
                }
            
                // Reindex the object
                //$indexer =& $_MIDCOM->get_service('indexer');
                //net_nemein_wiki_viewer::index($this->_request_data['controller']->datamanager, $indexer, $this->_topic);
                // *** FALL-THROUGH ***
                $_MIDCOM->relocate("__mfa/asgard/object/edit/{$this->_object->guid}/{$data['language_code']}");
                // This will exit.

            case 'cancel':
                $_MIDCOM->relocate("__mfa/asgard/object/view/{$this->_object->guid}/{$data['language_code']}");
                // This will exit.
        }

        $this->_prepare_request_data();
        midgard_admin_asgard_plugin::bind_to_object($this->_object, $handler_id, &$data);
        midgard_admin_asgard_plugin::finish_language($handler_id, &$data);
        return true;
    }

    /**
     * Shows the loaded object in editor.
     */
    function _show_edit($handler_id, &$data)
    {
        midcom_show_style('midgard_admin_asgard_header');
        midcom_show_style('midgard_admin_asgard_middle');
        midcom_show_style('midgard_admin_asgard_object_edit');
        midcom_show_style('midgard_admin_asgard_footer');
    }
    
    function _find_linking_property($new_type)
    {
        // Figure out the linking property
        $new_type_reflector = midgard_admin_asgard_reflector::get($new_type);
        $link_properties = $new_type_reflector->get_link_properties();
        $type_to_link_to =  midgard_admin_asgard_reflector::class_rewrite(get_class($this->_object));
        foreach ($link_properties as $new_type_property => $link)
        {
            $linked_type = midgard_admin_asgard_reflector::class_rewrite($link['class']);

            if (midgard_admin_asgard_reflector::is_same_class( $linked_type, $type_to_link_to))
            {
                $parent_property = $link['target'];
                return array($new_type_property, $parent_property);
            }
        }
        
        return false;
    }

    /**
     * DM2 creation callback, binds to the current content topic.
     */
    function & dm2_create_callback(&$controller)
    {
        $create_type = $this->_new_type;
        $this->_new_object = new $create_type();
        
        if ($this->_object)
        {
            // Figure out the linking property
            $link_info = $this->_find_linking_property($create_type);
            if (!is_array($link_info))
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Could not establish link between {$create_type} and " . get_class($this->_object));
            }
        
            $child_property = $link_info[0];
            $parent_property = $link_info[1];
            $this->_new_object->$child_property = $this->_object->$parent_property;
        }

        if (! $this->_new_object->create())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_print_r('We operated on this object:', $this->_new_object);
            debug_pop();
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'Failed to create a new object, cannot continue. Last Midgard error was: '. mgd_errstr());
            // This will exit.
        }

        return $this->_new_object;
    }
    
    /**
     * Object creating view
     */
    function _handler_create($handler_id, $args, &$data)
    {
        midgard_admin_asgard_plugin::init_language($handler_id, $args, &$data);
        $this->_new_type = $_MIDCOM->dbclassloader->get_midcom_class_name_for_legacy_midgard_class($args[0]);
        $_MIDCOM->dbclassloader->load_mgdschema_class_handler($this->_new_type);
        if (!class_exists($this->_new_type))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "MgdSchema type '{$args[0]}' was not found.");
            // This will exit
        }
        $data['new_type_arg'] = $args[0];        
        
        $defaults = array();        
        if ($handler_id == '____mfa-asgard-object_create_toplevel')
        {
            $_MIDCOM->auth->require_user_do('midgard:create', null, $this->_new_type);
            
            $data['view_title'] = sprintf($_MIDCOM->i18n->get_string('create %s', 'midcom'), midgard_admin_asgard_plugin::get_type_label($data['new_type_arg']));
            $data['asgard_toolbar'] = new midcom_helper_toolbar();
            midgard_admin_asgard_plugin::get_common_toolbar($data);
            $data['current_type'] = $args[0];
        }
        else
        {
            $this->_object = $_MIDCOM->dbfactory->get_object_by_guid($args[1]);
            if (!$this->_object)
            {
                $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "The GUID '{$args[1]}' was not found.");
                // This will exit
            }
            $this->_object->require_do('midgard:create');
            midgard_admin_asgard_plugin::bind_to_object($this->_object, $handler_id, &$data);
            
            // Set "defaults"
            $link_info = $this->_find_linking_property($this->_new_type);
            if (!is_array($link_info))
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Could not establish link between {$this->_new_type} and " . get_class($this->_object));
            }
            $parent_property = $link_info[1];
            $defaults[$link_info[0]] = $this->_object->$parent_property;
        }

        $this->_load_schemadb($this->_new_type);
        $this->_schemadb['object']->fields['guid']['hidden'] = true;
        
        $this->_controller = midcom_helper_datamanager2_controller::create('create');
        $this->_controller->schemadb =& $this->_schemadb;
        $this->_controller->schema = 'object';
        $this->_controller->callback_object =& $this;
        $this->_controller->defaults = $defaults;
        if (! $this->_controller->initialize())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to initialize a DM2 create controller.");
            // This will exit.
        }

        switch ($this->_controller->process_form())
        {
            case 'save':
                if (   is_a($this->_new_object, 'midgard_style')
                    || is_a($this->_new_object, 'midgard_element'))
                {
                    mgd_cache_invalidate();
                }
            
                // Reindex the object
                //$indexer =& $_MIDCOM->get_service('indexer');
                //net_nemein_wiki_viewer::index($this->_request_data['controller']->datamanager, $indexer, $this->_topic);
                // *** FALL-THROUGH ***
                $this->_new_object->set_parameter('midcom.helper.datamanager2', 'schema_name', 'default');
                $_MIDCOM->relocate("__mfa/asgard/object/edit/{$this->_new_object->guid}/{$data['language_code']}");
                // This will exit.

            case 'cancel':
                if ($this->_object)
                {
                    $objecturl = "object/view/{$this->_object->guid}/";
                }
                else
                {
                    $objecturl = $args[0];
                }

                $_MIDCOM->relocate("__mfa/asgard/{$objecturl}{$data['language_code']}");
                // This will exit.
        }

        $this->_prepare_request_data();
        midgard_admin_asgard_plugin::finish_language($handler_id, &$data);
        return true;
    }

    /**
     * Shows the loaded object in editor.
     */
    function _show_create($handler_id, &$data)
    {
        midcom_show_style('midgard_admin_asgard_header');
        midcom_show_style('midgard_admin_asgard_middle');
        midcom_show_style('midgard_admin_asgard_object_create');
        midcom_show_style('midgard_admin_asgard_footer');
    }
    
    /**
     * Object display
     */
    function _handler_delete($handler_id, $args, &$data)
    {
        midgard_admin_asgard_plugin::init_language($handler_id, $args, &$data);
        $this->_object = $_MIDCOM->dbfactory->get_object_by_guid($args[0]);
        if (!$this->_object)
        {
            return false;
        }

        $this->_prepare_request_data();
        
        $this->_load_schemadb();
        $this->_datamanager = new midcom_helper_datamanager2_datamanager($this->_schemadb);
        $this->_datamanager->set_schema('object');
        if (!$this->_datamanager->set_storage($this->_object))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to create a DM2 instance for object {$this->_object->guid}.");
            // This will exit.
        }
        
        if (array_key_exists('midgard_admin_asgard_deleteok', $_REQUEST))
        {
            // Deletion confirmed.
            $parent = $this->_object->get_parent();
            if (! $this->_object->delete())
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to delete object {$args[0]}, last Midgard error was: " . mgd_errstr());
                // This will exit.
            }
            
            if (   is_a($this->_object, 'midgard_style')
                || is_a($this->_object, 'midgard_element'))
            {
                mgd_cache_invalidate();
            }

            // Update the index
            $indexer =& $_MIDCOM->get_service('indexer');
            $indexer->delete($this->_object->guid);

            
            if ($data['language_code'] != '')
            {
                // Relocate to lang0 view page
                $_MIDCOM->relocate("__mfa/asgard/object/view/{$this->_object->guid}/");
                // This will exit()
            }
            
            if ($parent)
            {
                $_MIDCOM->relocate("__mfa/asgard/object/view/{$parent->guid}/");
                // This will exit()
            }
            
            $_MIDCOM->relocate('__mfa/asgard/');
            // This will exit.
        }

        if (array_key_exists('midgard_admin_asgard_deletecancel', $_REQUEST))
        {
            // Redirect to view page.
            $_MIDCOM->relocate("__mfa/asgard/object/view/{$this->_object->guid}/{$data['language_code']}");
            // This will exit()
        }
        
        midgard_admin_asgard_plugin::bind_to_object($this->_object, $handler_id, &$data);
        midgard_admin_asgard_plugin::finish_language($handler_id, &$data);
        return true;
    }

    /**
     * Shows the object to delete.
     */
    function _show_delete($handler_id, &$data)
    {
        $data['view_object'] = $this->_datamanager->get_content_html();
        midcom_show_style('midgard_admin_asgard_header');
        midcom_show_style('midgard_admin_asgard_middle');
        midcom_show_style('midgard_admin_asgard_object_delete');
        midcom_show_style('midgard_admin_asgard_footer');
    }
}
?>