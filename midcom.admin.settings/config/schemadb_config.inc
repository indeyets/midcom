'default' => array
(
    'name' => 'default',
    'description' => 'MidCOM Configuration schema',
    'l10n_db' => 'midcom.admin.settings',
    'fields' => array
    (
    
        // Authentication configuration
        'auth_backend' => array
        (
            'title' => 'auth_backend',
            'type' => 'select',
            'type_config' => Array
            (
                'options' => array
                (
                    ''       => sprintf($_MIDCOM->i18n->get_string('default (%s)', 'midcom.admin.settings'), $_MIDCOM->i18n->get_string("auth_backend_{$GLOBALS['midcom_config_default']['auth_backend']}", 'midcom.admin.settings')),
                    'simple' => 'auth_backend_simple',
                ),
            ),
            'widget' => 'select',
        ),
        
        'auth_login_session_timeout' => array
        (
            'type' => 'text',
            'widget' => 'text',
            'title' => 'auth_login_session_timeout',
        ),

    	'auth_frontend' => array
        (
            'title' => 'auth_frontend',
            'type' => 'select',
            'type_config' => Array
            (
                'options' => array
                (
                    ''     => sprintf($_MIDCOM->i18n->get_string('default (%s)', 'midcom.admin.settings'), $_MIDCOM->i18n->get_string("auth_frontend_{$GLOBALS['midcom_config_default']['auth_frontend']}", 'midcom.admin.settings')),
                    'form' => 'auth_frontend_form',
                ),
            ),
            'widget' => 'select',            
        ),

    	'auth_sitegroup_mode' => array
        (
            'title' => 'auth_sitegroup_mode',
            'type' => 'select',
            'type_config' => Array
            (
                'options' => array
                (
                    ''                => sprintf($_MIDCOM->i18n->get_string('default (%s)', 'midcom.admin.settings'), $_MIDCOM->i18n->get_string("auth_sitegroup_mode_{$GLOBALS['midcom_config_default']['auth_sitegroup_mode']}", 'midcom.admin.settings')),
                    'auto'            => 'auth_sitegroup_mode_auto',
                    'sitegrouped'     => 'auth_sitegroup_mode_sitegrouped',
                    'not-sitegrouped' => 'auth_sitegroup_mode_not-sitegrouped',                    
                ),
            ),
            'widget' => 'select',
        ),
        
    	'auth_login_form_httpcode' => array
        (
            'title' => 'auth_login_form_httpcode',
            'type' => 'select',
            'type_config' => Array
            (
                'options' => array
                (
                    ''    => sprintf($_MIDCOM->i18n->get_string('default (%s)', 'midcom.admin.settings'), $_MIDCOM->i18n->get_string("auth_login_form_httpcode_{$GLOBALS['midcom_config_default']['auth_login_form_httpcode']}", 'midcom.admin.settings')),
                    '403' => 'auth_login_form_httpcode_403',
                    '200' => 'auth_login_form_httpcode_200',
                ),
            ),
            'widget' => 'select',
        ),

    	'auth_check_client_ip' => array
        (
            'type' => 'select',
            'widget' => 'select',
            'title' => 'auth_check_client_ip',
            'type_config' => array
            (
                'options' => array
                (
                    ''	=> sprintf($_MIDCOM->i18n->get_string('default (%s)', 'midcom.admin.settings'), $GLOBALS['midcom_config_default']['auth_check_client_ip'] ? $_MIDCOM->i18n->get_string('yes', 'midcom') : $_MIDCOM->i18n->get_string('no', 'midcom')),
                    '1'	=> 'yes',
                    '0'	=> 'no',
                ),
            ),
        ),

    	'auth_allow_sudo' => array
        (
            'type' => 'select',
            'widget' => 'select',
            'title' => 'auth_allow_sudo',
            'type_config' => array
            (
                'options' => array
                (
                    ''	=> sprintf($_MIDCOM->i18n->get_string('default (%s)', 'midcom.admin.settings'), $GLOBALS['midcom_config_default']['auth_allow_sudo'] ? $_MIDCOM->i18n->get_string('yes', 'midcom') : $_MIDCOM->i18n->get_string('no', 'midcom')),
                    '1' => 'yes',
                    '0' => 'no',
                ),
            ),
        ),
	
    	'auth_backend_simple_cookie_id' => array
        (
            'type' => 'text',
            'widget' => 'text',
            'title' => 'auth_backend_simple_cookie_id',

        ),

    	'auth_backend_simple_cookie_path' => array
        (
            'type' => 'text',
            'widget' => 'text',
            'title' => 'auth_backend_simple_cookie_path',
        ),

    	'auth_backend_simple_cookie_domain' => array
        (
            'type' => 'text',
            'widget' => 'text',
            'title' => 'auth_backend_simple_cookie_domain' ,
        ),

        // Cache configuration
    	'cache_base_directory' => array
        (
            'type' => 'text',
            'widget' => 'text',
            'title' => 'cache_base_directory',
        ),

        /*
    	'cache_module_content_uncached' => array
        (
            'type' => 'select',
            'widget' => 'select',
            'title' => 'cache_module_content_uncached',
            'type_config' => array
            (
                'options' => array
                (
                    ''	=> sprintf($_MIDCOM->i18n->get_string('default (%s)', 'midcom.admin.settings'), $GLOBALS['midcom_config_default']['cache_module_content_uncached'] ? $_MIDCOM->i18n->get_string('yes', 'midcom') : $_MIDCOM->i18n->get_string('no', 'midcom')),
                    '1' => 'yes',
                    '0' => 'no',
                ),
            ),
        ),
        */

	'cache_module_content_headers_strategy' => array
        (
            'type' => 'select',
            'widget' => 'select',
            'title' => 'cache_module_content_headers_strategy',
            'type_config' => array
            (
                'options' => array
                (
                    ''  => sprintf($_MIDCOM->i18n->get_string('default (%s)', 'midcom.admin.settings'), $GLOBALS['midcom_config_default']['cache_module_content_headers_strategy']),
                    'no-cache' =>'cs-no-cache', 
        	    'revalidate' =>'cs-revalidate',
        	    'public' =>'cs-public',
        	    'private'=>'cs-private',
                ),
            ),
        ),

        'cache_module_content_default_lifetime' => array
        (
            'type' => 'text',
            'widget' => 'text',
            'title' => 'cache_module_content_default_lifetime',
        ),

    	'i18n_fallback_language' => array
        (
            'title' => 'i18n_fallback_language',
            'type' => 'select',
            'type_config' => Array
            (
                'options' => array_merge(array('' => sprintf($_MIDCOM->i18n->get_string('default (%s)', 'midcom.admin.settings'), $GLOBALS['midcom_config_default']['i18n_fallback_language'])),$_MIDCOM->i18n->list_languages()),
            ),
            'widget' => 'select',
        ),

        // Indexer Configuration
    	'indexer_backend' => array
        (
            'title' => 'indexer_backend',
            'type' => 'select',
            'type_config' => Array
            (
                'options' => array
                (
                    false    => 'indexer_backend_none',
                    'xmltcp' => 'indexer_backend_xmltcp',
                ),
            ),
            'widget' => 'select',
        ),

    	'indexer_index_name' => array
        (
            'type' => 'text',
            'widget' => 'text',
            'title' => 'indexer_index_name',
        ),

        // XMLTCP indexer backend (THE RECOMMENDED ONE)
    	'indexer_xmltcp_host' => array
        (
            'type' => 'text',
            'widget' => 'text',
            'title' => 'indexer_xmltcp_host',
        ),

    	'indexer_xmltcp_port' => array
        (
            'type' => 'text',
            'widget' => 'text',
            'title' => 'indexer_xmltcp_port',
        ),

        /*
        // XMLShell indexer backend configuration
    	'indexer_xmlshell_executable' => array
        (
            'type' => 'text',
            'widget' => 'text',
            'title' => 'indexer_xmlshell_executable',
        ),

    	'indexer_xmlshell_working_directory' => array
        (
            'type' => 'text',
            'widget' => 'text',
            'title' => 'indexer_xmlshell_working_directory',
        ),*/

        // Logging Configuration
    	'log_filename' => array
        (
            'type' => 'text',
            'widget' => 'text',
            'title' => 'log_filename',
        ),

    	'log_level' => array
        (
            'title' => 'log_level',
            'type' => 'select',
            'type_config' => Array
            (
                'options' => array
                (
                    ''       => sprintf($_MIDCOM->i18n->get_string('default (%s)', 'midcom.admin.settings'), $GLOBALS['midcom_config_default']['log_level']),
                    MIDCOM_LOG_DEBUG => 'midcom_log_debug',
                    MIDCOM_LOG_INFO  => 'midcom_log_info',
                    MIDCOM_LOG_WARN  => 'midcom_log_warn',
                    MIDCOM_LOG_ERROR => 'midcom_log_error',
                    MIDCOM_LOG_CRIT  => 'midcom_log_crit',
                ),
            ),
            'widget' => 'select',
        ),

    	'log_tailurl_enable' => array
        (
            'type' => 'select',
            'widget' => 'select',
            'title' => 'log_tailurl_enable',
            'type_config' => array
            (
                'options' => array
                (
                    ''	=> sprintf($_MIDCOM->i18n->get_string('default (%s)', 'midcom.admin.settings'), $GLOBALS['midcom_config_default']['log_tailurl_enable'] ? $_MIDCOM->i18n->get_string('yes', 'midcom') : $_MIDCOM->i18n->get_string('no', 'midcom')),
                    '1' => 'yes',
                    '0' => 'no',
                ),
            ),
        ),

        // Core configuration
        /*
    	'midcom_ais_url' => array
        (
            'type' => 'text',
            'widget' => 'text',
            'title' => 'midcom_ais_url',
        ),
        */

    	'midcom_prefix' => array
        (
            'type' => 'text',
            'widget' => 'text',
            'title' => 'midcom_prefix',
        ),

    	'midcom_root_topic_guid' => array
        (
            'title' => 'midcom_root_topic_guid',
            'required' => true,
            'type' => 'select',
            'type_config' => array
            (
                 'require_corresponding_option' => false,
                 'options' => array(),
            ),
            'widget' => 'universalchooser',
            'widget_config' => array
            (
                'class'       => 'midcom_db_topic',
                'component'   => 'midcom.admin.content',
                'titlefield'  => 'extra',
                'idfield'     => 'guid',
                'constraints' => array
                (
                    array
                    (
                        'field' => 'up',
                        'op'    => '=',
                        'value' => 0,
                    ),
                ),
                'searchfields'  => array
                (
                    'name',
                    'extra'
                ),
                'orders'        => array
                (
                    array('up'    => 'ASC'), 
                    array('name'  => 'ASC'), 
                    array('extra' => 'ASC')
                ),
            ),            
        ),

    	'midcom_sgconfig_basedir' => array
        (
            'type' => 'text',
            'widget' => 'text',
            'title' => 'midcom_sgconfig_basedir',
        ),

    	'midcom_site_url' => array
        (
            'type' => 'text',
            'widget' => 'text',
            'title' => 'midcom_site_url',
        ),

    	'midcom_tempdir' => array
        (
            'type' => 'text',
            'widget' => 'text',
            'title' => 'midcom_tempdir',
        ),

    	'midcom_temporary_resource_timeout' => array
        (
            'type' => 'text',
            'widget' => 'text',
            'title' => 'midcom_temporary_resource_timeout',
        ),

        // Visibility settings (NAP)
    	'show_hidden_objects' => array
        (
            'type' => 'select',
            'widget' => 'select',
            'title' => 'show_hidden_objects',
            'type_config' => array
            (
                'options' => array
                (
                    ''	=> sprintf($_MIDCOM->i18n->get_string('default (%s)', 'midcom.admin.settings'), $GLOBALS['midcom_config_default']['show_hidden_objects'] ? $_MIDCOM->i18n->get_string('yes', 'midcom') : $_MIDCOM->i18n->get_string('no', 'midcom')),
                    '1' => 'yes',
                    '0' => 'no',
                ),
            ),
        ),

    	'show_unapproved_objects' => array
        (
            'type' => 'select',
            'widget' => 'select',
            'title' => 'show_unapproved_objects',
            'type_config' => array
            (
                'options' => array
                (
                    ''	=> sprintf($_MIDCOM->i18n->get_string('default (%s)', 'midcom.admin.settings'), $GLOBALS['midcom_config_default']['show_unapproved_objects'] ? $_MIDCOM->i18n->get_string('yes', 'midcom') : $_MIDCOM->i18n->get_string('no', 'midcom')),
                    '1' => 'yes',
                    '0' => 'no',
                ),
            ),
        ),
        
    	'i18n_multilang_strict' => array
        (
            'type' => 'select',
            'widget' => 'select',
            'title' => 'i18n_multilang_strict',
            'type_config' => array
            (
                'options' => array
                (
                    ''	=> sprintf($_MIDCOM->i18n->get_string('default (%s)', 'midcom.admin.settings'), $GLOBALS['midcom_config_default']['i18n_multilang_strict'] ? $_MIDCOM->i18n->get_string('yes', 'midcom') : $_MIDCOM->i18n->get_string('no', 'midcom')),
                    '1' => 'yes',
                    '0' => 'no',
                ),
            ),
        ),

        // Utilities
    	'utility_imagemagick_base' => array
        (
            'type' => 'text',
            'widget' => 'text',
            'title' => 'utility_imagemagick_base',
        ),

    	'utility_jpegtran' => array
        (
            'type' => 'text',
            'widget' => 'text',
            'title' => 'utility_jpegtran',
        ),

    	'utility_unzip' => array
        (
            'type' => 'text',
            'widget' => 'text',
            'title' => 'utility_unzip',
        ),

    	'utility_gzip' => array
        (
            'type' => 'text',
            'widget' => 'text',
            'title' => 'utility_gzip',
        ),

    	'utility_tar' => array
        (
            'type' => 'text',
            'widget' => 'text',
            'title' => 'utility_tar',
        ),

    	'utility_jhead' => array
        (
            'type' => 'text',
            'widget' => 'text',
            'title' => 'utility_jhead',
        ),

    	'utility_find' => array
        (
            'type' => 'text',
            'widget' => 'text',
            'title' => 'utility_find',
        ),

    	'utility_file' => array
        (
            'type' => 'text',
            'widget' => 'text',
            'title' => 'utility_file',
        ),

    	'utility_catdoc' => array
        (
            'type' => 'text',
            'widget' => 'text',
            'title' => 'utility_catdoc',
        ),

    	'utility_pdftotext' => array
        (
            'type' => 'text',
            'widget' => 'text',
            'title' => 'utility_pdftotext',
        ),

    	'utility_unrtf' => array
        (
            'type' => 'text',
            'widget' => 'text',
            'title' => 'utility_unrtf',
        ),

        // Revision control service
    	'utility_diff' => array
        (
            'type' => 'text',
            'widget' => 'text',
            'title' => 'utility_diff',
        ),

    	'utility_rcs' => array
        (
            'type' => 'text',
            'widget' => 'text',
            'title' => 'utility_rcs',
        ),

    	'midcom_services_rcs_bin_dir' => array
        (
            'type' => 'text',
            'widget' => 'text',
            'title' => 'midcom_services_rcs_bin_dir',
        ),

    	'midcom_services_rcs_enable' => array
        (
            'type' => 'select',
            'widget' => 'select',
            'title' => 'midcom_services_rcs_enable',
            'type_config' => array
            (
                'options' => array
                (
                    ''	=> sprintf($_MIDCOM->i18n->get_string('default (%s)', 'midcom.admin.settings'), $GLOBALS['midcom_config_default']['midcom_services_rcs_enable'] ? $_MIDCOM->i18n->get_string('yes', 'midcom') : $_MIDCOM->i18n->get_string('no', 'midcom')),
                    '1' => 'yes',
                    '0' => 'no',
                ),
            ),
        ),

        // Metadata system
    	'metadata_approval' => array
        (
            'type' => 'select',
            'widget' => 'select',
            'title' => 'metadata_approval',
            'type_config' => array
            (
                'options' => array
                (
                    ''	=> sprintf($_MIDCOM->i18n->get_string('default (%s)', 'midcom.admin.settings'), $GLOBALS['midcom_config_default']['metadata_approval'] ? $_MIDCOM->i18n->get_string('yes', 'midcom') : $_MIDCOM->i18n->get_string('no', 'midcom')),
                    '1' => 'yes',
                    '0' => 'no',
                ),
            ),
        ),
    	'metadata_scheduling' => array
        (
            'type' => 'select',
            'widget' => 'select',
            'title' => 'metadata_scheduling',
            'type_config' => array
            (
                'options' => array
                (
                    ''	=> sprintf($_MIDCOM->i18n->get_string('default (%s)', 'midcom.admin.settings'), $GLOBALS['midcom_config_default']['metadata_scheduling'] ? $_MIDCOM->i18n->get_string('yes', 'midcom') : $_MIDCOM->i18n->get_string('no', 'midcom')),
                    '1' => 'yes',
                    '0' => 'no',
                ),
            ),
        ),
    	'metadata_schema' => array
        (
            'type' => 'text',
            'widget' => 'text',
            'title' => 'metadata_schema',
        ),
    	'staging2live_staging' => array
        (
            'type' => 'select',
            'widget' => 'select',
            'title' => 'staging2live_staging',
            'type_config' => array
            (
                'options' => array
                (
                    ''	=> sprintf($_MIDCOM->i18n->get_string('default (%s)', 'midcom.admin.settings'), $GLOBALS['midcom_config_default']['staging2live_staging'] ? $_MIDCOM->i18n->get_string('yes', 'midcom') : $_MIDCOM->i18n->get_string('no', 'midcom')),
                    '1' => 'yes',
                    '0' => 'no',
                ),
            ),
        ),

        // Positioning system
    	'positioning_enable' => array
        (
            'type' => 'select',
            'widget' => 'select',
            'title' => 'positioning_enable',
            'type_config' => array
            (
                'options' => array
                (
                    ''	=> sprintf($_MIDCOM->i18n->get_string('default (%s)', 'midcom.admin.settings'), $GLOBALS['midcom_config_default']['positioning_enable'] ? $_MIDCOM->i18n->get_string('yes', 'midcom') : $_MIDCOM->i18n->get_string('no', 'midcom')),
                    '1' => 'yes',
                    '0' => 'no',
                ),
            ),
        ),


    ),
),