'config' => array
(
    'description' => 'component configuration',
    'fields' => array
    (
        'master_event' => array
        (
            'title' => 'master event',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'cc.kaktus.exhibitions',
                'name' => 'master_event',
            ),
            'type' => 'text',
            'widget' => 'text',
        ),
        'schemadb' => array
        (
            'title' => 'schemadb',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'cc.kaktus.exhibitions',
                'name' => 'schemadb',
            ),
            'type' => 'text',
            'widget' => 'text',
        ),
        'navigation_items' => array
        (
            'title' => 'number of items in navigation',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'cc.kaktus.exhibitions',
                'name' => 'navigation_items',
            ),
            'type' => 'text',
            'widget' => 'text',
        ),
        'show_pseudo_leaves' => array
        (
            'title' => 'show pseudo leaves in navigation',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'cc.kaktus.exhibitions',
                'name' => 'navigation_items',
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
        'enable_ajax_editing' => array
        (
            'title' => 'enable ajax editing',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'cc.kaktus.exhibitions',
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
    ),
),
