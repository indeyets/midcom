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
    var $_privileges = array();

    var $_header = '';
    var $_row_labels = array();
    var $_rendered_row_labels = array();

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

        $_MIDCOM->enable_jquery();
        $script = "function submit_privileges(form){jQuery('#submit_action',form).attr({name: 'midcom_helper_datamanager2_save', value: 'Save'});form.submit();};";
        $_MIDCOM->add_jscript($script);
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
        /*
        echo "DEBUG: manifest <pre>\n";
        print_r($current_manifest);
        echo "</pre>\n";
        */
        if (   isset($current_manifest->customdata['midgard.admin.acl'])
            && isset($current_manifest->customdata['midgard.admin.acl']['extra_privileges']))
        {
            foreach ($current_manifest->customdata['midgard.admin.acl']['extra_privileges'] as $privilege)
            {
                if (!strpos($privilege, ':'))
                {
                    // Only component specified
                    // TODO: load components manifest and add privileges from there
                    continue;
                }
                $this->_privileges[] = $privilege;
            }
        }

        // In addition, give component configuration privileges if we're in topic
        if (is_a($this->_object, 'midcom_baseclasses_database_topic'))
        {
            $this->_privileges[] = 'midcom.admin.folder:topic_management';
            $this->_privileges[] = 'midcom.admin.folder:template_management';
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
     * Special treatment is done for the name field, which is set readonly for non-creates
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
                $label = $_MIDCOM->i18n->get_string($privilege->assignee, 'midgard.admin.acl');
            }
            else
            {
                $label = $assignee->name;
            }
            $assignees[$privilege->assignee] = $label;

            $key = str_replace(':', '_', $privilege->assignee);
            if (! isset($this->_row_labels[$key]))
            {
                $this->_row_labels[$key] = $label;
            }

            // This one is already an assignee, remove from "Add assignee" options
            if (array_key_exists($privilege->assignee, $additional_assignees))
            {
                unset($additional_assignees[$privilege->assignee]);
            }
        }

        // Add the "Add assignees" choices to schema
        $this->_schemadb['privileges']->fields['add_assignee']['type_config']['options'] = $additional_assignees;

        $header = "<table width=\"100%\" border=\"0\" id=\"midgard_admin_acl\">\n";
        $header_start = "<tr>\n";
        $header_end = "</tr>\n";
        $header_items = array();

        $header .= $header_start;

        foreach ($assignees as $assignee => $label)
        {

            foreach ($this->_privileges as $privilege)
            {

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

                if (! isset($header_items[$privilege_label]))
                {
                    $header_items[$privilege_label] = "<th scope=\"col\">{$_MIDCOM->i18n->get_string($privilege_label, 'midgard.admin.acl')}</th>\n";
                }

                $this->_schemadb['privileges']->append_field(str_replace(':', '_', $assignee) . '_' . str_replace(':', '_', str_replace('.', '_', $privilege)), Array
                    (
                        'title' => $privilege_label,
                        'helptext'    => sprintf($_MIDCOM->i18n->get_string('sets privilege %s', 'midgard.admin.acl'), $privilege),
                        'storage' => null,
                        'type' => 'privilege',
                        'type_config' => Array
                        (
                            'privilege_name' => $privilege,
                            'assignee'       => $assignee,
                        ),
                        'widget' => 'privilegeselection',
                    )
                );
            }
        }
        $header .= "<th align=\"left\" scope=\"col\">&nbsp;</th>\n";
        foreach ($header_items as $key => $item)
        {
            $header .= $item;
        }
        $header .= $header_end;
        $this->_header = $header;
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
        $this->_controller->set_storage($this->_object, 'privileges');
        if (! $this->_controller->initialize())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to initialize a DM2 controller instance for article {$this->_article->id}.");
            // This will exit.
        }
    }

	/**
	 * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return bool Indicating success.
	 */
    function _handler_edit($handler_id, $args, &$data)
    {
        $this->_object = $_MIDCOM->dbfactory->get_object_by_guid($args[0]);
        if (!$this->_object)
        {
            return false;
        }
        $this->_object->require_do('midgard:privileges');

        if (! is_a($this->_object, 'midcom_baseclasses_database_topic'))
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

        $tmp = Array();
        if (is_a($this->_object, 'midcom_baseclasses_database_topic'))
        {
            $tmp[] = Array
            (
                MIDCOM_NAV_URL => "__ais/acl/edit/{$this->_object->guid}.html",
                MIDCOM_NAV_NAME => $_MIDCOM->i18n->get_string('topic privileges', 'midgard.admin.acl'),
            );
            $this->_node_toolbar->hide_item("__ais/acl/edit/{$this->_object->guid}.html");
        }
        else
        {
            $tmp[] = Array
            (
                MIDCOM_NAV_URL => $_MIDCOM->permalinks->create_permalink($this->_object->guid),
                MIDCOM_NAV_NAME => $this->_resolve_object_title($this->_object),
            );
            $tmp[] = Array
            (
                MIDCOM_NAV_URL => "__ais/acl/edit/{$this->_object->guid}.html",
                MIDCOM_NAV_NAME => $_MIDCOM->i18n->get_string('privileges', 'midgard.admin.acl'),
            );
            $this->_view_toolbar->hide_item("__ais/acl/edit/{$this->_object->guid}.html");
        }
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);

        // Add the toolbar items, if necessary
        $this->_view_toolbar->add_help_item('edit', 'midgard.admin.acl');

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
        $data['title'] = sprintf($_MIDCOM->i18n->get_string('permissions for %s %s', 'midgard.admin.acl'), $type, $this->_resolve_object_title($this->_object));
        $_MIDCOM->set_pagetitle($data['title']);

        return true;
    }

    function _show_edit($handler_id, &$data)
    {
        echo "<h1>{$data['title']}</h1>\n";

        // var_dump($this->_controller->formmanager->form, 1);

        $form_start = "<form ";
        foreach ($this->_controller->formmanager->form->_attributes as $key => $value)
        {
            $form_start .= "{$key}=\"{$value}\" ";
        }
        $form_start .= "/>\n";
        echo $form_start;

        $table_start = "<table width=\"100%\" border=\"0\" id=\"midgard_admin_acl\">\n";
        echo $table_start;

        $priv_item_cnt = count($this->_privileges);

        foreach ($this->_controller->formmanager->form->_elements as $i => $row)
        {
            if (is_a($row, 'HTML_QuickForm_hidden'))
            {
                $html = "<input type=\"hidden\" ";
                foreach ($row->_attributes as $key => $value)
                {
                    $html .= "{$key}=\"{$value}\" ";
                }
                $html .= "/>\n";
                echo $html;
            }

            if (is_a($row, 'HTML_QuickForm_select'))
            {
                $html = "<tr></td>\n";
                $html .= "<label for=\"{$row->_attributes['id']}\">\n<span class=\"field_text\">{$row->_label}</span>\n";
                $html .= $this->_render_select($row);
                $html .= "</label>\n";
                $html .= "</td></tr>\n";

                echo $html;

                $this->_render_header();
            }

            if (is_a($row, 'HTML_QuickForm_group'))
            {
                $html = '';

                if ($row->_name == 'form_toolbar')
                {
                    $html .= "<tr><td class=\"privilege_row\">\n";
                    foreach ($row->_elements as $k => $element)
                    {
                        if (is_a($element, 'HTML_QuickForm_submit'))
                        {
                            $html .= $this->_render_button($element);
                        }
                        $html .= $row->_separator;
                    }
                    $html .= "</td></tr>\n";

                    echo $html;
                    continue;
                }

                $label = $this->_render_row_label($row->_name);
                $html .= $label;

                foreach ($row->_elements as $k => $element)
                {
                    if (is_a($element, 'HTML_QuickForm_select'))
                    {
                        $html .= $this->_render_select($element);
                    }
                    if (is_a($element, 'HTML_QuickForm_static'))
                    {
                        if (strpos($element->_attributes['name'], 'holder_start') !== false)
                        {
                            $html .= '<td align="center">';
                        }

                        $html .= $this->_render_static($element);
                        if (strpos($element->_attributes['name'], 'initscripts') !== false)
                        {
                            $html .= '</td>';
                        }
                    }

                }

                if ($i == $priv_item_cnt+1)
                {
                    $html .= "</tr>\n";
                }

                echo $html;
            }
        }

        $table_end = '</table>';
        echo $table_end;

        echo "<input type=\"hidden\" name=\"\" value=\"\" id=\"submit_action\"/>\n";

        echo "</form>\n";
    }

    function _render_select($object)
    {
        $html = '';
        $element_name = '';

        $html .= "<select ";
        foreach ($object->_attributes as $key => $value)
        {
            $html .= "{$key}=\"{$value}\" ";
            if ($key == 'name')
            {
                $element_name = $value;
            }
        }
        $html .= ">\n";

        $selected_val = '';
        if (isset($this->_controller->formmanager->form->_defaultValues[$element_name]))
        {
            $selected_val = $this->_controller->formmanager->form->_defaultValues[$element_name];
        }
        if (isset($this->_controller->formmanager->form->_submitValues[$element_name]))
        {
            $selected_val = $this->_controller->formmanager->form->_submitValues[$element_name];
        }

        foreach ($object->_options as $k => $item)
        {
            $selected = '';
            if (   $selected_val != ''
                && $selected_val == $item['attr']['value'])
            {
                $selected = 'selected="selected"';
            }

            $html .= "<option value=\"{$item['attr']['value']}\" {$selected}>{$item['text']}</option>\n";
        }

        $html .= "</select>\n";

        return $html;
    }

    function _render_button($object)
    {
        $html = "<input type=\"$object->_type\" ";
        foreach ($object->_attributes as $key => $value)
        {
            $html .= "{$key}=\"{$value}\" ";
            if ($key == 'name')
            {
                $element_name = $value;
            }
        }
        $html .= ">\n";

        return $html;
    }

    function _render_static($object)
    {
        $html = $object->_text;

        return $html;
    }

    function _render_header()
    {
        if ($this->_header != '')
        {
            echo $this->_header;
            $this->_header = '';
        }
    }

    function _render_row_label($row_name)
    {
        foreach ($this->_row_labels as $key => $label)
        {
            if (   strpos($row_name, $key) !== false
                && !isset($this->_rendered_row_labels[$key]))
            {
                $this->_rendered_row_labels[$key] = true;

                $actions = "<div class=\"actions\" id=\"privilege_row_actions_{$key}\">";
                $actions .= "<script type=\"text/javascript\">";
                $actions .= "jQuery('#privilege_row_{$key}').privilege_actions('{$key}');";
                $actions .= "</script>";
                $actions .= "</div>";

                return "<tr id=\"privilege_row_{$key}\">\n<td align=\"left\">{$actions}{$label}</td>\n";
            }
        }

        return '';
    }
}
?>