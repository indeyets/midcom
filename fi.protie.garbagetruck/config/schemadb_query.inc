'query' => array
(
    'name' => 'query',
    'description' => 'query',
    'fields' => array
    (
        'start' => Array
        (
            // COMPONENT-REQUIRED
            'title' => 'start',
            'storage' => 'null',
            'type' => 'date',
            'type_config' => array
            (
                'storage_type' => 'UNIXTIME'
            ),
            'widget' => 'jsdate',
        ),
        'end' => Array
        (
            // COMPONENT-REQUIRED
            'title' => 'end',
            'storage' => 'null',
            'type' => 'date',
            'type_config' => array
            (
                'storage_type' => 'UNIXTIME'
            ),
            'widget' => 'jsdate',
        ),
        'persons' => array
        (
            'title' => 'persons',
            'storage' => 'null',
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
        'vehicle' => array
        (
            'title' => 'vehicle',
            'storage' => 'vehicle',
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
                'class'    => 'fi_protie_garbagetruck_vehicle_dba',
                'component' => 'fi.protie.garbagetruck',
                'titlefield' => 'regno',
                'idfield' => 'id',
                'constraints' => array
                (
                    array
                    (
                        'field' => 'regno',
                        'op'    => '<>',
                        'value' => '',
                    ),
                ),
                'searchfields' => array
                (
                    'regno',
                    'name',
                ),
                'orders' => array
                (
                    array
                    (
                        'regno' => 'ASC',
                    ),
                ),
            ),
        ),
    ),
),