'config' => array
(
    'name'        => 'config',
    'description' => 'Default Configuration Schema',
    'locktimeout' => 0,
    'fields'      => array
    (

        'index_entries' => array
        (
            'description' => 'index_entries',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nemein.quickpoll',
            'config_key' => 'index_entries',
            'widget' => 'text',
            'start_fieldgroup' => Array ( 'title' => 'blog settings' ),
        ),
        
        'categories' => array
        (
            'description' => 'categories',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nemein.quickpoll',
            'config_key' => 'categories',
            'widget' => 'text',
            'end_fieldgroup' => '',
        ),

        'schemadb' => array
        (
            'description' => 'schemadb',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nemein.quickpoll',
            'config_key' => 'schemadb',
            'widget' => 'select',
            'widget_select_choices' => $GLOBALS['net_nemein_quickpoll_schemadbs'],
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
       
    ),
),
