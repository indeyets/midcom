'config' => array
(
    'name'        => 'config',
    'description' => 'Default Configuration Schema',
    'locktimeout' => 0,
    'fields'      => array
    (

        'enable_anonymous' => array
        (
            'description' => 'enable_anonymous',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nemein.quickpoll',
            'config_key' => 'enable_anonymous',
            'widget' => 'config_radiobox',
            'widget_radiobox_choices' => Array
            (
                '' => 'default setting',
                '1' => 'yes',
                '0' => 'no',
            ),
            'start_fieldgroup' => Array ( 'title' => 'poll settings' ),
            'end_fieldgroup' => '',
        ),

        'schemadb' => array
        (
            'description' => 'schemadb',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nemein.quickpoll',
            'config_key' => 'schemadb',
            'widget' => 'text',
            'start_fieldgroup' => Array ( 'title' => 'schema settings' ),
        ),

        'archive_enable' => array
        (
            'description' => 'archive_enable',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nemein.quickpoll',
            'config_key' => 'archive_enable',
            'widget' => 'config_radiobox',
            'widget_radiobox_choices' => Array
            (
                '' => 'default setting',
                '1' => 'yes',
                '0' => 'no',
            ),
        ),
        
        'show_navigation_pseudo_leaves' => array
        (
            'description' => 'show pseudo leaves in navigation',
            'helptext' => 'set this to no if you want to hide feeds and archive links in navigation',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nemein.quickpoll',
            'config_key' => 'show_navigation_pseudo_leaves',
            'widget' => 'config_radiobox',
            'widget_radiobox_choices' => array
            (
                '' => 'default setting',
                '1' => 'yes',
                '0' => 'no',
            ),
            'end_fieldgroup' => '',
        ),
        
        'lock_ip_address' => array
        (
            'description' => 'lock_ip_address',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nemein.quickpoll',
            'config_key' => 'lock_ip_address',
            'widget' => 'config_radiobox',
            'widget_radiobox_choices' => Array
            (
                '' => 'default setting',
                '1' => 'yes',
                '0' => 'no',
            ),
        ),
       
    ),
),
