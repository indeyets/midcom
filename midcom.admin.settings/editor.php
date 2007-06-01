<?php


class midcom_admin_settings_editor extends midcom_baseclasses_components_handler
{
    /**
     * The config storage to use
     *
     * @var midcom_db_topic
     * @access private
     */
    var $_codeinit = null;
    var $_config_storage = null;

    /**
     * The Datamanager of the article to display (for delete mode)
     *
     * @var midcom_helper_datamanager2_datamanager
     * @access private
     */
    var $_datamanager = null;

    /**
     * The controller of the article used for editing
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
     * Defaults for the schema database
     *
     * @var Array
     * @access private
     */
    var $_defaults = array();

    /**
     * Simple helper which references all important members to the request data listing
     * for usage within the style listing.
     */
    function _prepare_request_data()
    {
        $this->_request_data['datamanager'] =& $this->_datamanager;
        $this->_request_data['controller'] =& $this->_controller;
        
    }


    /**
     * Simple default constructor.
     */
    function midcom_admin_settings_editor()
    {
        parent::midcom_baseclasses_components_handler();
        
        $this->_config_storage = new midcom_db_page($_MIDGARD['page']);

        require MIDCOM_ROOT . '/midcom/helper/hostconfig.php';

	$_MIDCOM->load_library('midcom.helper.datamanager2');
    }

    function get_plugin_handlers()
    {
        return Array
        (
            'edit' => Array
            (
                'handler' => Array('midcom_admin_settings_editor', 'edit'),
                'fixed_args' => 'edit',
            ),
        );
    }


    /**
     * Loads and prepares the schema database.
     *
     * Special treatement is done for the name field, which is set readonly for non-admins
     * if the simple_name_handling config option is set. (using an auto-generated urlname based
     * on the title, if it is missing.)
     *
     * The operations are done on all available schemas within the DB.
     */
    function _load_schemadb()
    {
    	foreach ($GLOBALS['midcom_config_local'] as $key => $value)
    	{
    	   $this->_defaults[$key] = $value;
    	}
    
    	$this->_schemadb = midcom_helper_datamanager2_schema::load_database('file:/midcom/admin/settings/config/schemadb_config.inc');    	
    }

    /**
     * Internal helper, loads the controller for the current article. Any error triggers a 500.
     *
     * @access private
     */
    function _load_controller()
    {
        $this->_load_schemadb();
        $this->_controller = & midcom_helper_datamanager2_controller::create('nullstorage');
        $this->_controller->schemadb =& $this->_schemadb;
        $this->_controller->defaults = $this->_defaults;
        //$this->_controller->set_storage($this->_config_storage);
        if (! $this->_controller->initialize())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to initialize a DM2 controller instance.");
            // This will exit.
        }
    }

    /**
     * Displays an config edit view.
     *
     */
    function _handler_edit($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_admin_user();
        
        $qb = midcom_db_pageelement::new_query_builder();
        $qb->add_constraint('page', '=', $this->_config_storage->id);
        $qb->add_constraint('name', '=', 'code-init');
        $codeinits = $qb->execute();
        if (count($codeinits) == 0)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                "Failed to load 'code-init' for the website with root page ID {$this->_config_storage->id}.");
            // This will exit.
        }
        $this->_codeinit = $codeinits[0];

        $this->_load_controller();
	
        switch ($this->_controller->process_form())
        {
            case 'save':
                $this->_codeinit->value = $this->_get_code_init();

                if (   $this->_codeinit->value == ''
                    || !$this->_codeinit->value)
                {
                    $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                        "code-init content generation failed.");
                    // This will exit.
                }
                
                if ($this->_codeinit->update())
                {
                    $_MIDCOM->uimessages->add($_MIDCOM->i18n->get_string('host configuration', 'midcom.admin.settings'), 
                                                $_MIDCOM->i18n->get_string('settings saved successfully', 'midcom.admin.settings'), 
                                                'ok');
                }
                else
                {
                    $_MIDCOM->uimessages->add($_MIDCOM->i18n->get_string('host configuration', 'midcom.admin.settings'), 
                                                sprintf($_MIDCOM->i18n->get_string('failed to save settings, reason %s', 'midcom.admin.settings'), mgd_errstr()), 
                                                'error');
                }
                // *** FALL-THROUGH ***

            case 'cancel':
                $_MIDCOM->relocate('');
                // This will exit.
        }
        

        $this->_prepare_request_data();
        
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$this->_config_storage->title}");

        // Ensure we get the correct styles
        $_MIDCOM->style->prepend_component_styledir('midcom.admin.settings');                                         
    
        $_MIDCOM->add_link_head
        (
            array
            (
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL.'/midcom.helper.datamanager2/legacy.css',
            )
        );
        
        // Add the view to breadcrumb trail
        $tmp = Array();
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => '__ais/midcom-settings/edit.html',
            MIDCOM_NAV_NAME => $_MIDCOM->i18n->get_string('host configuration', 'midcom.admin.settings'),
        );
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
        
        // Set page title
        $_MIDCOM->set_pagetitle($_MIDCOM->i18n->get_string('host configuration', 'midcom.admin.settings'));

        return true;
    }


    /**
     * Shows the loaded article.
     */
    function _show_edit ($handler_id, &$data)
    {
        midcom_show_style('midcom-admin-settings-edit');
    }

    function _get_code_init() 
    {
        $hostconfig = new midcom_helper_hostconfig($this->_config_storage);
               
        foreach ($this->_controller->formmanager->form->_submitValues as $key => $val)
        {
            if (   array_key_exists($key, $GLOBALS['midcom_config'])
                && $GLOBALS['midcom_config'][$key] != $val)
            {
                $hostconfig->set($key, $val);
            }
        }
        return $hostconfig->get_code_init('midcom.admin.settings');
    }

    /**
     * Static helper for listing hours of a day for purposes of pulldowns in the schema
     */
    function get_day_hours()
    {
        $hours = array();
        $i = 0;
        while ($i <= 23)
        {
            $hours[$i] = $i;
            $i++;
        }
        return $hours;
    }
    
    /**
     * Static helper for listing minutes of hour for purposes of pulldowns in the schema
     */
    function get_hour_minutes()
    {
        $minutes = array();
        $i = 0;
        while ($i <= 59)
        {
            $minutes[$i] = $i;
            $i++;
        }
        return $minutes;
    }

}

?>