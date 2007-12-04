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
            'title' => 'schema database',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'org.routamc.photostream',
                'name' => 'schemadb',
            ),
            'type' => 'text',
            'widget' => 'text',
            'start_fieldgroup' => array
            ( 
                'title' => 'advanced schema and data settings',
            ),
            'end_fieldgroup' => '',
        ),
        'enable_ajax_editing' => array
        (
            'title' => 'enable AJAX editing',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'org.routamc.photostream',
                'name' => 'enable_ajax_editing',
            ),
            'type' => 'select',
            'type_config' => array
            (
                'options' => array
                (
                    '' => 'default setting',
                    '1' => 'yes',
                    '0' => 'no',
                ),
            ),
            'widget' => 'select',
            'start_fieldgroup' => array
            ( 
                'title' => 'user interface settings',
            ),
        ),
        'entry_page' => array
        (
            'title' => 'entry page operating model',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'org.routamc.photostream',
                'name' => 'entry_page',
            ),
            'type' => 'select',
            'type_config' => array
            (
                'options' => array
                (
                    '' => 'default setting',
                    'lists' => 'select the user or tags',
                    'straight' => 'go straight to the all photos view',
                ),
            ),
            'widget' => 'select',
        ),
        'navigate_with_context' => array
        (
            'title' => 'navigate with sticky context (e.g. tags, timespan or photographer)',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'org.routamc.photostream',
                'name' => 'navigate_with_context',
            ),
            'type' => 'select',
            'type_config' => array
            (
                'options' => array
                (
                    '' => 'default setting',
                    '1' => 'yes',
                    '0' => 'no',
                ),
            ),
            'widget' => 'select',
            'end_fieldgroup' => '',
        ),
        'api_email_enable' => array
        (
            'title' => 'email API enable',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'org.routamc.photostream',
                'name' => 'api_email_enable',
            ),
            'type' => 'select',
            'type_config' => array
            (
                'options' => array
                (
                    '' => 'default setting',
                    '1' => 'yes',
                    '0' => 'no',
                ),
            ),
            'widget' => 'select',
            'start_fieldgroup' => array
            ( 
                'title' => 'API settings',
            ),
        ),
        'api_email_schema' => array
        (
            'title' => 'email API schema',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'org.routamc.photostream',
                'name' => 'api_email_schema',
            ),
            'type' => 'text',
            'widget' => 'text',
        ),
        'api_email_default_tags' => array
        (
            'title' => 'email API default tags to apply to photos',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'org.routamc.photostream',
                'name' => 'api_email_default_tags',
            ),
            'type' => 'text',
            'widget' => 'text',
        ),
        /* not implemented yet
        'api_metaweblog_enable' => array
        (
            'title' => 'metaweblog API enable',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'org.routamc.photostream',
                'name' => 'api_metaweblog_enable',
            ),
            'type' => 'select',
            'type_config' => array
            (
                'options' => array
                (
                    '' => 'default setting',
                    '1' => 'yes',
                    '0' => 'no',
                ),
            ),
            'widget' => 'select',
        ),
        'api_metaweblog_schema' => array
        (
            'title' => 'metaweblog API schema',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'org.routamc.photostream',
                'name' => 'api_metaweblog_schema',
            ),
            'type' => 'text',
            'widget' => 'text',
            'end_fieldgroup' => '',
        ),
        */
    ),
),
