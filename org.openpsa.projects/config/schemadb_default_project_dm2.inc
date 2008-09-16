'default' => array
(
    'description' => 'project',
    'fields'      => array
    (
        'title' => array
        (
            'title'    => 'title',
            'type'     => 'text',
            'widget'   => 'text',
            'storage'  => 'title',
            'required' => true,
        ),
        'customer' => array
        (
            'title'   => 'customer',
            'storage' => 'customer',
            'type' => 'select',
            'type_config' => array
            (
                 'require_corresponding_option' => false,
                 'options' => array(),
            ),
            'widget' => 'universalchooser',
            'widget_config' => array
            (
                'class'       => 'org_openpsa_contacts_group',
                'component'   => 'org.openpsa.contacts',
                'titlefield'  => 'official',
                'idfield'     => 'id',
                'constraints' => array(),
                'searchfields'  => array
                (
                    'name',
                    'official',
                ),
                'orders'        => array
                (
                    array('official'    => 'ASC'),
                    array('name'    => 'ASC'),
                ),
            ),
        ),
        'tags' => Array
        (
            'title' => 'skills required',
            'storage' => null,
            'type' => 'tags',
            'widget' => 'text',
        ),
        'start' => Array
        (
            'title' => 'start time',
            'storage' => 'start',
            'type' => 'date',
            'type_config' => Array
            (
                'storage_type' => 'UNIXTIME'
            ),
            'widget' => 'jsdate',
            'widget_config' => Array
            (
                'show_time' => false,
            ),
        ),
        'end' => Array
        (
            'title' => 'deadline',
            'storage' => 'end',
            'type' => 'date',
            'type_config' => Array
            (
                'storage_type' => 'UNIXTIME'
            ),
            'widget' => 'jsdate',
            'widget_config' => Array
            (
                'show_time' => false,
            ),
        ),
        'description' => Array
        (
            'title' => 'description',
            'storage' => 'description',
            'type' => 'text',
            'type_config' => array
            (
                'output_mode' => 'markdown'
            ),
            'widget' => 'textarea',
        ),
        'manager' => array
        (
            'title'   => 'manager',
            'storage' => 'manager',
            //'required' => true,
            'type' => 'select',
            'type_config' => array
            (
                 'require_corresponding_option' => false,
                 'options' => array(),
            ),
            'widget' => 'universalchooser',
            'widget_config' => array
            (
                'class'       => 'org_openpsa_contacts_person',
                'component'   => 'org.openpsa.contacts',
                'titlefield'  => 'name',
                'idfield'     => 'id',
                'constraints' => array
                (
                    array
                    (
                        'field' => 'username',
                        'op'    => '<>',
                        'value' => '',
                    ),
                ),
                'searchfields'  => array
                (
                    'firstname',
                    'lastname',
                    'username',
                ),
                'orders'        => array
                (
                    array('lastname'    => 'ASC'),
                    array('firstname'    => 'ASC'),

                ),
            ),
        ),
        'resources' => array
        (
            'title'   => 'resources',
            'storage' => 'resources2',
            'type' => 'select',
            'type_config' => array
            (
                 'require_corresponding_option' => false,
                 'allow_multiple' => true,
                 'multiple_storagemode' => 'array',
                 'options' => array(),
            ),
            'widget' => 'universalchooser',
            'widget_config' => array
            (
                'class'       => 'org_openpsa_contacts_person',
                'component'   => 'org.openpsa.contacts',
                'titlefield'  => 'name',
                'idfield'     => 'id',
                'constraints' => array
                (
                    array
                    (
                        'field' => 'username',
                        'op'    => '<>',
                        'value' => '',
                    ),
                ),
                'searchfields'  => array
                (
                    'firstname',
                    'lastname',
                    'username',
                ),
                'orders'        => array
                (
                    array('lastname'    => 'ASC'),
                    array('firstname'    => 'ASC'),
                ),
            ),
        ),
        'contacts' => array
        (
            'title'   => 'contacts',
            'storage' => 'contacts2',
            'type' => 'select',
            'type_config' => array
            (
                 'require_corresponding_option' => false,
                 'allow_multiple' => true,
                 'multiple_storagemode' => 'array',
                 'options' => array(),
            ),
            'widget' => 'universalchooser',
            'widget_config' => array
            (
                'class'       => 'org_openpsa_contacts_person',
                'component'   => 'org.openpsa.contacts',
                'titlefield'  => 'name',
                'idfield'     => 'id',
                'constraints' => array(),
                'searchfields'  => array
                (
                    'firstname',
                    'lastname',
                    'username',
                ),
                'orders'        => array
                (
                    array('lastname'    => 'ASC'),
                    array('firstname'    => 'ASC'),
                ),
            ),
        ),
    )
),