<?php
/**
 * @package midgard.admin.acl
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Basic handler class
 * @package midgard.admin.acl
 */
class midgard_admin_acl_editor_plugin extends midcom_baseclasses_components_handler
{
    /**
     * The object we're managing
     *
     * @var object
     * @access private
     */
    var $_object = null;
    
    /**
     * The Datamanager of the member to display
     *
     * @var midcom_helper_datamanager2_datamanager
     * @access private
     */
    var $_datamanager = null;

    /**
     * The Controller of the member used for editing
     *
     * @var midcom_helper_datamanager2_controller_simple
     * @access private
     */
    var $_controller = null;

    /**
     * The schema database in use, available only while a datamanager is loaded.
     *
     * @var Array
     * @access private
     */
    var $_schemadb = null;

    /**
     * Privileges we're managing here
     *
     * @var Array
     * @access private
     */
    var $_privileges = Array();

    function midgard_admin_acl_editor_plugin()
    {
        parent::midcom_baseclasses_components_handler();

        $this->_privileges[] = 'midgard:read';        
        $this->_privileges[] = 'midgard:create';
        $this->_privileges[] = 'midgard:update';
        $this->_privileges[] = 'midgard:delete';
        $this->_privileges[] = 'midgard:owner';

        // TEMPORARY CODE: This links the old midcom approval helpers into the site
        // if we are configured to do so. This will be replaced once we revampt the
        // Metadata system of MidCOM to use 1.8        
        if ($GLOBALS['midcom_config']['metadata_approval'])
        {
            $this->_privileges[] = 'midcom:approve';
        }
    }
    
    function get_plugin_handlers()
    {
        return Array
        (
            'edit' => Array
            (
                'handler' => Array('midgard_admin_acl_editor_plugin', 'edit'),
                'fixed_args' => 'edit',
                'variable_args' => 1,
            ),
        );
    }
    
    /**
     * Load component-defined additional privileges
     */
    function _load_component_privileges()
    {
        $component_loader = $_MIDCOM->get_component_loader();
        $current_manifest = $component_loader->manifests[$_MIDCOM->get_context_data(MIDCOM_CONTEXT_COMPONENT)];
        foreach ($current_manifest->privileges as $privilege => $default_value)
        {
            $this->_privileges[] = $privilege;
        }
        
        // In addition, give component configuration privileges if we're in topic
        if (is_a($this->_object, 'midcom_baseclasses_database_topic'))
        {
            $this->_privileges[] = 'midcom:component_config';
        }
    }
    
    function _resolve_object_title($object)
    {
        $vars = get_object_vars($object);
        
        if (array_key_exists('title', $vars)) 
        {
            return $object->title;
        } 
        elseif (array_key_exists('name', $vars)) 
        {
            return $object->name;
        }
        else
        {
            return "#{$object->id}";
        }
    }   
    
    /**
     * Loads and prepares the schema database.
     *
     * Special treatement is done for the name field, which is set readonly for non-creates
     * if the simple_name_handling config option is set. (using an auto-generated urlname based
     * on the title, if it is missing.)
     *
     * The operations are done on all available schemas within the DB.
     */
    function _load_schemadb()
    {  
        $this->_schemadb = midcom_helper_datamanager2_schema::load_database('file:/midgard/admin/acl/config/schemadb_default.inc');
        
        // Populate additional assignee selector 
        $additional_assignees = Array(
            '' => '',
        );
        
        // Populate the magic assignees
        $additional_assignees['EVERYONE'] = $_MIDCOM->i18n->get_string('EVERYONE', 'midgard.admin.acl');
        $additional_assignees['USERS'] = $_MIDCOM->i18n->get_string('USERS', 'midgard.admin.acl');
        $additional_assignees['ANONYMOUS'] = $_MIDCOM->i18n->get_string('ANONYMOUS', 'midgard.admin.acl');

        // List groups as potential assignees        
        $qb = midcom_db_group::new_query_builder();
        if ($_MIDGARD['sitegroup'] != 0)
        {
            // Normally only display groups in current SG
            $qb->add_constraint('sitegroup', '=', $_MIDGARD['sitegroup']);
        }
        $groups = $qb->execute();
        foreach ($groups as $group)
        {
            $label = $group->official;
            if (empty($group->official))
            {
                $label = $group->name;
                if (empty($group->name))
                {
                    $label = sprintf($_MIDCOM->i18n->get_string('group %s', 'midgard.admin.acl'), "#{$group->id}");
                }
            }
            
            $additional_assignees["group:{$group->guid}"] = $label;
        }
        
        $assignees = Array();
        
        // Populate all resources having existing privileges
        $existing_privileges = $this->_object->get_privileges();
        foreach ($existing_privileges as $privilege)
        {
        
            $assignee = $_MIDCOM->auth->get_assignee($privilege->assignee);
            if (!$assignee)
            {
                // This is a magic assignee
                $assignees[$privilege->assignee] = $_MIDCOM->i18n->get_string($privilege->assignee, 'midgard.admin.acl');
            }
            else
            {
                $assignees[$privilege->assignee] = $assignee->name;
            }
            
            // This one is already an assignee, remove from "Add assignee" options
            if (array_key_exists($privilege->assignee, $additional_assignees))
            {
                unset($additional_assignees[$privilege->assignee]);
            }
        }

        // Add the "Add assignees" choices to schema
        $this->_schemadb['default']->fields['add_assignee']['type_config']['options'] = $additional_assignees;
                
        //$sitegroup = mgd_get_sitegroup($_MIDGARD['sitegroup']);
        
        foreach ($assignees as $assignee => $label)
        {
            $prepended = false;
            foreach ($this->_privileges as $privilege)
            {
                $prepend = '';
                if (!$prepended)
                {   
                    $prepend = "<h3 style='clear: left;'>{$label}</h3>\n";
                    $prepended = true;
                }
                $prepend .= '<fieldset class="radio">';
                $append = '</fieldset>';
                
                $privilege_components = explode(':', $privilege);
                if (   $privilege_components[0] == 'midcom'
                    || $privilege_components[0] == 'midgard')
                {
                    // This is one of the core privileges, we handle it
                    $privilege_label = $privilege;
                }
                else
                {
                    // This is a component-specific privilege, call component to localize it
                    $privilege_label = $_MIDCOM->i18n->get_string("privilege {$privilege_components[1]}", $privilege_components[0]);
                }
                            
                $this->_schemadb['default']->append_field(str_replace(':', '_', $assignee) . '_' . str_replace(':', '_', str_replace('.', '_', $privilege)), Array
                    (
                        'title'       => $privilege_label,
                        'helptext'    => sprintf($_MIDCOM->i18n->get_string('sets privilege %s', 'midgard.admin.acl'), $privilege),
                        'storage'     => null,
                        'type'        => 'privilege',
                        'type_config' => Array
                        (
                            'privilege_name' => $privilege,
                            'assignee'       => $assignee,
                        ),
                        'widget' => 'privilege',
                        'static_prepend' => $prepend,
                        'static_append' => $append,
                    )
                );
            }
        }
    }
    
   /**
     * Internal helper, loads the datamanager for the current article. Any error triggers a 500.
     *
     * @access private
     */
    function _load_datamanager()
    {
        // Populate the schema
        $this->_load_schemadb();
        
        $this->_datamanager = new midcom_helper_datamanager2_datamanager($this->_schemadb);

        if (   ! $this->_datamanager
            || ! $this->_datamanager->autoset_storage($this->_object))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to create a DM2 instance for object {$this->_object->id}.");
            // This will exit.
        }
    }

    /**
     * Internal helper, loads the controller for the current article. Any error triggers a 500.
     *
     * @access private
     */
    function _load_controller()
    {
        $_MIDCOM->load_library('midcom.helper.datamanager2');
        $this->_load_schemadb();
        $this->_controller =& midcom_helper_datamanager2_controller::create('simple');
        $this->_controller->schemadb =& $this->_schemadb;
        $this->_controller->set_storage($this->_object);
        if (! $this->_controller->initialize())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to initialize a DM2 controller instance for article {$this->_article->id}.");
            // This will exit.
        }
    }

    function _handler_edit($handler_id, $args, &$data)
    {
        $this->_object = $_MIDCOM->dbfactory->get_object_by_guid($args[0]);
        if (!$this->_object)
        {
            return false;
        }
        $this->_object->require_do('midgard:privileges');
        
        if (get_class($this->_object) != 'midcom_baseclasses_database_topic')
        {
            $_MIDCOM->bind_view_to_object($this->_object);
        }
        
        // Load possible additional component privileges
        $this->_load_component_privileges();
        
        // Load the datamanager controller
        $this->_load_controller();
        
        switch ($this->_controller->process_form())
        {
            case 'save':
                // Handle populating additional assignees
                if ($this->_object->parameter('midgard.admin.acl', 'add_assignee'))
                {
                    // We do this by adding a READ privilege so they show up on get_privileges()
                    // TODO: Would be nicer to register a priv that doesn't really count
                    $this->_object->set_privilege('midgard:read', $this->_object->parameter('midgard.admin.acl', 'add_assignee'), MIDCOM_PRIVILEGE_ALLOW);
                    
                    // Then clear the parameter and relocate
                    $this->_object->parameter('midgard.admin.acl', 'add_assignee', '');
                    $_MIDCOM->relocate($_MIDGARD['uri']);
                    // This will exit.
                }
                
            case 'cancel':
                $_MIDCOM->relocate($_MIDCOM->permalinks->create_permalink($this->_object->guid));
                // This will exit.
        }  
        
        // $_MIDCOM->set_pagetitle(sprintf($this->_request_data['l10n']->get('interview %s'), $this->_object->title));
        
        return true;
    }
    
    function _show_edit($handler_id, &$data)
    {
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
    
        echo "<h1>".sprintf($_MIDCOM->i18n->get_string('permissions for %s %s', 'midgard.admin.acl'), $type, $this->_resolve_object_title($this->_object))."</h1>\n";
        $this->_controller->display_form();
    }
}
?>