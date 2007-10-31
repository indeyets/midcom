<?php
/**
 * @package midcom.admin.libconfig
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id: viewer.php 3975 2006-09-06 17:36:03Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Listing libraries handler class
 * 
 * @package midcom.admin.libconfig
 */
class midcom_admin_libconfig_handler_edit extends midcom_baseclasses_components_handler
{
    var $_lib = array();

    /**
     * Simple constructor
     * 
     * @access public
     */
    function midcom_admin_libconfig_handler_edit()
    {
        parent::midcom_baseclasses_components_handler();
    }

    function _on_initialize()
    {

        $this->_l10n = $_MIDCOM->i18n->get_l10n('midcom.admin.libconfig');
        $this->_request_data['l10n'] = $this->_l10n;

        $_MIDCOM->add_link_head
        (
            array
            (
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL . '/midcom.admin.libconfig/style.css',
            )
        );

        midgard_admin_asgard_plugin::prepare_plugin($this->_l10n->get('midcom.admin.libconfig'),$this->_request_data);

    }

    
    function _update_breadcrumb($name)
    {
        // Populate breadcrumb
        $label = $_MIDCOM->i18n->get_string($name,$name);
        $tmp = Array();
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "__mfa/asgard_midcom.admin.libconfig/",
            MIDCOM_NAV_NAME => $this->_request_data['view_title'],
        );

        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "__mfa/asgard_midcom.admin.libconfig/edit/{$name}",
            MIDCOM_NAV_NAME => $label,
        );

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
    }

    function _prepare_toolbar(&$data)
    {
        midgard_admin_asgard_plugin::get_common_toolbar($data);
    }
    
    function _handler_edit($handler_id, $args, &$data)
    {   

        if (array_key_exists($args[0],$_MIDCOM->componentloader->manifests))
        {
            $lib = $_MIDCOM->componentloader->manifests[$args[0]];
        }
        else
        {
            return false;
        }

        $componentpath = MIDCOM_ROOT . $_MIDCOM->componentloader->path_to_snippetpath($args[0]);

        // Load and parse the global config
        $cfg = midcom_baseclasses_components_interface::read_array_from_file("{$componentpath}/config/config.inc");
        if (! $cfg)
        {
            // hmmm... that should never happen
            $cfg = array();
        }

        $config = new midcom_helper_configuration($cfg);

        // Go for the sitewide default
        $cfg = midcom_baseclasses_components_interface::read_array_from_file("/etc/midgard/midcom/{$args[0]}/config.inc");
        if ($cfg !== false)
        {
            $config->store($cfg, false);
        }

        // Finally, check the sitegroup config
        $cfg = midcom_baseclasses_components_interface::read_array_from_snippet("{$GLOBALS['midcom_config']['midcom_sgconfig_basedir']}/{$args[0]}/config");
        if ($cfg !== false)
        {
            $config->store($cfg, false);
        }

        //schemadb
        $schema = $config->_global['schemadb_config'];

        if ($schema)
        {
            // We rely on config schema. Hope that schema covers all fields
            $schemadb = midcom_helper_datamanager2_schema::load_database($schema);
        }
        else
        {
            // Create dummy schema. Naughty component would not provide config schema.
            $schemadb = midcom_helper_datamanager2_schema::load_database("file:/midcom/admin/libconfig/config/schemadb_template.inc");
            $schemadb['default']->l10n_schema = $args[0];
        }

        foreach($config->_global as $key => $value)
        {
            // try to sniff what fields are missing in schema
            if (!array_key_exists($key,$schemadb['default']->fields))
            {
                $widget = 'text';
                if (ereg("\n",$value)) $widget = 'textarea';
                $schemadb['default']->append_field
                (
                    $key,
                    array
                    (
                        'title'       => $key,
                        'type'        => 'text',
                        'widget'      => $widget,
                    )
                );

            }
        }

        $controller =& midcom_helper_datamanager2_controller::create('nullstorage');
        $controller->schemadb =& $schemadb;
        $controller->defaults = $config->_global;
        if (! $controller->initialize())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to initialize a DM2 controller instance for configuration.");
        // This will exit.
        }

        switch ($controller->process_form())
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
                $lib_snippetdir->get_by_path($GLOBALS['midcom_config']['midcom_sgconfig_basedir']."/".$args[0]);
                if ($lib_snippetdir->id == false )
                {
                    $sd = new midcom_baseclasses_database_snippetdir();
                    $sd->up = $sg_snippetdir->id;
                    $sd->name = $args[0];
                    if (!$sd->create())
                    {
                        $_MIDCOM->generate_error(MIDCOM_ERRCRIT,"Failed to create {$args[0]}".mgd_errstr());
                    }
                    $lib_snippetdir = new midcom_baseclasses_database_snippetdir($sd->guid);
                    unset($sd);
                }

                $snippet = new midcom_baseclasses_database_snippet();
                $snippet->get_by_path($GLOBALS['midcom_config']['midcom_sgconfig_basedir']."/".$args[0]."/config");
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

                $snippet->code = $this->_get_config($controller);

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
                $_MIDCOM->relocate('__mfa/asgard_midcom.admin.libconfig/edit/'.$args[0]);
                // This will exit.
        }


        $data['controller'] =& $controller;

        $this->_update_breadcrumb($args[0]);
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
    function _show_edit($handler_id, &$data)
    {
        midgard_admin_asgard_plugin::asgard_header();
        
        midcom_show_style('midcom-admin-libs-edit');
        midgard_admin_asgard_plugin::asgard_footer();
        
    }

    function _get_config(&$controller)
    {
        foreach ($controller->formmanager->form->_submitValues as $key => $val)
        {
            if ($key == 'midcom_helper_datamanager2_save') continue;
            $data .= "'{$key}' => '{$val}',\n";
        }

        return $data;
    }
}
?>
