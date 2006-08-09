<?php
/**
 * @package midgard.admin.sitewizard
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Site Wizard sitegroup creation and selection
 *
 * @package midgard.admin.sitewizard
 */
class midgard_admin_sitewizard_handler_host extends midcom_baseclasses_components_handler
{
    /**
     * Simple default constructor.
     */
    function midgard_admin_sitewizard_handler_host()
    {
        parent::midcom_baseclasses_components_handler();
    }
   
    /**
     * Creates a host
     */
    function _handler_settings($handler_id, $args, &$data)
    {
        $this->_request_data['sitegroup'] = mgd_get_sitegroup($args[0]);
        if (!$this->_request_data['sitegroup'])
        {
            return false;
        }
    
        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
        $this->_request_data['current_host'] = new midcom_db_host($_MIDGARD['host']);
        
        if (array_key_exists('midgard_admin_sitewizard_process', $_POST))
        {
            // Set up defaults
            $host_settings = Array(
                'hostname' => $this->_request_data['current_host']->name,
                'port'     => 0,
                'prefix'   => '',
                'component' => $this->_config->get('default_component'),
            );

            // Read form contents            
            if (array_key_exists('midgard_admin_sitewizard_host_name', $_POST))
            {
                $host_settings['hostname'] = $_POST['midgard_admin_sitewizard_host_name'];
            }
            if (array_key_exists('midgard_admin_sitewizard_host_port', $_POST))
            {
                $host_settings['port'] = $_POST['midgard_admin_sitewizard_host_port'];
            }
            if (array_key_exists('midgard_admin_sitewizard_host_prefix', $_POST))
            {
                $host_settings['prefix'] = $_POST['midgard_admin_sitewizard_host_prefix'];
            }
            if (array_key_exists('midgard_admin_sitewizard_host_component', $_POST))
            {
                $host_settings['component'] = $_POST['midgard_admin_sitewizard_host_component'];
            }
            
            // Save information into session        
            $session = new midcom_service_session();
            $session->set("midgard_admin_sitewizard_{$this->_request_data['sitegroup']->id}", $host_settings);
            
            // Relocate user to template selection
            $_MIDCOM->relocate("{$prefix}template/{$this->_request_data['sitegroup']->id}/");
            // This will exit
        }
        
        $_MIDCOM->set_pagetitle($this->_l10n->get('create website'));
        
        // Get available components
        $this->_request_data['components'] = Array();
        foreach ($_MIDCOM->componentloader->manifests as $manifest)
        {
            // Skip pure code libraries
            if ($manifest->purecode)
            {
                continue;
            }
            $manifest->get_name_translated();
            $this->_request_data['components'][$manifest->name] = "{$manifest->name_translated} ($manifest->name)";
        }
        asort($this->_request_data['components']);        
        
        return true;
    }

    /**
     * Shows host creation form.
     */
    function _show_settings($handler_id, &$data)
    {
        midcom_show_style('show-wizard-header');
        midcom_show_style('wizard-create-host');
        midcom_show_style('show-wizard-footer');
    }

    /**
     * Selects a template
     */
    function _handler_template($handler_id, $args, &$data)
    {
        $this->_request_data['sitegroup'] = mgd_get_sitegroup($args[0]);
        if (!$this->_request_data['sitegroup'])
        {
            return false;
        }

        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);    
        $session = new midcom_service_session();
        if (!$session->exists("midgard_admin_sitewizard_{$this->_request_data['sitegroup']->id}"))
        {
            // Relocate user back to host settings
            $_MIDCOM->relocate("{$prefix}host/{$this->_request_data['sitegroup']->id}/");
            // This will exit
        }
        
        if (array_key_exists('midgard_admin_sitewizard_process', $_POST))
        {
            $host_settings = $session->get("midgard_admin_sitewizard_{$this->_request_data['sitegroup']->id}");
            if (!is_array($host_settings))
            {
                // Corrupt data, relocate back to host settings
                $_MIDCOM->relocate("{$prefix}host/{$this->_request_data['sitegroup']->id}/");
                // This will exit        
            }

            // Read form contents            
            if (array_key_exists('midgard_admin_sitewizard_select_template', $_POST))
            {
                if ($_POST['midgard_admin_sitewizard_select_template'] == 'custom')
                {
                    if (array_key_exists('template', $host_settings))
                    {
                        unset($host_settings['template']);
                    }
                }
                else
                {
                    $host_settings['template'] = $_POST['midgard_admin_sitewizard_select_template'];
                }
            }
            
            // Save information into session
            $session->set("midgard_admin_sitewizard_{$this->_request_data['sitegroup']->id}", $host_settings);
            
            // Relocate user to host creation
            $_MIDCOM->relocate("{$prefix}create/{$this->_request_data['sitegroup']->id}/");
            // This will exit
        }
        
        $_MIDCOM->set_pagetitle($this->_l10n->get('change style'));
        
        $qb = midcom_db_style::new_query_builder();
        $qb->add_constraint('name', 'LIKE', 'template_%');
        $qb->add_constraint('up', '=', 0);
        // TODO: Check for sitegroups?
        $this->_request_data['templates'] = $qb->execute();
        
        return true;
    }

    /**
     * Shows the template selection
     */
    function _show_template($handler_id, &$data)
    {
        midcom_show_style('show-wizard-header');
        midcom_show_style('wizard-select-style');
        midcom_show_style('show-wizard-footer');
    }

    /**
     * Selects a template
     */
    function _handler_create($handler_id, $args, &$data)
    {
        $this->_request_data['sitegroup'] = mgd_get_sitegroup($args[0]);
        if (!$this->_request_data['sitegroup'])
        {
            return false;
        }

        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);    
        $session = new midcom_service_session();
        if (!$session->exists("midgard_admin_sitewizard_{$this->_request_data['sitegroup']->id}"))
        {
            // Relocate user back to host settings
            $_MIDCOM->relocate("{$prefix}host/{$this->_request_data['sitegroup']->id}/");
            // This will exit
        }
        
        $host_settings = $session->get("midgard_admin_sitewizard_{$this->_request_data['sitegroup']->id}");

        // Set up the config
        $config = new midgard_admin_sitegroup_creation_config_host();
        $config->sitegroup_id = $this->_request_data['sitegroup']->id;
        $config->hostname = $host_settings['hostname'];
        $config->host_prefix = $host_settings['prefix'];
        $config->host_port = $host_settings['port'];
        $config->topic_midcom = $host_settings['component'];
        $config->verbose = true;
        
        if ($session->exists("midgard_admin_sitewizard_17compat_{$this->_request_data['sitegroup']->id}"))
        {
            // FIXME: Midgard 1.7 compatibility hack
            $auth = $session->get("midgard_admin_sitewizard_17compat_{$this->_request_data['sitegroup']->id}");
            $config->set_username($auth[0]);
            $config->set_password($auth[1]);
            $session->remove("midgard_admin_sitewizard_17compat_{$this->_request_data['sitegroup']->id}");
        }
        
        if (array_key_exists('template', $host_settings))
        {
            $config->extend_style = $host_settings['template'];
        }

        ob_start();
        $runner = midgard_admin_sitegroup_creation_host::factory($config);
        if ($runner->validate()) 
        {
            $runner->run();
        }
        $host_errors = ob_get_contents();
        ob_end_clean();
        
        if ($runner->host->id)
        {
            $_MIDCOM->relocate("{$prefix}finish/{$runner->host->id}/");
            // This will exit
        }
        else
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, nl2br("<strong>Failed to create host</strong>:\n" . $host_errors));
            // This will exit
        }
    }

    /**
     * Creates a host
     */
    function _handler_finish($handler_id, $args, &$data)
    {
        $this->_request_data['host'] = new midcom_db_host($args[0]);
        if (!$this->_request_data['host'])
        {
            return false;
        }
    
        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
        
        $_MIDCOM->set_pagetitle($this->_l10n->get('finish'));
          
        return true;
    }

    /**
     * Shows host creation form.
     */
    function _show_finish($handler_id, &$data)
    {
        midcom_show_style('show-wizard-header');
        midcom_show_style('wizard-finish');
        midcom_show_style('show-wizard-footer');
    }    
}

?>