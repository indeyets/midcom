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
        $_MIDCOM->auth->require_valid_user();
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
             * Front page of an MgdSchema
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
        );
    }
    
    function get_type_label($type)
    {
        // Figure out nice short name for the type
        $type_parts = explode('_', $type);
        $type_label = $type_parts[count($type_parts) - 1];
        if (   $type_label == 'dba'
            || $type_label == 'db')
        {
            $type_label = $type_parts[count($type_parts) - 2];
        }
        return $_MIDCOM->i18n->get_string($type_label, 'midgard.admin.asgard');
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
        
        // Figure out correct title
        switch ($handler_id)
        {
            case '____mfa-asgard-object_edit':
                $title_string = $_MIDCOM->i18n->get_string('edit %s %s', 'midgard.admin.asgard');
                break;
            case '____mfa-asgard-object_metadata':
                $title_string = $_MIDCOM->i18n->get_string('metadata of %s %s', 'midgard.admin.asgard');
                break;
            case '____mfa-asgard-object_parameters':
                $title_string = $_MIDCOM->i18n->get_string('parameters of %s %s', 'midgard.admin.asgard');
                break;
            case '____mfa-asgard-object_create':
                $title_string = sprintf($_MIDCOM->i18n->get_string('create %s under %s', 'midgard.admin.asgard'), midgard_admin_asgard_plugin::get_type_label($data['new_type_arg']), '%s %s');
                break;
            case '____mfa-asgard-object_delete':
                $title_string = $_MIDCOM->i18n->get_string('delete %s %s', 'midgard.admin.asgard');
                break;
            default:
                $title_string = $_MIDCOM->i18n->get_string('%s %s', 'midgard.admin.asgard');
                break;
        }
        $label_property = $data['object_reflector']->get_label_property();
        $type_label = midgard_admin_asgard_plugin::get_type_label(get_class($object));
        $data['view_title'] = sprintf($title_string, $type_label, $object->$label_property);
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
                MIDCOM_TOOLBAR_URL => "__mfa/asgard/object/view/{$object->guid}/",
                MIDCOM_TOOLBAR_LABEL => $_MIDCOM->i18n->get_string('view', 'midcom'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/view.png',
            )
        );
            
        if ($object->can_do('midgard:update'))
        {
            $toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => "__mfa/asgard/object/edit/{$object->guid}/",
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
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/properties.png',
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
                    MIDCOM_TOOLBAR_URL => "__mfa/asgard/object/delete/{$object->guid}/",
                    MIDCOM_TOOLBAR_LABEL => $_MIDCOM->i18n->get_string('delete', 'midcom'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
                )
            );
        }

        $breadcrumb = array();
        $label_property = $data['object_reflector']->get_label_property();
        $breadcrumb[] = array
        (
            MIDCOM_NAV_URL => "__mfa/asgard/object/view/{$object->guid}/",
            MIDCOM_NAV_NAME => $object->$label_property,
        );
                
        $parent = $object->get_parent();
        $i = 0;
        while (   is_object($parent)
               && $parent->guid
               && $i < 10)
        {
            $i++;
            $parent_reflector = midgard_admin_asgard_reflector::get($parent);
            $parent_label_property = $parent_reflector->get_label_property();
            $breadcrumb[] = array
            (
                MIDCOM_NAV_URL => "__mfa/asgard/object/view/{$parent->guid}/",
                MIDCOM_NAV_NAME => $parent->$parent_label_property,
            );
            $parent = $parent->get_parent();
        }
        $breadcrumb = array_reverse($breadcrumb);

        switch ($handler_id)
        {
            case '____mfa-asgard-object_view':
                $toolbar->disable_item("__mfa/asgard/object/view/{$object->guid}/");
                break;
            case '____mfa-asgard-object_edit':
                $breadcrumb[] = array
                (
                    MIDCOM_NAV_URL => "__mfa/asgard/object/edit/{$object->guid}/",
                    MIDCOM_NAV_NAME => $_MIDCOM->i18n->get_string('edit', 'midcom'),
                );
                $toolbar->disable_item("__mfa/asgard/object/edit/{$object->guid}/");
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
            case '____mfa-asgard-object_create':
                $breadcrumb[] = array
                (
                    MIDCOM_NAV_URL => "__mfa/asgard/object/create/{$data['new_type_arg']}/{$object->guid}/",
                    MIDCOM_NAV_NAME => sprintf($_MIDCOM->i18n->get_string('create %s', 'midcom'), midgard_admin_asgard_plugin::get_type_label($data['new_type_arg'])),
                );
                $toolbar->disable_item("__mfa/asgard/object/create/{$data['new_type_arg']}/{$object->guid}/");
                break;
            case '____mfa-asgard-object_delete':
                $breadcrumb[] = array
                (
                    MIDCOM_NAV_URL => "__mfa/asgard/object/delete/{$object->guid}/",
                    MIDCOM_NAV_NAME => $_MIDCOM->i18n->get_string('delete', 'midcom'),
                );
                $toolbar->disable_item("__mfa/asgard/object/delete/{$object->guid}/");
                break;
        }
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $breadcrumb);
        
        return $toolbar;
    }
}
?>