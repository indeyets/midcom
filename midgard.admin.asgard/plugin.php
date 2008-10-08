<?php
/**
 * @package midgard.admin.asgard
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: acl_editor.php 5538 2007-03-20 13:22:41Z rambo $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Plugin interface
 * @package midgard.admin.asgard
 */
class midgard_admin_asgard_plugin extends midcom_baseclasses_components_handler
{
    /**
     * Get the plugin handlers, which act alike with Request Switches of MidCOM
     * Baseclasses Components (midcom.baseclasses.components.request)
     *
     * @access public
     * @return mixed Array of the plugin handlers
     */
    function get_plugin_handlers()
    {
        $_MIDCOM->load_library('midgard.admin.asgard');
        $_MIDCOM->auth->require_user_do('midgard.admin.asgard:access', null, 'midgard_admin_asgard_plugin');
        // Disable content caching
        $_MIDCOM->cache->content->no_cache();

        // Preferred language
        if (($language = midgard_admin_asgard_plugin::get_preference('interface_language')))
        {
            $_MIDCOM->i18n->set_language($language);
        }

        return array
        (
            /**
             * Asgard "welcome page"
             *
             * Match /asgard/
             */
            'welcome' => array
            (
                'handler' => array ('midgard_admin_asgard_handler_welcome', 'welcome'),
                'fixed_args' => array(),
                'variable_args' => 0,
            ),
            /**
             * Component listing page
             *
             * Match /components/
             */
            'components' => array
            (
                'handler' => array ('midgard_admin_asgard_handler_components', 'list'),
                'fixed_args' => array('components'),
                'variable_args' => 0,
            ),
            /**
             * Component listing page
             *
             * Match /components/
             */
            'components_component' => array
            (
                'handler' => array ('midgard_admin_asgard_handler_components', 'component'),
                'fixed_args' => array('components'),
                'variable_args' => 1,
            ),
            /**
             * Component configuration view
             *
             * Match /components/configuration/<component>
             */
            'components_configuration' => array
            (
                'handler' => array ('midgard_admin_asgard_handler_component_configuration', 'view'),
                'fixed_args' => array('components', 'configuration'),
                'variable_args' => 1,
            ),
            /**
             * Component configuration editor
             *
             * Match /components/configuration/edit/<component>
             */
            'components_configuration_edit' => array
            (
                'handler' => array ('midgard_admin_asgard_handler_component_configuration', 'edit'),
                'fixed_args' => array('components', 'configuration', 'edit'),
                'variable_args' => 1,
            ),
            /**
             * Component configuration editor
             *
             * Match /components/configuration/edit/<component>
             */
            'components_configuration_edit_folder' => array
            (
                'handler' => array ('midgard_admin_asgard_handler_component_configuration', 'edit'),
                'fixed_args' => array('components', 'configuration', 'edit'),
                'variable_args' => 2,
            ),
            /**
             * Trashed items of MgdSchema
             *
             * Match /asgard/
             */
            'trash' => array
            (
                'handler' => array ('midgard_admin_asgard_handler_undelete', 'trash'),
                'fixed_args' => array('trash'),
            ),
            /**
             * User preferences page
             *
             * Match /preferences/
             */
            'preferences' => array
            (
                'handler' => array ('midgard_admin_asgard_handler_preferences', 'preferences'),
                'fixed_args' => array('preferences'),
                'variable_args' => 0,
            ),
            /**
             * AJAX interface for remembering user preferences set on the fly
             *
             * Match /preferences/ajax/
             */
            'preferences_ajax' => array
            (
                'handler' => array ('midgard_admin_asgard_handler_preferences', 'ajax'),
                'fixed_args' => array('preferences', 'ajax'),
                'variable_args' => 0,
            ),
            /**
             * User preferences page for any person
             *
             * Match /preferences/<person guid>/
             */
            'preferences_guid' => array
            (
                'handler' => array ('midgard_admin_asgard_handler_preferences', 'preferences'),
                'fixed_args' => array('preferences'),
                'variable_args' => 1,
            ),
            /**
             * Front page of a MgdSchema
             *
             * Match /asgard/
             */
            'type' => array
            (
                'handler' => array ('midgard_admin_asgard_handler_type', 'type'),
                'fixed_args' => array(),
                'variable_args' => 1,
            ),
            /**
             * Trashed items of MgdSchema
             *
             * Match /asgard/
             */
            'trash_type' => array
            (
                'handler' => array ('midgard_admin_asgard_handler_undelete', 'trash_type'),
                'fixed_args' => array('trash'),
                'variable_args' => 1,
            ),
            /**
             * View an object
             *
             * Match /asgard/object/view/<guid>/
             */
            'object_view' => array
            (
                'handler' => array ('midgard_admin_asgard_handler_object_manage', 'view'),
                'fixed_args' => array ('object', 'view'),
                'variable_args' => 1,
            ),
            /**
             * View an object in another language
             *
             * Match /asgard/object/view/<guid>/<lang>
             */
            'object_view_lang' => array
            (
                'handler' => array ('midgard_admin_asgard_handler_object_manage', 'view'),
                'fixed_args' => array ('object', 'view'),
                'variable_args' => 2,
            ),
            /**
             * Edit an object
             *
             * Match /asgard/object/edit/<guid>/
             */
            'object_edit' => array
            (
                'handler' => array ('midgard_admin_asgard_handler_object_manage', 'edit'),
                'fixed_args' => array ('object', 'edit'),
                'variable_args' => 1,
            ),
            /**
             * Edit an object
             *
             * Match /asgard/object/edit/<guid>/<lang>
             */
            'object_edit_lang' => array
            (
                'handler' => array ('midgard_admin_asgard_handler_object_manage', 'edit'),
                'fixed_args' => array ('object', 'edit'),
                'variable_args' => 2,
            ),
            /**
             * Edit object metadata
             *
             * Match /asgard/object/metadata/<guid>/
             */
            'object_metadata' => array
            (
                'handler' => array ('midgard_admin_asgard_handler_object_metadata', 'edit'),
                'fixed_args' => array ('object', 'metadata'),
                'variable_args' => 1,
            ),
            /**
             * Edit object parameters
             *
             * Match /asgard/object/parameters/<guid>/
             */
            'object_parameters' => array
            (
                'handler' => array ('midgard_admin_asgard_handler_object_parameters', 'edit'),
                'fixed_args' => array ('object', 'parameters'),
                'variable_args' => 1,
            ),
            /**
             * Edit object permissions
             *
             * Match /asgard/object/permissions/<guid>/
             */
            'object_permissions' => array
            (
                'handler' => array ('midgard_admin_asgard_handler_object_permissions', 'edit'),
                'fixed_args' => array ('object', 'permissions'),
                'variable_args' => 1,
            ),
            /**
             * Copy object
             *
             * Match /asgard/object/copy/<guid>/
             */
            'object_copy' => array
            (
                'handler' => array ('midgard_admin_asgard_handler_object_manage', 'copy'),
                'fixed_args' => array ('object', 'copy'),
                'variable_args' => 1,
            ),
            /**
             * Copy object tree
             *
             * Match /asgard/object/copy/tree/<guid>/
             */
            'object_copy_tree' => array
            (
                'handler' => array ('midgard_admin_asgard_handler_object_manage', 'copy'),
                'fixed_args' => array ('object', 'copy', 'tree'),
                'variable_args' => 1,
            ),
            /**
             * Create a new file
             *
             * Match /files/
             */
            'object_attachments' => array
            (
                'handler' => array ('midgard_admin_asgard_handler_object_attachments', 'create'),
                'fixed_args' => array ('object', 'attachments'),
                'variable_args' => 1,
            ),
            /**
             * Edit a file
             *
             * Match /files/<filename>
             */
            'object_attachments_edit' => array
            (
                'handler' => array ('midgard_admin_asgard_handler_object_attachments', 'edit'),
                'fixed_args' => array ('object', 'attachments'),
                'variable_args' => 2,
            ),
            /**
             * Delete a file
             *
             * Match /files/<filename>/delete/
             */
            'object_attachments_delete' => array
            (
                'handler' => array ('midgard_admin_asgard_handler_object_attachments', 'delete'),
                'fixed_args' => array ('object', 'attachments', 'delete'),
                'variable_args' => 2,
            ),
            /**
             * Create a toplevel object with chooser
             *
             * Match /asgard/object/create/type/<parent guid>/
             */
            'object_create_chooser' => array
            (
                'handler' => array ('midgard_admin_asgard_handler_object_manage', 'create'),
                'fixed_args' => array ('object', 'create', 'chooser'),
                'variable_args' => 1,
            ),
            /**
             * Create an object
             *
             * Match /asgard/object/create/type/<parent guid>/
             */
            'object_create' => array
            (
                'handler' => array ('midgard_admin_asgard_handler_object_manage', 'create'),
                'fixed_args' => array ('object', 'create'),
                'variable_args' => 2,
            ),

            /**
             * Create a toplevel object
             *
             * Match /asgard/object/create/type/<parent guid>/
             */
            'object_create_toplevel' => array
            (
                'handler' => array ('midgard_admin_asgard_handler_object_manage', 'create'),
                'fixed_args' => array ('object', 'create'),
                'variable_args' => 1,
            ),

            /**
             * Delete an object
             *
             * Match /asgard/object/delete/<guid>/
             */
            'object_delete' => array
            (
                'handler' => array ('midgard_admin_asgard_handler_object_manage', 'delete'),
                'fixed_args' => array ('object', 'delete'),
                'variable_args' => 1,
            ),
            /**
             * Delete an object in language
             *
             * Match /asgard/object/delete/<guid>/<lang>
             */
            'object_delete_lang' => array
            (
                'handler' => array ('midgard_admin_asgard_handler_object_manage', 'delete'),
                'fixed_args' => array ('object', 'delete'),
                'variable_args' => 2,
            ),
            /**
             * Revision control system of an object.
             */
            /**
             * RCS history
             *
             * Match /asgard/object/rcs/<guid>
             */
            'object_rcs_history' => array
            (
                'handler' => array('midgard_admin_asgard_handler_object_rcs','history'),
                'fixed_args' => array ('object', 'rcs'),
                'variable_args' => 1,
            ),
            /**
             * RCS history
             *
             * Match /asgard/object/rcs/<guid>
             */
            'object_rcs_preview' => array
            (
                'handler' => array('midgard_admin_asgard_handler_object_rcs','preview'),
                'fixed_args' => array('object', 'rcs', 'preview'),
                'variable_args' => 2,
            ),
            'object_rcs_diff' => array
            (
                'handler' => array('midgard_admin_asgard_handler_object_rcs','diff'),
                'fixed_args' => array('object', 'rcs', 'diff'),
                'variable_args' => 3,
            ),
            'object_rcs_restore' => array
            (
                'handler' => array('midgard_admin_asgard_handler_object_rcs','restore'),
                'fixed_args' => array('object', 'rcs', 'restore'),
                'variable_args' => 2,
            ),
        );
    }

    /**
     * Static method other plugins may use
     * 
     * @static
     * @access public
     * @param string $title     Page title
     * @param array &$data      Local request data
     */
    function prepare_plugin($title, &$data)
    {
        $_MIDCOM->auth->require_user_do('midgard.admin.asgard:access', null, 'midgard_admin_asgard_plugin');
        // Disable content caching
        $_MIDCOM->cache->content->no_cache();
        $data['view_title'] = $title;
        $data['asgard_toolbar'] = new midcom_helper_toolbar();
        
        // Preferred language
        if (($language = midgard_admin_asgard_plugin::get_preference('interface_language')))
        {
            $_MIDCOM->i18n->set_language($language);
        }

        $_MIDCOM->skip_page_style = true;
        $_MIDCOM->style->prepend_component_styledir('midgard.admin.asgard');
        $_MIDCOM->style->prepend_component_styledir(str_replace('asgard_','',$data['plugin_name']));
    }

    function get_type_label($type)
    {
        $ref = midcom_helper_reflector_tree::get($type);
        return $ref->get_class_label();
    }

    function init_language($handler_id, $args, &$data)
    {
        switch ($handler_id)
        {
            case '____mfa-asgard-object_view_lang':
            case '____mfa-asgard-object_edit_lang':
            case '____mfa-asgard-object_delete_lang':
                $data['language_code'] = $args[1];
                $data['original_language'] = $_MIDGARD['lang'];
                $lang_qb = midcom_baseclasses_database_language::new_query_builder();
                $lang_qb->add_constraint('code', '=', $data['language_code']);
                $langs = $lang_qb->execute();
                if (count($langs) == 0)
                {
                    $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "Language '{$args[1]}' was not found.");
                    // This will exit.
                }
                mgd_set_lang($langs[0]->id);
                break;
            default:
                $data['language_code'] = '';
        }
    }

    function finish_language($handler_id, &$data)
    {
        switch ($handler_id)
        {
            case '____mfa-asgard-object_view_lang':
            case '____mfa-asgard-object_edit_lang':
            case '____mfa-asgard-object_delete_lang':
                mgd_set_lang($data['original_language']);
                break;
        }
    }

    /**
     * Static method for binding view to an object
     */
    function bind_to_object($object, $handler_id, &$data)
    {
        // Tell our object to MidCOM
        $_MIDCOM->bind_view_to_object($object);
        $_MIDCOM->set_26_request_metadata($object->metadata->revised, $object->guid);
        $data['object_reflector'] = midcom_helper_reflector::get($object);
        $data['tree_reflector'] = midcom_helper_reflector_tree::get($object);

        $data['object'] =& $object;

        // Populate toolbar
        $data['asgard_toolbar'] = midgard_admin_asgard_plugin::get_object_toolbar($object, $handler_id, &$data);
        midgard_admin_asgard_plugin::get_common_toolbar($data);


        // Figure out correct title and language handling
        switch ($handler_id)
        {
            case '____mfa-asgard-object_edit':
            case '____mfa-asgard-object_edit_lang':
                $title_string = $_MIDCOM->i18n->get_string('edit %s %s', 'midgard.admin.asgard');
                break;
            case '____mfa-asgard-object_metadata':
                $title_string = $_MIDCOM->i18n->get_string('metadata of %s %s', 'midgard.admin.asgard');
                break;
            case '____mfa-asgard-object_attachments':
            case '____mfa-asgard-object_attachments_edit':
            case '____mfa-asgard-object_attachments_delete':
                $title_string = $_MIDCOM->i18n->get_string('attachments of %s %s', 'midgard.admin.asgard');
                break;
            case '____mfa-asgard-object_parameters':
                $title_string = $_MIDCOM->i18n->get_string('parameters of %s %s', 'midgard.admin.asgard');
                break;
            case '____mfa-asgard-object_permissions':
                // Figure out label for the object's class
                switch (get_class($this->_object))
                {
                    case 'midcom_baseclasses_database_topic':
                        $type = $_MIDCOM->i18n->get_string('folder', 'midgard.admin.acl');
                        break;
                    default:
                        $type_parts = explode('_', get_class($this->_object));
                        $type = $type_parts[count($type_parts)-1];
                }
                $title_string = sprintf($_MIDCOM->i18n->get_string('permissions for %s %s', 'midgard.admin.acl'), $type, midgard_admin_asgard_handler_object_permissions::resolve_object_title($this->_object));
                break;
            case '____mfa-asgard-object_create':
                $title_string = sprintf($_MIDCOM->i18n->get_string('create %s under %s', 'midgard.admin.asgard'), midgard_admin_asgard_plugin::get_type_label($data['new_type_arg']), '%s %s');
                break;
            case '____mfa-asgard-object_delete':
            case '____mfa-asgard-object_delete_lang':
                $title_string = $_MIDCOM->i18n->get_string('delete %s %s', 'midgard.admin.asgard');
                break;
            default:
                $title_string = $_MIDCOM->i18n->get_string('%s %s', 'midgard.admin.asgard');
                break;
        }
        $label = $data['object_reflector']->get_object_label($object);
        $type_label = midgard_admin_asgard_plugin::get_type_label(get_class($object));
        $data['view_title'] = sprintf($title_string, $type_label, $label);
        $_MIDCOM->set_pagetitle($data['view_title']);

    }
    
    /**
     * Helper function that sets the default object mode
     * 
     * @static
     * @access public
     */
    function get_default_mode(&$data)
    {
        //only set mode once per request
        if (!empty($data['default_mode']))
        {
            return $data['default_mode'];
        }
        
        if ($this->_config->get('edit_mode') == 1)
        {
            $data['default_mode'] = 'edit';
        }
        else
        {
            $data['default_mode'] = 'view';
        }
        
        if (midgard_admin_asgard_plugin::get_preference('edit_mode') == 1)
        {
            $data['default_mode'] = 'edit';
        }
        else
        {
            $data['default_mode'] = 'view';
        }
        
        return $data['default_mode'];
    }

    /**
     * Populate the object toolbar
     * 
     * @param mixed $object        MgdSchema object for which the toolbar will be created
     * @param String $handler_id   Initialized handler id
     * @param Array $data          Local request data
     */
    function get_object_toolbar($object, $handler_id, &$data)
    {
        $toolbar = new midcom_helper_toolbar();

        midgard_admin_asgard_plugin::get_default_mode(&$data);

        // Show view toolbar button, if the user hasn't configured to use straight the edit mode
        if ($data['default_mode'] === 'view')
        {
            $toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => "__mfa/asgard/object/view/{$object->guid}/{$data['language_code']}/",
                    MIDCOM_TOOLBAR_LABEL => $_MIDCOM->i18n->get_string('view', 'midcom'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/view.png',
                )
            );
        }

        $link = $_MIDCOM->permalinks->resolve_permalink($object->guid);
        if ($link)
        {
            $toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => $link,
                    MIDCOM_TOOLBAR_LABEL => $_MIDCOM->i18n->get_string('view on site', 'midgard.admin.asgard'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_internet.png',
                )
            );
        }

        if ($object->can_do('midgard:update'))
        {
            $toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => "__mfa/asgard/object/edit/{$object->guid}/{$data['language_code']}/",
                    MIDCOM_TOOLBAR_LABEL => $_MIDCOM->i18n->get_string('edit', 'midcom'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
                )
            );
        }

        if ($object->can_do('midgard:create'))
        {
            if (midcom_helper_reflector_tree::get_child_objects($object))
            {
                $toolbar->add_item
                (
                    array
                    (
                        MIDCOM_TOOLBAR_URL => "__mfa/asgard/object/copy/tree/{$object->guid}/",
                        MIDCOM_TOOLBAR_LABEL => $_MIDCOM->i18n->get_string('copy', 'midcom'),
                        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/editcopy.png',
                    )
                );
            }
            else
            {
                $toolbar->add_item
                (
                    array
                    (
                        MIDCOM_TOOLBAR_URL => "__mfa/asgard/object/copy/{$object->guid}/",
                        MIDCOM_TOOLBAR_LABEL => $_MIDCOM->i18n->get_string('copy', 'midcom'),
                        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/editcopy.png',
                    )
                );
            }
        }

        if ($object->can_do('midgard:update'))
        {
            if (   is_a($object, 'midgard_topic')
                && $object->component
                && $object->can_do('midcom:component_config'))
            {
                $toolbar->add_item
                (
                    array
                    (
                        MIDCOM_TOOLBAR_URL => "__mfa/asgard/components/configuration/edit/{$object->component}/{$object->guid}/",
                        MIDCOM_TOOLBAR_LABEL => $_MIDCOM->i18n->get_string('component configuration', 'midcom'),
                        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_folder-properties.png',
                    )
                );
            }

            $toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => "__mfa/asgard/object/metadata/{$object->guid}/",
                    MIDCOM_TOOLBAR_LABEL => $_MIDCOM->i18n->get_string('metadata', 'midcom'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/metadata.png',
                )
            );
            /** COPIED from midcom_services_toolbars */
            if ($GLOBALS['midcom_config']['metadata_approval'])
            {
                $metadata =& midcom_helper_metadata::retrieve($object);
                if (   $metadata
                    && $metadata->is_approved())
                {
                    $toolbar->add_item(Array(
                        MIDCOM_TOOLBAR_URL => "__ais/folder/unapprove/",
                        MIDCOM_TOOLBAR_LABEL => $_MIDCOM->i18n->get_string('unapprove', 'midcom'),
                        MIDCOM_TOOLBAR_HELPTEXT => $_MIDCOM->i18n->get_string('approved', 'midcom'),
                        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/approved.png',
                        MIDCOM_TOOLBAR_POST => true,
                        MIDCOM_TOOLBAR_POST_HIDDENARGS => Array
                        (
                            'guid' => $object->guid,
                            'return_to' => $_SERVER['REQUEST_URI'],
                        ),
                        MIDCOM_TOOLBAR_ACCESSKEY => 'u',
                        MIDCOM_TOOLBAR_ENABLED => $object->can_do('midcom:approve'),
                    ));
                }
                else
                {
                    $toolbar->add_item(Array(
                        MIDCOM_TOOLBAR_URL => "__ais/folder/approve/",
                        MIDCOM_TOOLBAR_LABEL => $_MIDCOM->i18n->get_string('approve', 'midcom'),
                        MIDCOM_TOOLBAR_HELPTEXT => $_MIDCOM->i18n->get_string('unapproved', 'midcom'),
                        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/not_approved.png',
                        MIDCOM_TOOLBAR_POST => true,
                        MIDCOM_TOOLBAR_POST_HIDDENARGS => Array
                        (
                            'guid' => $object->guid,
                            'return_to' => $_SERVER['REQUEST_URI'],
                        ),
                        MIDCOM_TOOLBAR_ACCESSKEY => 'a',
                        MIDCOM_TOOLBAR_ENABLED => $object->can_do('midcom:approve'),
                    ));
                }
            }
            /** /COPIED from midcom_services_toolbars */

            $toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => "__mfa/asgard/object/attachments/{$object->guid}/",
                    MIDCOM_TOOLBAR_LABEL => $_MIDCOM->i18n->get_string('attachments', 'midgard.admin.asgard'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/attach.png',
                )
            );

            $toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => "__mfa/asgard/object/parameters/{$object->guid}/",
                    MIDCOM_TOOLBAR_LABEL => $_MIDCOM->i18n->get_string('parameters', 'midcom'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/properties.png',
                    MIDCOM_TOOLBAR_ENABLED => $object->can_do('midgard:parameters'),
                )
            );

            $toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => "__mfa/asgard/object/permissions/{$object->guid}/",
                    MIDCOM_TOOLBAR_LABEL => $_MIDCOM->i18n->get_string('privileges', 'midcom'),
                    MIDCOM_TOOLBAR_ICON => 'midgard.admin.asgard/permissions-16.png',
                    MIDCOM_TOOLBAR_ENABLED => $object->can_do('midgard:privileges'),
                )
            );


            if (   array_key_exists('midcom.helper.replicator', $_MIDCOM->componentloader->manifests)
                && $_MIDCOM->auth->admin)
            {
                $toolbar->add_item
                (
                    array
                    (
                        MIDCOM_TOOLBAR_URL => "__mfa/asgard_midcom.helper.replicator/object/{$object->guid}/",
                        MIDCOM_TOOLBAR_LABEL => $_MIDCOM->i18n->get_string('replication information', 'midcom.helper.replicator'),
                        MIDCOM_TOOLBAR_ICON => 'midcom.helper.replicator/replicate-server-16.png',
                    )
                );
            }
        }

        if ($object->can_do('midgard:create'))
        {
            // Find out what types of children the object can have and show create buttons for them
            $child_types = $data['tree_reflector']->get_child_classes();
            foreach ($child_types as $type)
            {
                $display_button = true;
                if (is_a($object, 'midgard_topic'))
                {
                    // With topics we should check for component before populating create buttons as so many types can be children of topics
                    switch ($type)
                    {
                        case 'midgard_topic':
                        case 'midgard_article':
                            // Articles and topics can always be created
                            break;
                        default:
                            $midcom_dba_classname = $_MIDCOM->dbclassloader->get_midcom_class_name_for_legacy_midgard_class($type);
                            if (!$midcom_dba_classname)
                            {
                                $display_button = false;
                                break;
                            }
                            $component = $_MIDCOM->dbclassloader->_mgdschema_class_handler[$midcom_dba_classname];
                            if ($component != $object->component)
                            {
                                $display_button = false;
                            }
                            break;
                    }
                }
                elseif (   is_a($object, 'midgard_article')
                        && $object->topic)
                {
                    $topic = new midcom_db_topic($object->topic);
                    // With articles we should check for topic component before populating create buttons as so many types can be children of topics
                    switch ($type)
                    {
                        case 'midgard_article':
                            // Articles can always be created
                            break;
                        default:
                            $midcom_dba_classname = $_MIDCOM->dbclassloader->get_midcom_class_name_for_legacy_midgard_class($type);
                            $component = $_MIDCOM->dbclassloader->_mgdschema_class_handler[$midcom_dba_classname];
                            if ($component != $topic->component)
                            {
                                $display_button = false;
                            }
                            break;
                    }
                }

                if (!$display_button)
                {
                    // Skip this type
                    continue;
                }

                $toolbar->add_item
                (
                    array
                    (
                        MIDCOM_TOOLBAR_URL => "__mfa/asgard/object/create/{$type}/{$object->guid}/",
                        MIDCOM_TOOLBAR_LABEL => sprintf($_MIDCOM->i18n->get_string('create %s', 'midcom'), midgard_admin_asgard_plugin::get_type_label($type)),
                        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/' . $data['tree_reflector']->get_create_icon($type),
                    )
                );
            }
        }

        if ($object->can_do('midgard:delete'))
        {
            $toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => "__mfa/asgard/object/delete/{$object->guid}/{$data['language_code']}",
                    MIDCOM_TOOLBAR_LABEL => $_MIDCOM->i18n->get_string('delete', 'midcom'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
                )
            );
        }

        if ($object->can_do('midgard:update'))
        {
            $toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => "__mfa/asgard/object/rcs/{$object->guid}/",
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('show history'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/history.png',
                    MIDCOM_TOOLBAR_ENABLED => (substr($handler_id, 0, 25) === '____mfa-asgard-object_rcs') ? false : true,
                )
            );
        }
        $tmp = array();

        $breadcrumb = array();
        $label = $data['object_reflector']->get_object_label($object);
        $breadcrumb[] = array
        (
            MIDCOM_NAV_URL => "__mfa/asgard/object/view/{$object->guid}/{$data['language_code']}",
            MIDCOM_NAV_NAME => $label,
        );

        $parent = $object->get_parent();
        $i = 0;
        while (   is_object($parent)
               && $parent->guid
               && $i < 10)
        {
            $i++;
            $parent_reflector = midcom_helper_reflector::get($parent);
            $parent_label = $parent_reflector->get_object_label($parent);
            $breadcrumb[] = array
            (
                MIDCOM_NAV_URL => "__mfa/asgard/object/view/{$parent->guid}/{$data['language_code']}",
                MIDCOM_NAV_NAME => $parent_label,
            );
            $parent = $parent->get_parent();
        }
        $breadcrumb = array_reverse($breadcrumb);

        switch ($handler_id)
        {
            case '____mfa-asgard-object_view':
            case '____mfa-asgard-object_view_lang':
                $toolbar->disable_item("__mfa/asgard/object/view/{$object->guid}/{$data['language_code']}");
                break;
            case '____mfa-asgard-object_edit':
            case '____mfa-asgard-object_edit_lang':
                $breadcrumb[] = array
                (
                    MIDCOM_NAV_URL => "__mfa/asgard/object/edit/{$object->guid}/{$data['language_code']}",
                    MIDCOM_NAV_NAME => $_MIDCOM->i18n->get_string('edit', 'midcom'),
                );
                $toolbar->disable_item("__mfa/asgard/object/edit/{$object->guid}/{$data['language_code']}");
                break;
            case '____mfa-asgard-object_copy':
                $breadcrumb[] = array
                (
                    MIDCOM_NAV_URL => "__mfa/asgard/object/copy/{$object->guid}/",
                    MIDCOM_NAV_NAME => $_MIDCOM->i18n->get_string('copy', 'midcom'),
                );
                $toolbar->disable_item("__mfa/asgard/object/copy/{$object->guid}/{$data['language_code']}");
                break;
            case '____mfa-asgard-object_copy_tree':
                $breadcrumb[] = array
                (
                    MIDCOM_NAV_URL => "__mfa/asgard/object/copy/tree/{$object->guid}/",
                    MIDCOM_NAV_NAME => $_MIDCOM->i18n->get_string('copy', 'midcom'),
                );
                $toolbar->disable_item("__mfa/asgard/object/copy/tree/{$object->guid}/{$data['language_code']}");
                break;
            case '____mfa-asgard-components_configuration_edit_folder':
                $breadcrumb[] = array
                (
                    MIDCOM_NAV_URL => "__mfa/asgard/components/configuration/edit/{$object->component}/{$object->guid}/",
                    MIDCOM_NAV_NAME => $_MIDCOM->i18n->get_string('component configuration', 'midcom'),
                );
                $toolbar->disable_item("__mfa/asgard/components/configuration/edit/{$object->component}/{$object->guid}/");
                break;
            case '____mfa-asgard-object_metadata':
                $breadcrumb[] = array
                (
                    MIDCOM_NAV_URL => "__mfa/asgard/object/metadata/{$object->guid}/",
                    MIDCOM_NAV_NAME => $_MIDCOM->i18n->get_string('metadata', 'midcom'),
                );
                $toolbar->disable_item("__mfa/asgard/object/metadata/{$object->guid}/");
                break;
            case '____mfa-asgard-object_attachments':
            case '____mfa-asgard-object_attachments_edit':
            case '____mfa-asgard-object_attachments_delete':
                $breadcrumb[] = array
                (
                    MIDCOM_NAV_URL => "__mfa/asgard/object/attachments/{$object->guid}/",
                    MIDCOM_NAV_NAME => $_MIDCOM->i18n->get_string('attachments', 'midgard.admin.asgard'),
                );
                
                if ($handler_id == '____mfa-asgard-object_attachments_edit')
                {
                    $breadcrumb[] = array
                    (
                        MIDCOM_NAV_URL => "__mfa/asgard/object/attachments/{$object->guid}/edit",
                        MIDCOM_NAV_NAME => $_MIDCOM->i18n->get_string('edit', 'midcom'),
                    );
                }
                if ($handler_id == '____mfa-asgard-object_attachments_delete')
                {
                    $breadcrumb[] = array
                    (
                        MIDCOM_NAV_URL => "__mfa/asgard/object/attachments/{$object->guid}/delete",
                        MIDCOM_NAV_NAME => $_MIDCOM->i18n->get_string('delete', 'midcom'),
                    );
                }

                $toolbar->disable_item("__mfa/asgard/object/attachments/{$object->guid}/");
                break;
            case '____mfa-asgard-object_parameters':
                $breadcrumb[] = array
                (
                    MIDCOM_NAV_URL => "__mfa/asgard/object/parameters/{$object->guid}/",
                    MIDCOM_NAV_NAME => $_MIDCOM->i18n->get_string('parameters', 'midcom'),
                );
                $toolbar->disable_item("__mfa/asgard/object/parameters/{$object->guid}/");
                break;
            case '____mfa-asgard-object_permissions':
                $breadcrumb[] = array
                (
                    MIDCOM_NAV_URL => "__mfa/asgard/object/permissions/{$object->guid}/",
                    MIDCOM_NAV_NAME => $_MIDCOM->i18n->get_string('privileges', 'midcom'),
                );
                $toolbar->disable_item("__mfa/asgard/object/permissions/{$object->guid}/");
                break;
            case '____mfa-asgard-object_create':
                $breadcrumb[] = array
                (
                    MIDCOM_NAV_URL => "__mfa/asgard/object/create/{$data['new_type_arg']}/{$object->guid}/",
                    MIDCOM_NAV_NAME => sprintf($_MIDCOM->i18n->get_string('create %s', 'midcom'), midgard_admin_asgard_plugin::get_type_label($data['new_type_arg'])),
                );
                $toolbar->disable_item("__mfa/asgard/object/create/{$data['new_type_arg']}/{$object->guid}/");
                break;
            case '____mfa-asgard-object_delete':
            case '____mfa-asgard-object_delete_lang':
                $breadcrumb[] = array
                (
                    MIDCOM_NAV_URL => "__mfa/asgard/object/delete/{$object->guid}/{$data['language_code']}",
                    MIDCOM_NAV_NAME => $_MIDCOM->i18n->get_string('delete', 'midcom'),
                );
                $toolbar->disable_item("__mfa/asgard/object/delete/{$object->guid}/{$data['language_code']}");
                break;
            case '____mfa-asgard_midcom.helper.replicator-object':
                $breadcrumb[] = array
                (
                    MIDCOM_NAV_URL => "__mfa/asgard_midcom.helper.replicator/object/{$object->guid}/",
                    MIDCOM_NAV_NAME => $_MIDCOM->i18n->get_string('replication information', 'midcom.helper.replicator'),
                );
                $toolbar->disable_item("__mfa/asgard_midcom.helper.replicator/object/{$object->guid}/");
                break;
            case '____mfa-asgard-object_rcs_diff':
                $tmp[] = Array
                (
                    MIDCOM_NAV_URL => "__mfa/asgard/object/rcs/preview/{$this->_object->guid}/{$data['args'][1]}/{$data['args'][2]}",
                    MIDCOM_NAV_NAME => sprintf($this->_l10n->get('differences between %s and %s'), $data['args'][1], $data['args'][2]),
                );

            case '____mfa-asgard-object_rcs_preview':
                if (isset($data['args'][2]))
                {
                    $current = $data['args'][2];
                }
                else
                {
                    $current = $data['args'][1];
                }

                $tmp[] = Array
                (
                    MIDCOM_NAV_URL => "__mfa/asgard/object/rcs/preview/{$this->_object->guid}/{$current}/",
                    MIDCOM_NAV_NAME => sprintf($this->_l10n->get('version %s'), $current),
                );

            case '____mfa-asgard-object_rcs_history':
                $tmp[] = Array
                (
                    MIDCOM_NAV_URL => "__mfa/asgard/object/rcs/{$this->_object->guid}/",
                    MIDCOM_NAV_NAME => $this->_l10n->get('show history'),
                );

                $tmp = array_reverse($tmp);

                $breadcrumb = array_merge($breadcrumb, $tmp);

                break;
        }

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $breadcrumb);

        return $toolbar;
    }
    
    /**
     * Add Asgard styling for plugins
     * 
     * @static
     * @access public
     */
    function asgard_header()
    {
        midcom_show_style('midgard_admin_asgard_header');
        midcom_show_style('midgard_admin_asgard_middle');
    }

    /**
     * Add Asgard styling for plugins
     * 
     * @static
     * @access public
     */
    function asgard_footer()
    {
        midcom_show_style('midgard_admin_asgard_footer');
    }

    function get_common_toolbar(&$data)
    {
    }

    /**
     * Get a preference for the current user
     *
     * @static
     * @access public
     * @param string $preference    Name of the preference
     */
    function get_preference($preference)
    {
        static $preferences = array();
        
        if (!isset($preferences[$preference]))
        {
            // Store the person statically
            if (!isset($preferences[$_MIDCOM->auth->user->guid]))
            {
                $preferences[$_MIDCOM->auth->user->guid] = new midcom_db_person($_MIDCOM->auth->user->guid);
            }
            
            $preferences[$preference] = $preferences[$_MIDCOM->auth->user->guid]->get_parameter('midgard.admin.asgard:preferences', $preference);
        }
        
        return $preferences[$preference];
    }

    /**
     * Get last visited object for the current user
     *
     * @static
     * @access public
     * @param array $last_visited    Array of guid => object pairs
     */
    function get_last_visited()
    {
        $last_visited_guids = midgard_admin_asgard_plugin::get_preference('last_visited');
        $last_visited_guids = unserialize($last_visited_guids);
        
        $last_visited = array();
        
        if (empty($last_visited_guids))
        {
            return $last_visited;
        }

        foreach ($last_visited_guids as $guid)
        {
            $object = $_MIDCOM->dbfactory->get_object_by_guid($guid);
            $last_visited[$guid] = $object;
        }

        return $last_visited;
    }

    /**
     * Set last visited parameter for the current user
     *
     * @static
     * @access public
     * @param string $guid    GUID of the object to add
     */
    function set_last_visited($guid)
    {
        $last_visited_orig = array_keys(midgard_admin_asgard_plugin::get_last_visited());
        $last_visited_new = Array();

        if (sizeof($last_visited_orig) > 0)
        {
            // remove copies of the same GUID from history
            if (in_array($guid, $last_visited_orig))
            {
                foreach ($last_visited_orig as $orig_guid)
                {
                    if ($orig_guid != $guid)
                    {
                        $last_visited_new[] = $orig_guid;
                    }
                }
            }
            else 
        {
        $last_visited_new = $last_visited_orig;

                // remove the oldest entry if maximum size is reached
        if (sizeof($last_visited_orig) == $this->_config->get('last_visited_size'))
        {
            $remove_candidate = array_pop($last_visited_new);
        }
        }
        }

        // now, add the new guid
        array_unshift($last_visited_new, $guid);

    // if something has changed, update accordingly
        if (serialize($last_visited_orig) != serialize($last_visited_new))
        {
            $user = new midcom_db_person($_MIDCOM->auth->user->guid);
            
            $user->set_parameter('midgard.admin.asgard:preferences', 'last_visited', serialize($last_visited_new));
        }
    }
    
    /**
     * Get the MgdSchema root classes
     * 
     * @static
     * @access public
     * @return Array containing class name and translated name
     */
    function get_root_classes()
    {
        static $root_classes = array();
        
        // Return cached results
        if (!empty($root_classes))
        {
            return $root_classes;
        }
        
        // Initialize the returnable array
        $root_classes = array();
        
        // Get the classes
        $classes = midcom_helper_reflector_tree::get_root_classes();
        
        // Get the translated name
        foreach ($classes as $class)
        {
            $ref = new midcom_helper_reflector($class);
            $root_classes[$class] = $ref->get_class_label();
        }
        
        asort($root_classes);
        
        return $root_classes;
    }
}
?>