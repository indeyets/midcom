'default' => array
(
    'name' => 'default',
    'description' => 'todo list item',
    'fields' => array
    (
        'title' => array
        (
            'title' => 'title',
            'storage' => 'title',
            'type' => 'text',
            'widget' => 'text',
        ),
        'deadline' => array
        (
            // Component required
            'title' => 'deadline',
            'storage' => 'deadline',
            'type' => 'text',
            'widget' => 'text',
            'type' => 'date',
            'type_config' => array (),
            'widget' => 'jsdate',
        ),
        'pid' => array
        (
            'title' => 'person',
            'storage' => 'pid',
            'required' => true,
            'type' => 'select',
            'type_config' => array
            (
                'require_corresponding_option' => false,
                'options' => array (),
                'allow_other' => true,
            ),
            'widget' => 'universalchooser',
            'widget_config' => array
            (
                'class'    => 'midcom_db_person',
                'component' => 'net.nemein.personnel',
                'titlefield' => 'rname',
                'idfield' => 'id',
                'constraints' => array
                (
                    array
                    (
                        'field' => 'firstname',
                        'op'    => '<>',
                        'value' => '',
                    ),
                    array
                    (
                        'field' => 'lastname',
                        'op'    => '<>',
                        'value' => '',
                    ),
                ),
                'searchfields' => array
                (
                    'firstname',
                    'lastname',
                    'email',
                ),
                'orders' => array
                (
                    array
                    (
                        'lastname' => 'ASC',
                    ),
                    array
                    (
                        'firstname' => 'ASC',
                    ),
                ),
            ),
        ),
        'gid' => array
        (
            'title' => 'group',
            'storage' => 'parameter',
            'type' => 'select',
            'type_config' => array
            (
                'require_corresponding_option' => false,
                'options' => array (),
                'allow_multiple' => true,
                'multiple_storagemode' => 'serialized',
                'allow_other' => true,
            ),
            'widget' => 'universalchooser',
            'widget_config' => array
            (
                'class'    => 'midcom_db_group',
                'component' => 'net.nemein.organisations',
                'titlefield' => 'name',
                'idfield' => 'id',
                'constraints' => array
                (
                    array
                    (
                        'field' => 'name',
                        'op'    => '<>',
                        'value' => '',
                    ),
                ),
                'searchfields' => array
                (
                    'name',
                    'official',
                ),
                'orders' => array
                (
                    array
                    (
                        'name' => 'ASC',
                    ),
                    array
                    (
                        'official' => 'ASC',
                    ),
                ),
            ),
        ),
        'description' => array
        (
            'title' => 'description',
            'storage' => 'description',
            'type' => 'text',
            'type_config' => array
            (
                'output_mode' => 'html',
            ),
            'widget' => 'tinymce',
        ),
        'priority' => array
        (
            'title' => 'priority',
            'storage' => 'priority',
            'type' => 'select',
            'type_config' => array
            (
                'options' => array
                (
                    '' => 'choose the priority level',
                    1 => 'lowest',
                    2 => 'low',
                    3 => 'normal',
                    4 => 'high',
                    5 => 'critical',
                ),
            ),
            'widget' => 'select',
        ),
    ),
),