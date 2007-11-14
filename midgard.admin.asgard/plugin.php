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
        );
    }
    
    /**
     * Static method other plugins may use
     */
    function prepare_plugin($title, &$data)
    {
        $_MIDCOM->auth->require_user_do('midgard.admin.asgard:access', null, 'midgard_admin_asgard_plugin');    
        $data['view_title'] = $title;
        $data['asgard_toolbar'] = new midcom_helper_toolbar();
        $_MIDCOM->skip_page_style = true;
        $_MIDCOM->style->prepend_component_styledir('midgard.admin.asgard');
        $_MIDCOM->style->prepend_component_styledir(str_replace('asgard_','',$data['plugin_name']));
    }
    
    function get_type_label($type)
    {
        $ref = midgard_admin_asgard_reflector_tree::get($type);
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
        $data['object_reflector'] = midgard_admin_asgard_reflector::get($object);
        $data['tree_reflector'] = midgard_admin_asgard_reflector_tree::get($object);
        
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
     * Static method for populating the object toolbar
     */
    function get_object_toolbar($object, $handler_id, &$data)
    {
        $toolbar = new midcom_helper_toolbar();
        $toolbar->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => "__mfa/asgard/object/view/{$object->guid}/{$data['language_code']}",
                MIDCOM_TOOLBAR_LABEL => $_MIDCOM->i18n->get_string('view', 'midcom'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/view.png',
            )
        );
        
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
                    MIDCOM_TOOLBAR_URL => "__mfa/asgard/object/edit/{$object->guid}/{$data['language_code']}",
                    MIDCOM_TOOLBAR_LABEL => $_MIDCOM->i18n->get_string('edit', 'midcom'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
                )
            );
            
            $toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => "__mfa/asgard/object/metadata/{$object->guid}/",
                    MIDCOM_TOOLBAR_LABEL => $_MIDCOM->i18n->get_string('metadata', 'midcom'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/metadata.png',
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
                if ($type == 'midgard_topic')
                {
                   $toolbar->add_item
                    (
                        array
                        (
                            MIDCOM_TOOLBAR_URL => "__mfa/asgard/object/create/{$type}/{$object->guid}/",
                            MIDCOM_TOOLBAR_LABEL => sprintf($_MIDCOM->i18n->get_string('create %s', 'midcom'), midgard_admin_asgard_plugin::get_type_label($type)),
                            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/new-dir.png',
                            MIDCOM_TOOLBAR_ENABLED => $object->can_do('midcom.admin.folder:topic_management'),
                        )
                    );
                }
                else
                {
                    $toolbar->add_item
                    (
                        array
                        (
                            MIDCOM_TOOLBAR_URL => "__mfa/asgard/object/create/{$type}/{$object->guid}/",
                            MIDCOM_TOOLBAR_LABEL => sprintf($_MIDCOM->i18n->get_string('create %s', 'midcom'), midgard_admin_asgard_plugin::get_type_label($type)),
                            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/new-text.png',
                        )
                    );
                }
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
            $parent_reflector = midgard_admin_asgard_reflector::get($parent);
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
            case '____mfa-asgard-object_metadata':
                $breadcrumb[] = array
                (
                    MIDCOM_NAV_URL => "__mfa/asgard/object/metadata/{$object->guid}/",
                    MIDCOM_NAV_NAME => $_MIDCOM->i18n->get_string('metadata', 'midcom'),
                );
                $toolbar->disable_item("__mfa/asgard/object/metadata/{$object->guid}/");
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
        }
        
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $breadcrumb);
        
        return $toolbar;
    }

    // Add Asgard styling for plugins

    function asgard_header()
    {
        midcom_show_style('midgard_admin_asgard_header');
        midcom_show_style('midgard_admin_asgard_middle');
    }

    function asgard_footer()
    {
        midcom_show_style('midgard_admin_asgard_footer');
    }

    function get_common_toolbar(&$data)
    {
        $data['asgard_toolbar']->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)."__mfa/asgard/",
                MIDCOM_TOOLBAR_LABEL => $_MIDCOM->i18n->get_string('asgard homepage', 'midgard.admin.asgard'),
                MIDCOM_TOOLBAR_ICON => 'midgard.admin.asgard/asgard2-16.png',
            )
        );

        // Add link to site
        $data['asgard_toolbar']->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX),
                MIDCOM_TOOLBAR_LABEL => $_MIDCOM->i18n->get_string('back to site', 'midgard.admin.asgard'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/gohome.png',
            )
        );

        $data['asgard_toolbar']->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)."midcom-logout-",
                MIDCOM_TOOLBAR_LABEL => $_MIDCOM->i18n->get_string('logout','midcom'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/exit.png',
            )
        );
    }
}
?>