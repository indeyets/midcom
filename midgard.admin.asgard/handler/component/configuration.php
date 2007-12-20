<?php
/**
 * @package midgard.admin.asgard
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id: viewer.php 3975 2006-09-06 17:36:03Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Component configuration handler
 * 
 * @package midgard.admin.asgard
 */
class midgard_admin_asgard_handler_component_configuration extends midcom_baseclasses_components_handler
{
    var $_lib = array();

    /**
     * Simple constructor
     * 
     * @access public
     */
    function midgard_admin_asgard_handler_component_configuration()
    {
        $this->_component = 'midgard.admin.asgard';
        parent::midcom_baseclasses_components_handler();
    }

    function _on_initialize()
    {
        $_MIDCOM->add_link_head
        (
            array
            (
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL . '/midcom.admin.libconfig/style.css',
            )
        );

        // Ensure we get the correct styles
        $_MIDCOM->style->prepend_component_styledir('midgard.admin.asgard');
        $_MIDCOM->skip_page_style = true;
    }

    function _prepare_toolbar(&$data)
    {
        $data['asgard_toolbar'] = new midcom_helper_toolbar();
        $data['asgard_toolbar']->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => "__mfa/asgard/components/configuration/edit/{$data['name']}",
                MIDCOM_TOOLBAR_LABEL => $_MIDCOM->i18n->get_string('edit', 'midcom'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
            )
        );

        midgard_admin_asgard_plugin::get_common_toolbar($data);
    }
    
    function _load_configs($component)
    {
        $lib = $_MIDCOM->componentloader->manifests[$component];
        $componentpath = MIDCOM_ROOT . $_MIDCOM->componentloader->path_to_snippetpath($component);

        // Load and parse the global config
        $cfg = midcom_baseclasses_components_interface::read_array_from_file("{$componentpath}/config/config.inc");
        if (! $cfg)
        {
            // hmmm... that should never happen
            $cfg = array();
        }

        $config = new midcom_helper_configuration($cfg);

        // Go for the sitewide default
        $cfg = midcom_baseclasses_components_interface::read_array_from_file("/etc/midgard/midcom/{$component}/config.inc");
        if ($cfg !== false)
        {
            $config->store($cfg, false);
        }

        // Finally, check the sitegroup config
        $cfg = midcom_baseclasses_components_interface::read_array_from_snippet("{$GLOBALS['midcom_config']['midcom_sgconfig_basedir']}/{$component}/config");
        if ($cfg !== false)
        {
            $config->store($cfg, false);
        }

        return $config;
    }
    
    function _handler_view($handler_id, $args, &$data)
    {   
        $data['name'] = $args[0];
        if (!array_key_exists($data['name'], $_MIDCOM->componentloader->manifests))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "Component '{$data['name']}' was not found.");
            // This will exit
        }
        
        $data['config'] = $this->_load_configs($data['name']);

        $data['view_title'] = sprintf($this->_l10n->get('configuration for %s'), $data['name']);
        $this->_prepare_toolbar($data);
        $_MIDCOM->set_pagetitle($data['view_title']);        

        return true;
    }

 
    /**
     * Show list of the style elements for the currently edited topic component
     * 
     * @access private
     * @param string $handler_id Name of the used handler
     * @param mixed $data Data passed to the show method
     */
    function _show_view($handler_id, &$data)
    {
        midgard_admin_asgard_plugin::asgard_header();
        
        midcom_show_style('midgard_admin_asgard_component_configuration_header');
        $data['even'] = false;
        foreach($data['config']->_global as $key => $value)
        {
            $data['key'] = $_MIDCOM->i18n->get_string($key,$data['name']);
            $data['global'] = $this->_detect($data['config']->_global[$key]);
            
            if (isset($data['config']->_local[$key]))
            {
                $data['local'] = $this->_detect($data['config']->_local[$key]);
            }
            else
            {
                $data['local'] = $this->_detect(null);
            }

            midcom_show_style('midgard_admin_asgard_component_configuration_item');
            if (!$data['even'])
            {
                $data['even'] = true;
            }
            else
            {
                $data['even'] = false;
            }

        }
        midcom_show_style('midgard_admin_asgard_component_configuration_footer');
        midgard_admin_asgard_plugin::asgard_footer();
        
    }

    function _detect($value)
    {
        $type = gettype($value);

        switch ($type)
        {
            case "boolean":

                $src = MIDCOM_STATIC_URL . '/stock-icons/16x16/cancel.png';
                $result = "<img src='{$src}'/>";

                if ($value === true)
                {
                    $src = MIDCOM_STATIC_URL . '/stock-icons/16x16/stock_mark.png';
                    $result = "<img src='{$src}'/>";
                }

                break;
            case "array":
                $content = '';
                foreach ($value as $key => $val)
                {
                    $content .= "<li>{$key} => ".$this->_detect($val).",</li>";
                }
                $result = "<ul>array<br />(<br />{$content}),</ul>";


                break;
            case "object":
                $result = "<strong>Object</strong>";
                break;
            case "NULL":
                $src = MIDCOM_STATIC_URL . '/stock-icons/16x16/cancel.png';
                $result = "<img src='{$src}'/>";
                $result = "<strong>N/A</strong>";
                break;
            default:
                $result = $value;

        }

        return $result;

    }

   
    function _handler_edit($handler_id, $args, &$data)
    {   
        $data['name'] = $args[0];
        if (!array_key_exists($data['name'], $_MIDCOM->componentloader->manifests))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "Component '{$data['name']}' was not found.");
            // This will exit
        }

        $data['config'] = $this->_load_configs($data['name']);

        // Load SchemaDb
        $schemadb_config_path = $_MIDCOM->componentloader->path_to_snippetpath($data['name']) . '/config/schemadb_config.inc';
        $schemadb = null;
        $schema = 'default';
        /*
        FIXME: Enable this again once config schemas stop using functions and other stuff causing fatal errors
        if (file_exists(MIDCOM_ROOT . $schemadb_config_path))
        {
            // Check that the schema is valid DM2 schema
            $schema_array = midcom_baseclasses_components_interface::read_array_from_file(MIDCOM_ROOT . $schemadb_config_path);
            if (isset($schema_array['config']))
            {
                $schema = 'config';
            }
            
            if (!isset($schema_array[$schema]['name']))
            {
                // This looks like DM2 schema
                $schemadb = midcom_helper_datamanager2_schema::load_database("file:/{$schemadb_config_path}");
            }
            
            // TODO: Log error on deprecated config schema?
        }*/
        
        if (!$schemadb)
        {
            // Create dummy schema. Naughty component would not provide config schema.
            $schemadb = midcom_helper_datamanager2_schema::load_database("file:/midcom/admin/libconfig/config/schemadb_template.inc");
        }
        $schemadb[$schema]->l10n_schema = $data['name'];

        foreach($data['config']->_global as $key => $value)
        {
            // try to sniff what fields are missing in schema
            if (!array_key_exists($key,$schemadb['default']->fields))
            {
                $schemadb['default']->append_field
                (
                    $key,
                    $this->_detect_schema($key,$value)
                );

            }

            if (   !isset($data['config']->_local[$key])
                || !$data['config']->_local[$key])
            {
                $schemadb['default']->fields[$key]['static_prepend'] = "<div class='global'><span>Global value</span>";
                $schemadb['default']->fields[$key]['static_append'] = "</div>";

            }
        }

        //prepare values
        foreach($data['config']->_merged as $key => $value)
        {
            if (is_array($value))
            {
                $defaults[$key] = $this->_draw_array($value);
            }
            else
            {
                $defaults[$key] = $value;
            }
        }

        $this->_controller =& midcom_helper_datamanager2_controller::create('nullstorage');
        $this->_controller->schemadb =& $schemadb;
        $this->_controller->defaults = $defaults;
        if (! $this->_controller->initialize())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to initialize a DM2 controller instance for configuration.");
        // This will exit.
        }

        switch ($this->_controller->process_form())
        {
            case 'save':
                $sg_snippetdir = new midcom_baseclasses_database_snippetdir();
                $sg_snippetdir->get_by_path($GLOBALS['midcom_config']['midcom_sgconfig_basedir']);
                if ($sg_snippetdir->id == false )
                {
                    $sd = new midcom_baseclasses_database_snippetdir();
                    $sd->up = 0;
                    $sd->name = $GLOBALS['midcom_config']['midcom_sgconfig_basedir'];
                    if (!$sd->create())
                    {
                        $_MIDCOM->generate_error(MIDCOM_ERRCRIT,"Failed to create {$GLOBALS['midcom_config']['midcom_sgconfig_basedir']}".mgd_errstr());
                    }
                    $sg_snippetdir = new midcom_baseclasses_database_snippetdir($sd->guid);
                    unset($sd);
                }

                $lib_snippetdir = new midcom_baseclasses_database_snippetdir();
                $lib_snippetdir->get_by_path($GLOBALS['midcom_config']['midcom_sgconfig_basedir']."/".$data['name']);
                if ($lib_snippetdir->id == false )
                {
                    $sd = new midcom_baseclasses_database_snippetdir();
                    $sd->up = $sg_snippetdir->id;
                    $sd->name = $data['name'];
                    if (!$sd->create())
                    {
                        $_MIDCOM->generate_error(MIDCOM_ERRCRIT,"Failed to create {$data['name']}".mgd_errstr());
                    }
                    $lib_snippetdir = new midcom_baseclasses_database_snippetdir($sd->guid);
                    unset($sd);
                }

                $snippet = new midcom_baseclasses_database_snippet();
                $snippet->get_by_path($GLOBALS['midcom_config']['midcom_sgconfig_basedir']."/".$data['name']."/config");
                if ($snippet->id == false )
                {
                    $sn = new midcom_baseclasses_database_snippet();
                    $sn->up = $lib_snippetdir->id;
                    $sn->name = "config";
                    if (!$sn->create())
                    {
                        $_MIDCOM->generate_error(MIDCOM_ERRCRIT,"Failed to create config snippet".mgd_errstr());
                    }
                    $snippet = new midcom_baseclasses_database_snippet($sn->id);
                }

                $snippet->code = $this->_get_config($this->_controller);

                if (   $snippet->code == ''
                    || !$snippet->code)
                {
                    $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                        "code-init content generation failed.");
                    // This will exit.
                }

                $rst = $snippet->update();

                if ($rst)
                {
                    mgd_cache_invalidate();
                    $_MIDCOM->uimessages->add($_MIDCOM->i18n->get_string('host configuration', 'midcom.admin.settings'),
                    $_MIDCOM->i18n->get_string('settings saved successfully', 'midcom.admin.settings')
                    . $this->_codeinit->id,
                                                'ok');
                }
                else
                {
                    $_MIDCOM->uimessages->add($_MIDCOM->i18n->get_string('host configuration', 'midcom.admin.settings'),
                      sprintf($_MIDCOM->i18n->get_string('failed to save settings, reason %s', 'midc')),
                                                'error');
                }
                // *** FALL-THROUGH ***

            case 'cancel':
                $_MIDCOM->relocate("__mfa/asgard/components/configuration/{$data['name']}");
                // This will exit.
        }


        $data['controller'] =& $this->_controller;

        $this->_prepare_toolbar($data);
        $data['view_title'] = sprintf($this->_l10n->get('edit configuration for %s'), $data['name']);
        $_MIDCOM->set_pagetitle($data['view_title']);        

        return true;
    }

 
    /**
     * Show list of the style elements for the currently edited topic component
     * 
     * @access private
     * @param string $handler_id Name of the used handler
     * @param mixed $data Data passed to the show method
     */
    function _show_edit($handler_id, &$data)
    {
        midgard_admin_asgard_plugin::asgard_header();
        midcom_show_style('midgard_admin_asgard_component_configuration_edit');
        midgard_admin_asgard_plugin::asgard_footer();
    }
    

    function _get_config()
    {
        $post = $this->_controller->formmanager->form->_submitValues;
        foreach ($this->_request_data['config']->_global as $key => $val)
        {

            $newval = $post[$key];

            switch(gettype($this->_request_data['config']->_global[$key]))
            {
                case "boolean":
                    $data .= ($newval)?"'{$key}' => true,\n":"'{$key}' => false,\n";
                    break;
                case "array":
                    break;
                default:
                    if ($newval)
                    {
                        $data .= "'{$key}' => '{$newval}',\n";
                    }
            }
        }

        return $data;
    }

    function _detect_schema($key,$value)
    {
        $result = array
        (
            'title'       => $key,
            'type'        => 'text',
            'widget'      => 'text',
        );

        $type = gettype($value);

        switch ($type)
        {
            case "boolean":
                $result['type'] = 'boolean';
                $result['widget'] = 'checkbox';

                break;
            case "array":
                $result['widget'] = 'textarea';
                break;
            default:
                if (ereg("\n",$value))
                {
                    $result['widget'] = 'textarea';
                }

        }


        return $result;

    }

    function _draw_array($array)
    {
        foreach ($array as $key => $val)
        {
            switch(gettype($val))
            {
                case "boolean":
                    $data .= ($val)?"    '{$key}' => true,\n":"'{$key}' => false,\n";
                    break;
                case "array":
                    $data .= $this->_draw_array($val);
                    break;

                default:
                    $data = '';
                    if (is_numeric($val))
                    {
                        $data .= "    '{$key}' => {$val},\n";
                    }
                    else
                    {
                        $data .= "    '{$key}' => '{$val}',\n";
                    }
            }

        }
        $result = "array(\n{$data}),\n";
        return $result;
    }

}
?>
