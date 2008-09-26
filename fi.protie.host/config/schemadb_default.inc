'default' => array
(
    'description' => 'host',
    'fields' => array
    (
        'name' => array
        (
            'title' => 'name',
            'storage' => 'name',
            'type' => 'text',
            'widget' => 'text',
            'required' => true,
        ),
        'prefix' => array
        (
            'title' => 'prefix',
            'storage' => 'prefix',
            'type' => 'text',
            'widget' => 'text',
        ),
        'page' => array
        (
            'title' => 'root page',
            'storage' => 'root',
            'type' => 'select',
            'type_config' => array
            (
                'allow_other' => true,
                'require_corresponding_option' => false,
                'options' => array(),
            ),
            'widget' => 'chooser',
            'widget_config' => array
            (
                'class'       => 'midcom_db_host',
                'component'   => 'fi.protie.host',
                'titlefield'  => 'title',
                'idfield'     => 'id',
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
                        'field' => 'name',
                        'op' => '<>',
                        'value' => '',
                    ),
                ),
                'result_headers' => array
                (
                    array
                    (
                        'name' => 'title',
                        'title' => 'title',
                    ),
                    array
                    (
                        'name' => 'name',
                        'title' => 'name',
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
                        'name' => 'ASC',
                    ),
                ),
            ),
        ),
        'online' => array
        (
            'title' => 'online',
            'storage' => 'online',
            'type' => 'boolean',
            'widget' => 'checkbox',
        ),
    ),
),