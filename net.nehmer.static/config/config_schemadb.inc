'config' => array
(
    'description' => 'Default Configuration Schema',
    'fields' => array
    (
        'sort_order' => array
        (
            'title' => 'sort_order',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'net.nehmer.static',
                'name' => 'sort_order',
            ),
            'type' => 'select',
            'type_config' => array
            (
                'options' => array
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
            'widget' => 'select',
        ),
        'autoindex' => array
        (
            'title' => 'activate autoindex',
            'helptext' => 'autoindex-notes',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'net.nehmer.static',
                'name' => 'autoindex',
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
                'title' => 'display settings'
            ),
        ),
        
        'show_blobs_in_autoindex' => array
        (
            'title' => 'show blobs in autoindex',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'net.nehmer.static',
                'name' => 'show_blobs_in_autoindex',
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
        
        'indexinnav' => array
        (
            'title' => 'indexinnav',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'net.nehmer.static',
                'name' => 'indexinnav',
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
        'hide_navigation' => array
        (
            'title' => 'hide articles from navigation',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'net.nehmer.static',
                'name' => 'hide_navigation',
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
        
        'ajax_enable' => array
        (
            'title' => 'ajax_enable',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'net.nehmer.static',
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
        ),
        
        'auto_approve' => array
        (
            'title' => 'auto_approved',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'net.nehmer.static',
                'name' => 'auto_approve',
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
        'simple_name_handling' => array
        (
            'title' => 'simple_name_handling',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'net.nehmer.static',
                'name' => 'simple_name_handling',
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
        'schemadb' => array
        (
            'title' => 'schemadb',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'net.nehmer.static',
                'name' => 'schemadb',
            ),
            'type' => 'select',
            'type_config' => array
            (
                'options' => $GLOBALS['net_nehmer_static_schemadbs'],
            ),
            'widget' => 'select',
            'start_fieldgroup' => array
            (
                'title' => 'schema settings'
            ),
        ),
        
        'symlink_topic' => array
        (
            'title' => 'symlink_topic',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'net.nehmer.static',
                'name' => 'symlink_topic',
            ),
            'type' => 'select',
            'type_config' => array
            (
                'require_corresponding_option' => false,
                'options' => array(),
                'allow_other' => true,
                'allow_multiple' => false,
            ),
            'widget' => 'chooser',
            'widget_config' => array
            (
                'class'       => 'midcom_db_topic',
                'component'   => 'net.nehmer.static',
                'titlefield'  => 'extra',
                'idfield'     => 'guid',
                'searchfields' => array
                (
                    array
                    (
                        'title' => 'ASC',
                    ),
                    array
                    (
                        'extra' => 'ASC',
                    ),
                    array
                    (
                        'component' => 'ASC',
                    ),
                    array
                    (
                        'name' => 'ASC',
                    ),
                ),
                'constraints' => array
                (
                    array
                    (
                        'field' => 'extra',
                        'op' => '<>',
                        'value' => '',
                    ),
                    array
                    (
                        'field' => 'component',
                        'op' => '=',
                        'value' => 'net.nehmer.static',
                    ),
                ),
                'result_headers' => array
                (
                    array
                    (
                        'name' => 'extra',
                        'title' => 'extra',
                    ),
                    array
                    (
                        'name' => 'name',
                        'title' => 'name',
                    ),
                    array
                    (
                        'name' => 'component',
                        'title' => 'component',
                    ),
                ),
                'orders' => array
                (
                    array
                    (
                        'title' => 'ASC',
                    ),
                    array
                    (
                        'extra' => 'ASC',
                    ),
                    array
                    (
                        'name' => 'ASC',
                    ),
                ),
            ),
            'end_fieldgroup' => '',
        ),
    ),
),
