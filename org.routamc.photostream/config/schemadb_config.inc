/**
 * This schema is used to show the settings form on
 * for the folder of your component
 */

'config' => array 
(
    'name'        => 'config',
    'description' => 'Default Configuration Schema', /* This is a topic */
    'locktimeout' => 0, /* Don't lock this, we always display the edit view */
    'fields'      => array 
    (
        'schemadb' => array
        (
            'description' => 'schema database',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'org.routamc.photostream',
            'config_key' => 'schemadb',
            'start_fieldgroup' => array
            ( 
                'title' => 'advanced schema and data settings',
            ),
            'end_fieldgroup' => '',
        ),
        'enable_ajax_editing' => array
        (
            'description' => 'enable AJAX editing',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'org.routamc.photostream',
            'config_key' => 'enable_ajax_editing',
            'widget' => 'config_radiobox',
            'widget_radiobox_choices' => Array
            (
                '' => 'default setting',
                '1' => 'yes',
                '0' => 'no',
            ),
            'start_fieldgroup' => array
            ( 
                'title' => 'user interface settings',
            ),
        ),
        'navigate_with_context' => array
        (
            'description' => 'navigate with sticky context (e.g. tags, timespan or photographer)',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'org.routamc.photostream',
            'config_key' => 'navigate_with_context',
            'widget' => 'config_radiobox',
            'widget_radiobox_choices' => Array
            (
                '' => 'default setting',
                '1' => 'yes',
                '0' => 'no',
            ),
            'end_fieldgroup' => '',
        ),
        'api_email_enable' => array
        (
            'description' => 'email API enable',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'org.routamc.photostream',
            'config_key' => 'api_email_enable',
            'widget' => 'config_radiobox',
            'widget_radiobox_choices' => Array
            (
                '' => 'default setting',
                '1' => 'yes',
                '0' => 'no',
            ),
            'start_fieldgroup' => array
            ( 
                'title' => 'API settings',
            ),
        ),
        'api_email_schema' => array
        (
            'description' => 'email API schema',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'org.routamc.photostream',
            'config_key' => 'api_email_schema',
        ),
        'api_email_default_tags' => array
        (
            'description' => 'email API default tags to apply to photos',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'org.routamc.photostream',
            'config_key' => 'api_email_default_tags',
        ),
        /* not implemented yet
        'api_metaweblog_enable' => array
        (
            'description' => 'metaweblog API enable',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'org.routamc.photostream',
            'config_key' => 'api_metaweblog_enable',
            'widget' => 'config_radiobox',
            'widget_radiobox_choices' => Array
            (
                '' => 'default setting',
                '1' => 'yes',
                '0' => 'no',
            ),
        ),
        'api_metaweblog_schema' => array
        (
            'description' => 'metaweblog API schema',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'org.routamc.photostream',
            'config_key' => 'api_metaweblog_schema',
            'end_fieldgroup' => '',
        ),
        */
    ),
),
