'log' => array
(
    'name' => 'log',
    'description' => 'log',
    'fields' => array
    (
        'recorddate' => Array
        (
            // COMPONENT-REQUIRED
            'title' => 'recorddate',
            'storage' => 'recorddate',
            'required' => true,
            'type' => 'date',
            'type_config' => array (
                'storage_type' => 'UNIXTIME'
            ),
            'widget' => 'jsdate',
        ),
        'route' => array
        (
            'title' => 'route',
            'storage' => 'route',
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
                'class'    => 'fi_protie_garbagetruck_route_dba',
                'component' => 'fi.protie.garbagetruck',
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
                ),
                'orders' => array
                (
                    array
                    (
                        'name' => 'ASC',
                    ),
                ),
            ),
        ),
        'persons' => array
        (
            'title' => 'persons',
            'storage' => 'persons',
            'required' => true,
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
        'tjp' => array
        (
            'title' => 'Työnjohtopiiri',
            'type' => 'text',
            'storage' => 'tjp',
            'widget' => 'text',
        ),
        'hours' => array
        (
            'title' => 'work hours',
            'type' => 'number',
            'storage' => 'hours',
            'widget' => 'text',
        ),
        'mileage' => array
        (
            'title' => 'mileage',
            'type' => 'number',
            'storage' => 'mileage',
            'widget' => 'text',
        ),
        'cargos' => array
        (
            'title' => 'cargos',
            'type' => 'number',
            'storage' => 'parameter',
            'widget' => 'text',
        ),
        'mileage' => array
        (
            'title' => 'mileage',
            'type' => 'number',
            'storage' => 'mileage',
            'widget' => 'text',
        ),
        'shift' => array
        (
            'title' => 'shift',
            'type' => 'select',
            'type_config' => array
            (
                'options' => array
                (
                    'ap' => 'Aamupäivä',
                    'ip' => 'Iltapäivä',
                ),
            ),
            'storage' => 'mileage',
            'widget' => 'select',
        ),
        'mileage' => array
        (
            'title' => 'mileage',
            'type' => 'number',
            'storage' => 'mileage',
            'widget' => 'text',
        ),
        'siirto1120m' => array
        (
            'title' => 'siirto1120m',
            'type' => 'number',
            'storage' => 'siirto1120m',
            'widget' => 'text',
        ),
        'siirto2130m' => array
        (
            'title' => 'siirto2130m',
            'type' => 'number',
            'storage' => 'siirto2130m',
            'widget' => 'text',
        ),
        'siirto3140m' => array
        (
            'title' => 'siirto3140m',
            'type' => 'number',
            'storage' => 'siirto3140m',
            'widget' => 'text',
        ),
        'siirto4150m' => array
        (
            'title' => 'siirto4150m',
            'type' => 'number',
            'storage' => 'siirto4150m',
            'widget' => 'text',
        ),
        'siirto51plus' => array
        (
            'title' => 'siirto51plus',
            'type' => 'number',
            'storage' => 'siirto51plus',
            'widget' => 'text',
        ),
        'sakki150m' => array
        (
            'title' => 'sakki150m',
            'type' => 'number',
            'storage' => 'sakki150m',
            'widget' => 'text',
        ),
        'f140lastia' => array
        (
            'title' => 'f140lastia',
            'type' => 'number',
            'storage' => 'f140lastia',
            'widget' => 'text',
        ),
        'f240lastia' => array
        (
            'title' => 'f240lastia',
            'type' => 'number',
            'storage' => 'f240lastia',
            'widget' => 'text',
        ),
        'f300lastia' => array
        (
            'title' => 'f300lastia',
            'type' => 'number',
            'storage' => 'f300lastia',
            'widget' => 'text',
        ),
        'f600lastia' => array
        (
            'title' => 'f600lastia',
            'type' => 'number',
            'storage' => 'f600lastia',
            'widget' => 'text',
        ),
        'netweight' => array
        (
            'title' => 'netweight',
            'type' => 'number',
            'storage' => 'netweight',
            'widget' => 'text',
        ),
        'cost' => array
        (
            'title' => 'cost',
            'type' => 'number',
            'storage' => 'cost',
            'widget' => 'text',
        ),
    ),
),