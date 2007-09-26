'default' => array
(
    'description' => 'featured',
    'fields'      => array
    (
        'title' => Array
        (
            // COMPONENT-REQUIRED
            'title' => 'title',
            'storage' => 'title',
            'type' => 'text',
            'widget' => 'text',
        ),
        'group_name' => Array
        (
            // COMPONENT-REQUIRED
            'title' => 'group name',
            'storage' => 'groupName',
            'type' => 'select',
            'type_config' => array
            (
                'options' => net_nemein_featured_viewer::get_groups_callback(),
            ),
            'widget' => 'select',
        ),
        'object_location' => Array
        (
            // COMPONENT-REQUIRED
            'title' => 'object location',
            'storage' => 'objectLocation',
            'type' => 'text',
            'widget' => 'text',
            'required' => true,
        ),
        'defaultStyle' => Array
        (
            // COMPONENT-REQUIRED
            'title' => 'default style',
            'storage' => 'defaultStyle',
            'type' => 'text',
            'widget' => 'text',
        ),
    ),
),
