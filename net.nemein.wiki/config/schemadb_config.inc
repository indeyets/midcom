'config' => array
(
    'name'        => 'config',
    'description' => 'Default Configuration Schema', /* This is a topic */
    'locktimeout' => 0, /* Don't lock this, we display the edit view always */
    'fields'      => array
    (
        /* Schema settings */
        'autogenerate_toc' => array
        (
            'description' => 'autogenerate table of contents',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nemein.wiki',
            'config_key' => 'autogenerate_toc',
            'widget' => 'config_radiobox',
            'widget_radiobox_choices' => array
            (
                '' => 'default setting',
                '1' => 'yes',
                '0' => 'no',
            ),
            'start_fieldgroup' => array
            ( 
                'title' => 'display settings',
            ),
        ),
        'display_related_to' => array
        (
            'description' => 'display related to items in page',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nemein.wiki',
            'config_key' => 'display_related_to',
            'widget' => 'config_radiobox',
            'widget_radiobox_choices' => array
            (
                '' => 'default setting',
                '1' => 'yes',
                '0' => 'no',
            ),
            'end_fieldgroup' => '',
        ),
        'schema' => array
        (
            'description' => 'default schema',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nemein.wiki',
            'config_key' => 'default_schema',
            'start_fieldgroup' => array
            ( 
                'title' => 'advanced schema and data settings'
            ),
        ),
        'schemadb' => array
        (
            'description' => 'schema database',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nemein.wiki',
            'config_key' => 'schemadb',
            'end_fieldgroup' => '',
        ),
    ),
),