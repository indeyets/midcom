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
     * Authenticated person record
     *
     * @var midcom_db_person
     * @access private
     */
    var $_person;

    /**
     * Simple default constructor.
     */
    function __construct()
    {
        $this->_component = 'midgard.admin.asgard';
        parent::__construct();
    }

    function _on_initialize()
    {
        // Ensure we get the correct styles
        $_MIDCOM->style->prepend_component_styledir('midgard.admin.asgard');
        $_MIDCOM->skip_page_style = true;

        $_MIDCOM->load_library('midcom.helper.datamanager2');

        // Get the localization library for Asgard
        $this->_request_data['l10n'] = $_MIDCOM->i18n->get_l10n('midgard.admin.asgard');
        $this->_l10n =& $this->_request_data['l10n'];
        midgard_admin_asgard_plugin::get_default_mode(&$this->_request_data);
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
        $this->_request_data['asgard_prefix'] = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX) . '__mfa/asgard/';
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
    function _load_schemadb($type = null, $include_fields = null)
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

        switch (true)
        {
            case is_null($include_fields):
            case !$include_fields:
                break;
            case is_array($include_fields):
                if (count($include_fields) === 0)
                {
                    $include_fields = null;
                }
                break;
            case is_string($include_fields):
                $include_fields = array
                (
                    $include_fields,
                );
                break;
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

            // Skip the fields that aren't requested, if inclusion list has been defined
            if (   $include_fields
                && !in_array($key, $include_fields))
            {
                continue;
            }

            // Only hosts have lang field that we will actually display
            if (   $key == 'lang'
                && !is_a($this->_object, 'midgard_host'))
            {
                continue;
            }

            // Linked fields should use chooser
            if ($this->_reflector->is_link($key))
            {
                $linked_type = $this->_reflector->get_link_name($key);
                $linked_type_reflector = midcom_helper_reflector::get($linked_type);
                $field_type = $this->_reflector->get_midgard_type($key);

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
                        $field_label = sprintf($this->_l10n->get('%s (%s)'), midgard_admin_asgard_plugin::get_type_label($key), $type_label);
                    }
                }

                // Get the chooser widgets
                switch ($field_type)
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
                                'type'        => 'select',
                                'type_config' => array
                                (
                                    'require_corresponding_option' => false,
                                    'options' => array(),
                                    'allow_other' => true,
                                    'allow_multiple' => false,
                                ),
                                'widget' => 'chooser',
                                'widget_config' => array
                                (
                                    'class' => $class,
                                    'component' => $component,
                                    'titlefield' => $linked_type_reflector->get_label_property(),
                                    'id_field' => $this->_reflector->get_link_target($key),
                                    'searchfields' => $linked_type_reflector->get_search_properties(),
                                    'result_headers' => $linked_type_reflector->get_result_headers(),
                                    'orders' => array(),
                                    'creation_mode_enabled' => true,
                                    'creation_handler' => "{$_MIDGARD['self']}__mfa/asgard/object/create/chooser/{$linked_type}/",
                                    'creation_default_key' => $linked_type_reflector->get_label_property(),
                                ),
                            )
                        );
                        break;
                }

                // Skip rest of processing
                continue;
            }

            $field_type = $this->_reflector->get_midgard_type($key);
            switch ($field_type)
            {
                case MGD_TYPE_STRING:
                    if (   $key == 'component'
                        && is_a($this->_object, 'midgard_topic'))
                    {
                        // Component pulldown for topics
                        $components = array('' => '');
                        foreach ($_MIDCOM->componentloader->manifests as $manifest)
                        {
                            // Skip purecode components
                            if ($manifest->purecode)
                            {
                                continue;
                            }

                            $components[$manifest->name] = $_MIDCOM->i18n->get_string($manifest->name, $manifest->name) . " ({$manifest->name})";
                        }
                        asort($components);

                        $this->_schemadb['object']->append_field
                        (
                            $key,
                            array
                            (
                                'title'       => $key,
                                'storage'     => $key,
                                'type'        => 'select',
                                'type_config' => array
                                (
                                    'options' => $components,
                                    'allow_other' => true,
                                ),
                                'widget'      => 'selectcomponent',
                            )
                        );
                        break;
                    }

                    // Special page treatment
                    if (   $key === 'info'
                        && is_a($this->_object, 'midgard_page'))
                    {
                        $this->_schemadb['object']->append_field
                        (
                            $key,
                            array
                            (
                                'title'       => $key,
                                'storage'     => $key,
                                'type'        => 'select',
                                'type_config' => array
                                (
                                    'allow_multiple' => true,
                                    'multiple_separator' => ',',
                                    'multiple_storagemode' => 'imploded',
                                    'options' => array
                                    (
                                        'auth'        => 'require authentication',
                                        'active'      => 'active url parsing',
                                    ),
                                ),
                                'widget'      => 'select',
                            )
                        );
                        break;
                    }

                    if (   $key === 'info'
                        && is_a($this->_object, 'midgard_pageelement'))
                    {
                        $this->_schemadb['object']->append_field
                        (
                            $key,
                            array
                            (
                                'title'       => $key,
                                'storage'     => $key,
                                'type'        => 'select',
                                'type_config' => array
                                (
                                    'options' => array
                                    (
                                        '' => 'not inherited',
                                        'inherit' => 'inherited',
                                    ),
                                ),
                                'widget'      => 'select',
                            )
                        );
                        break;
                    }

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

                    // Workaround for the content field of pages
                    $adjusted_key = $key;
                    if (   $type == 'midcom_baseclasses_database_page'
                        && $key == 'content')
                    {
                        $adjusted_key = 'code';
                    }

                    switch ($adjusted_key)
                    {
                        case 'content':
                        case 'description':
                            $height = 30;

                            // Check the user preference and configuration
                            if (   midgard_admin_asgard_plugin::get_preference('tinymce_enabled')
                                || (   midgard_admin_asgard_plugin::get_preference('tinymce_enabled') !== '0'
                                    && $this->_config->get('tinymce_enabled')))
                            {
                                $widget = 'tinymce';
                            }
                            $output_mode = 'html';

                            break;
                        case 'value':
                        case 'code':
                            // These are typical "large" fields
                            $height = 30;

                            // Check the user preference and configuration
                            if (   midgard_admin_asgard_plugin::get_preference('codepress_enabled')
                                || (   midgard_admin_asgard_plugin::get_preference('codepress_enabled') !== '0'
                                    && $this->_config->get('codepress_enabled')))
                            {
                                $widget = 'codepress';
                            }

                            $dm_type = 'php';
                            $output_mode = 'code';

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
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_view($handler_id, $args, &$data)
    {
        midgard_admin_asgard_plugin::init_language($handler_id, $args, $data);

        $this->_object = $_MIDCOM->dbfactory->get_object_by_guid($args[0]);

        if (! $this->_object)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "The GUID '{$args[0]}' was not found.");
            // This will exit.
        }

        midgard_admin_asgard_plugin::set_last_visited($this->_object->guid);

        $this->_prepare_request_data();

        $this->_load_schemadb();
        $this->_datamanager = new midcom_helper_datamanager2_datamanager($this->_schemadb);
        $this->_datamanager->set_schema('object');
        if (!$this->_datamanager->set_storage($this->_object))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to create a DM2 instance for object {$this->_object->guid}.");
            // This will exit.
        }

        midgard_admin_asgard_plugin::bind_to_object($this->_object, $handler_id, $data);
        midgard_admin_asgard_plugin::finish_language($handler_id, $data);
        midgard_admin_asgard_plugin::get_common_toolbar($data);

        return true;
    }

    /**
     * Shows the loaded object.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_view($handler_id, &$data)
    {
        if (isset($_GET['ajax']))
        {
            $data['view_object'] = $this->_datamanager->get_content_html();
            midcom_show_style('midgard_admin_asgard_object_view');
            return;
        }

        $data['view_object'] = $this->_datamanager->get_content_html();
        midcom_show_style('midgard_admin_asgard_header');
        midcom_show_style('midgard_admin_asgard_middle');
        midcom_show_style('midgard_admin_asgard_object_view');
        midcom_show_style('midgard_admin_asgard_footer');
    }

    /**
     * Object editing view
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_edit($handler_id, $args, &$data)
    {
        midgard_admin_asgard_plugin::init_language($handler_id, $args, $data);
        $this->_object = $_MIDCOM->dbfactory->get_object_by_guid($args[0]);
        if (!$this->_object)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "The GUID '{$args[0]}' was not found.");
            // This will exit
        }
        midgard_admin_asgard_plugin::set_last_visited($this->_object->guid);

        $this->_object->require_do('midgard:update');
        
        // Set the object language
        $this->_set_object_language($args);
        
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
                $_MIDCOM->relocate("__mfa/asgard/object/{$this->_request_data['default_mode']}/{$this->_object->guid}/{$data['language_code']}");
                // This will exit.
            case 'edit':
                $qf =& $this->_controller->formmanager->form;
                if(isset($_REQUEST['midcom_helper_datamanager2_save']) && isset($qf->_errors))
                {
                    foreach($qf->_errors as $field => $error)
                    {
                        $element =& $qf->getElement($field);
                        $message = sprintf($this->_l10n->get('validation error in field %s: %s'), $element->getLabel(), $error);
                        $_MIDCOM->uimessages->add
                            (
                                $this->_l10n->get('midgard.admin.asgard'),
                                $message,
                                'error'
                            );
                    }
                }
        }

        $this->_prepare_request_data();
        midgard_admin_asgard_plugin::bind_to_object($this->_object, $handler_id, $data);
        midgard_admin_asgard_plugin::finish_language($handler_id, $data);
        return true;
    }

    /**
     * Shows the loaded object in editor.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
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
        $new_type_reflector = midcom_helper_reflector::get($new_type);
        $link_properties = $new_type_reflector->get_link_properties();
        $type_to_link_to =  midcom_helper_reflector::class_rewrite(get_class($this->_object));
        foreach ($link_properties as $new_type_property => $link)
        {
            $linked_type = midcom_helper_reflector::class_rewrite($link['class']);

            if (midcom_helper_reflector::is_same_class( $linked_type, $type_to_link_to))
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
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_create($handler_id, $args, &$data)
    {
        midgard_admin_asgard_plugin::init_language($handler_id, $args, $data);
        $this->_new_type = $_MIDCOM->dbclassloader->get_midcom_class_name_for_legacy_midgard_class($args[0]);
        if (!$this->_new_type)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'Failed to find type for the new object');
            // This will exit.
        }

        $_MIDCOM->dbclassloader->load_mgdschema_class_handler($this->_new_type);
        if (!class_exists($this->_new_type))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "MgdSchema type '{$args[0]}' was not found.");
            // This will exit
        }
        $data['new_type_arg'] = $args[0];

        $data['defaults'] = array();
        if (   $handler_id == '____mfa-asgard-object_create_toplevel'
            || $handler_id == '____mfa-asgard-object_create_chooser')
        {
            $_MIDCOM->auth->require_user_do('midgard:create', null, $this->_new_type);

            $data['view_title'] = sprintf($_MIDCOM->i18n->get_string('create %s', 'midcom'), midgard_admin_asgard_plugin::get_type_label($data['new_type_arg']));

            $data['asgard_toolbar'] = new midcom_helper_toolbar();
            midgard_admin_asgard_plugin::get_common_toolbar($data);
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
            midgard_admin_asgard_plugin::bind_to_object($this->_object, $handler_id, $data);

            // Set "defaults"
            $link_info = $this->_find_linking_property($this->_new_type);
            if (!is_array($link_info))
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Could not establish link between {$this->_new_type} and " . get_class($this->_object));
            }
            $parent_property = $link_info[1];
            $data['defaults'][$link_info[0]] = $this->_object->$parent_property;
        }

        $this->_load_schemadb($this->_new_type);

        if (isset($this->_schemadb['object']->fields['guid']))
        {
            $this->_schemadb['object']->fields['guid']['hidden'] = true;
        }

        // Allow setting defaults from query string, useful for things like "create event for today" and chooser
        if (   isset($_GET['defaults'])
            && is_array($_GET['defaults']))
        {
            foreach ($_GET['defaults'] as $key => $value)
            {
                if (!isset($this->_schemadb['object']->fields[$key]))
                {
                    // No such field in schema
                    continue;
                }

                $data['defaults'][$key] = $value;
            }
        }

        $this->_controller = midcom_helper_datamanager2_controller::create('create');
        $this->_controller->schemadb =& $this->_schemadb;
        $this->_controller->schema = 'object';
        $this->_controller->callback_object =& $this;
        $this->_controller->defaults = $data['defaults'];
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

                if ($handler_id != '____mfa-asgard-object_create_chooser')
                {
                    $_MIDCOM->relocate("__mfa/asgard/object/edit/{$this->_new_object->guid}/{$data['language_code']}");
                    // This will exit.
                }
                break;

            case 'cancel':
                $data['cancelled'] = true;
                if ($this->_object)
                {
                    $objecturl = "object/{$this->_request_data['default_mode']}/{$this->_object->guid}/";
                }
                else
                {
                    $objecturl = $args[0];
                }

                if ($handler_id != '____mfa-asgard-object_create_chooser')
                {
                    $_MIDCOM->relocate("__mfa/asgard/{$objecturl}{$data['language_code']}");
                    // This will exit.
                }
        }

        $this->_prepare_request_data();
        midgard_admin_asgard_plugin::finish_language($handler_id, $data);
        return true;
    }

    /**
     * Shows the loaded object in editor.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_create($handler_id, &$data)
    {
        if ($handler_id == '____mfa-asgard-object_create_chooser')
        {
            midcom_show_style('midgard_admin_asgard_popup_header');
            if (   $this->_new_object
                || isset($data['cancelled']))
            {
                $data['jsdata'] = $this->_object_to_jsdata($this->_new_object);
                midcom_show_style('midgard_admin_asgard_object_create_after');
            }
            else
            {
                midcom_show_style('midgard_admin_asgard_object_create');

            }
            midcom_show_style('midgard_admin_asgard_popup_footer');
            return;
        }

        midcom_show_style('midgard_admin_asgard_header');
        midcom_show_style('midgard_admin_asgard_middle');
        midcom_show_style('midgard_admin_asgard_object_create');
        midcom_show_style('midgard_admin_asgard_footer');
    }


    function _object_to_jsdata(&$object)
    {
        $id = @$object->id;
        $guid = @$object->guid;

        $jsdata = "{";

        $jsdata .= "id: '{$id}',";
        $jsdata .= "guid: '{$guid}',";
        $jsdata .= "pre_selected: true,";

        $hi_count = count($this->_schemadb['object']->fields);
        $i = 1;
        foreach ($this->_schemadb['object']->fields as $field => $field_data)
        {
            $value = @$object->$field;
            $value = rawurlencode($value);
            $jsdata .= "{$field}: '{$value}'";

            if ($i < $hi_count)
            {
                $jsdata .= ", ";
            }

            $i++;
        }

        $jsdata .= "}";

        return $jsdata;
    }

    /**
     * Object display
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_delete($handler_id, $args, &$data)
    {
        midgard_admin_asgard_plugin::init_language($handler_id, $args, $data);
        $this->_object = $_MIDCOM->dbfactory->get_object_by_guid($args[0]);
        if (!$this->_object)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "The GUID '{$args[0]}' was not found.");
            // This will exit.
        }

        $type = $this->_object->__new_class_name__;
        
        $relocate_url = $type;
        $class_extends = $this->_config->get('class_extends');
        if (   is_array($class_extends)
            && array_key_exists($type, $class_extends))
        {
            $relocate_url = $class_extends[$type];
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
            if (!$this->_object->delete_tree())
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
                $_MIDCOM->relocate("__mfa/asgard/object/{$this->_request_data['default_mode']}/{$this->_object->guid}/");
                // This will exit()
            }

            if ($parent)
            {
                $_MIDCOM->relocate("__mfa/asgard/object/{$this->_request_data['default_mode']}/{$parent->guid}/");
                // This will exit()
            }

            $_MIDCOM->relocate("__mfa/asgard/{$relocate_url}");
            // This will exit.
        }

        if (array_key_exists('midgard_admin_asgard_deletecancel', $_REQUEST))
        {
            // Redirect to default object mode page.
            $_MIDCOM->relocate("__mfa/asgard/object/{$this->_request_data['default_mode']}/{$this->_object->guid}/{$data['language_code']}");
            // This will exit()
        }

        midgard_admin_asgard_plugin::bind_to_object($this->_object, $handler_id, $data);
        midgard_admin_asgard_plugin::finish_language($handler_id, $data);

        // Add Thickbox
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL . '/midgard.admin.asgard/object_browser.js');
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL . '/jQuery/thickbox/jquery-thickbox-3.1.pack.js');
        $_MIDCOM->add_link_head
        (
            array
            (
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL . '/jQuery/thickbox/thickbox.css',
                'media' => 'screen',
            )
        );
        $_MIDCOM->add_jscript('var tb_pathToImage = "' . MIDCOM_STATIC_URL . '/jQuery/thickbox/loadingAnimation.gif"');

        // Add jQuery file for the checkbox operations
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL . '/midgard.admin.asgard/jquery-copytree.js');
        $_MIDCOM->add_jscript('jQuery(document).ready(function(){jQuery("#midgard_admin_asgard_copytree").tree_checker();})');

        return true;
    }

    /**
     * Shows the object to delete.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_delete($handler_id, &$data)
    {
        $data['view_object'] = $this->_datamanager->get_content_html();
        midcom_show_style('midgard_admin_asgard_header');

        midcom_show_style('midgard_admin_asgard_middle');

        // Initialize the tree
        $data['tree'] = new midgard_admin_asgard_copytree($this->_object, $data);
        $data['tree']->copy_tree = false;
        $data['tree']->inputs = false;
        
        $midgard_language = $_MIDCOM->i18n->get_midgard_language();
        
        // Current language is not zero, selective delete to prevent deleting too much of objects.
        // Object will not be deleted if it doesn't have a language property at all or if its
        // language property is not the one requested for deletion
        if (   $midgard_language !== 0
            && (   !isset($this->_object->lang)
                || $this->_object->lang !== $midgard_language))
        {
            midcom_show_style('midgard_admin_asgard_object_delete_language');
        }
        else
        {
            midcom_show_style('midgard_admin_asgard_object_delete');
        }

        midcom_show_style('midgard_admin_asgard_footer');
    }

    /**
     * Copy handler
     *
     * @access public
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_copy($handler_id, $args, &$data)
    {
        midgard_admin_asgard_plugin::init_language($handler_id, $args, $data);

        // Get the object that will be copied
        $this->_object = $_MIDCOM->dbfactory->get_object_by_guid($args[0]);

        static $targets = array();

        $mgdschema_class = midcom_helper_reflector::resolve_baseclass(get_class($this->_object));

        // Get the target details
        if (in_array($mgdschema_class, $targets))
        {
            $target = $targets[$mgdschema_class];
        }
        else
        {
            $target = midcom_helper_reflector::get_target_properties($this->_object);
        }

        // Load the schemadb for searching the parent object
        $this->_load_schemadb($target['class'], $target['parent']);

        // Add Thickbox
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL . '/midgard.admin.asgard/object_browser.js');
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL . '/jQuery/thickbox/jquery-thickbox-3.1.pack.js');
        $_MIDCOM->add_link_head
        (
            array
            (
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL . '/jQuery/thickbox/thickbox.css',
                'media' => 'screen',
            )
        );
        $_MIDCOM->add_jscript('var tb_pathToImage = "' . MIDCOM_STATIC_URL . '/jQuery/thickbox/loadingAnimation.gif"');

        // Add jQuery file for the checkbox operations
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL . '/midgard.admin.asgard/jquery-copytree.js');
        $_MIDCOM->add_jscript('jQuery(document).ready(function(){jQuery("#midgard_admin_asgard_copytree").tree_checker();})');


        // Add switch for copying parameters
        $this->_schemadb['object']->append_field
        (
            'parameters',
            array
            (
                'title'       => $this->_l10n->get('copy parameters'),
                'storage'     => null,
                'type'        => 'boolean',
                'widget'      => 'checkbox',
                'default'     => 1,
            )
        );

        // Add switch for copying metadata
        $this->_schemadb['object']->append_field
        (
            'metadata',
            array
            (
                'title'       => $this->_l10n->get('copy metadata'),
                'storage'     => null,
                'type'        => 'boolean',
                'widget'      => 'checkbox',
                'default'     => 1,
            )
        );

        // Change the name for the parent field
        $this->_schemadb['object']->fields[$target['parent']]['title'] = $_MIDCOM->i18n->get_string('choose the target', 'midgard.admin.asgard');

        // Load the nullstorage controller
        $this->_controller = midcom_helper_datamanager2_controller::create('nullstorage');
        $this->_controller->schemadb =& $this->_schemadb;

        if (!$this->_controller->initialize())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Failed to initialize the controller');
            // This will exit
        }

        $this->_prepare_request_data();

        // Process the form
        switch ($this->_controller->process_form())
        {
            case 'save':
                // Get the target information of the form
                $target['id'] = $data['controller']->datamanager->types[$target['parent']]->convert_to_storage();
                $parameters = $data['controller']->datamanager->types['parameters']->convert_to_storage();
                $metadata = $data['controller']->datamanager->types['metadata']->convert_to_storage();

                if ($handler_id === '____mfa-asgard-object_copy_tree')
                {
                    $exclude = array();

                    foreach ($_POST['all_objects'] as $guid)
                    {
                        if (!in_array($guid, $_POST['selected']))
                        {
                            $exclude[] = $guid;
                        }
                    }

                    $new_object = midcom_helper_reflector::copy_object_tree($this->_object, $target, $exclude, $parameters, $metadata);
                }
                else
                {
                    $new_object = midcom_helper_reflector::copy_object($this->_object->guid, $target, $parameters, $metadata);
                }

                if (   !$new_object
                    || !$new_object->guid)
                {
                    $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Failed to copy the object');
                }

                if ($handler_id === '____mfa-asgard-object_copy_tree')
                {
                    $_MIDCOM->uimessages->add($this->_l10n->get('midgard.admin.asgard'), $this->_l10n->get('copy successful, you have been relocated to the root of the new object tree'));
                }
                else
                {
                    $_MIDCOM->uimessages->add($this->_l10n->get('midgard.admin.asgard'), $this->_l10n->get('copy successful, you have been relocated to the new object'));
                }

                // Relocate to the newly created object
                $_MIDCOM->relocate("__mfa/asgard/object/{$this->_request_data['default_mode']}/{$new_object->guid}/");
                break;

            case 'cancel':
                $_MIDCOM->relocate("__mfa/asgard/object/{$this->_request_data['default_mode']}/{$args[0]}/");
        }

        // Common hooks for Asgard
        midgard_admin_asgard_plugin::bind_to_object($this->_object, $handler_id, $data);
        midgard_admin_asgard_plugin::get_common_toolbar($data);
        midgard_admin_asgard_plugin::finish_language($handler_id, $data);

        // Set the page title
        switch ($handler_id)
        {
            case '____mfa-asgard-object_copy_tree':
                $data['page_title'] = sprintf($_MIDCOM->i18n->get_string('copy %s and its descendants', 'midgard.admin.asgard'), $this->_object->$target['label']);
                break;
            default:
                $data['page_title'] = sprintf($_MIDCOM->i18n->get_string('copy %s', 'midgard.admin.asgard'), $this->_object->$target['label']);

        }

        $data['target'] = $target;

        return true;
    }

    /**
     * Show copy style
     *
     * @access public
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_copy($handler_id, &$data)
    {
        midcom_show_style('midgard_admin_asgard_header');

        midcom_show_style('midgard_admin_asgard_middle');

        // Show the tree hieararchy
        if ($handler_id === '____mfa-asgard-object_copy_tree')
        {
            $data['tree'] = new midgard_admin_asgard_copytree($this->_object, $data);
            $data['tree']->inputs = true;
            $data['tree']->copy_tree = true;

            midcom_show_style('midgard_admin_asgard_object_copytree');
        }
        else
        {
            // Show the copy page
            midcom_show_style('midgard_admin_asgard_object_copy');
        }
        midcom_show_style('midgard_admin_asgard_footer');
    }
    
    /**
     * Set the object language if applicable
     * 
     * @access private
     */
    function _set_object_language($args = array())
    {
        // Set the language if requested
        if (!isset($this->_object->lang))
        {
            return false;
        }
        
        switch (true)
        {
            case (isset($args[1])):
                $lang = $_MIDCOM->i18n->code_to_id($args[1]);
                break;
            case ($_MIDCOM->i18n->get_content_language()):
                // This doesn't seem to have the wished effect
                return;
                $lang = $_MIDCOM->i18n->code_to_id($_MIDCOM->i18n->get_content_language());
                break;
        }
        
        $this->_object->lang = $lang;
    }
}
?>