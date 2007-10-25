'config' => array
(
    'name'        => 'config',
    'description' => 'Default Configuration Schema',
    'locktimeout' => 0,
    'fields'      => array
    (
        'sort_order' => array
        (
            'description' => 'sort_order',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nehmer.static',
            'config_key' => 'sort_order',
            'widget' => 'select',
            'widget_select_choices' => array
            (
                'score' => 'score',
                'created' => 'created',
                'reverse created' => 'reverse created',
                'name' => 'name',
                'reverse name' => 'reverse name',
                'reverse score' => 'reverse score',
                'revised' => 'revised',
                'reverse revised' => 'reverse revised',
                'calendar' => 'calendar',
                'reverse calendar' => 'reverse calendar'
            ),
        ),
        'autoindex' => array
        (
            'description' => 'activate autoindex',
            'helptext' => 'autoindex-notes',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nehmer.static',
            'config_key' => 'autoindex',
            'widget' => 'config_radiobox',
            'widget_radiobox_choices' => array
            (
                '' => 'default setting',
                '1' => 'yes',
                '0' => 'no',
            ),
            'start_fieldgroup' => array
            (
                'title' => 'display settings'
            ),
        ),
        
        'show_blobs_in_autoindex' => array
        (
            'description' => 'show blobs in autoindex',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nehmer.static',
            'config_key' => 'show_blobs_in_autoindex',
            'widget' => 'config_radiobox',
            'widget_radiobox_choices' => array
            (
                '' => 'default setting',
                '1' => 'yes',
                '0' => 'no',
            ),
        ),
        
        'indexinnav' => array
        (
            'description' => 'indexinnav',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nehmer.static',
            'config_key' => 'indexinnav',
            'widget' => 'config_radiobox',
            'widget_radiobox_choices' => array
            (
                '' => 'default setting',
                '1' => 'yes',
                '0' => 'no',
            ),
        ),
        
        'ajax_enable' => array
        (
            'description' => 'ajax_enable',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nehmer.static',
            'config_key' => 'enable_ajax_editing',
            'widget' => 'config_radiobox',
            'widget_radiobox_choices' => array
            (
                '' => 'default setting',
                '1' => 'yes',
                '0' => 'no',
            ),
        ),
        
        'auto_approve' => array
        (
            'description' => 'auto_approved',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nehmer.static',
            'config_key' => 'auto_approve',
            'widget' => 'config_radiobox',
            'widget_radiobox_choices' => array
            (
                '' => 'default setting',
                '1' => 'yes',
                '0' => 'no',
            ),
            
            'end_fieldgroup' => '',
        ),
        'simple_name_handling' => array
        (
            'description' => 'simple_name_handling',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nehmer.static',
            'config_key' => 'simple_name_handling',
            'widget' => 'config_radiobox',
            'widget_radiobox_choices' => array
            (
                '' => 'default setting',
                '1' => 'yes',
                '0' => 'no',
            ),
        ),
        'schemadb' => array
        (
            'description' => 'schemadb',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nehmer.static',
            'config_key' => 'schemadb',
            'widget' => 'select',
            'widget_select_choices' => $GLOBALS['net_nehmer_static_schemadbs'],
            'start_fieldgroup' => array
            (
                'title' => 'schema settings'
            ),
        ),
        
        'symlink_topic' => array
        (
            'description' => 'symlink_topic',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nehmer.static',
            'config_key' => 'symlink_topic',
            'widget' => 'select',
            'widget_select_choices' => net_nehmer_static_symlink_topic_list(),
            'end_fieldgroup' => '',
        ),
    ),
),
