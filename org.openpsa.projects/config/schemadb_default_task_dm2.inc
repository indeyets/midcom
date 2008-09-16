'default' => array
(
    'description' => 'task',
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
        'plannedHours' => array
        (
            'title'    => 'planned hours',
            'type'     => 'number',
            'widget'   => 'text',
            'storage'  => 'plannedHours',
        ),
        'minimum_slot' => Array
        (
            'title' => 'minimum time slot needed for task bookings',
            'storage' => Array(
                'location' => 'configuration',
                'domain'   => 'org.openpsa.projects.projectbroker',
                'name'     => 'minimum_slot',
            ),
            'type' => 'number',
            'widget'  => 'text',
        ),
        'resource_seek_type' => Array
        (
            'title' => 'automatic resource search',
            'storage' => 'resource_seek_type',
            'type' => 'select',
            'type_config' => Array
            (
                'options' => Array
                (
                    'none'          => 'no search',
                    'organization'  => 'my organization',
                    'openpsa'       => 'whole openpsa',
                    'dbe'           => 'whole world (dbe)',
                ),
            ),
            'widget' => 'select',
        ),
        'agreement' => array
        (
            'title'   => 'agreement',
            'storage' => 'agreement',
            'type' => 'select',
            'type_config' => array
            (
                 'require_corresponding_option' => false,
                 'options' => array(),
            ),
            'widget' => 'universalchooser',
            'widget_config' => array
            (
                'class'       => 'org_openpsa_sales_salesproject_deliverable',
                'component'   => 'org.openpsa.sales',
                'titlefield'  => 'deliverable_html',
                'idfield'     => 'id',
                'constraints' => array(),
                'searchfields'  => array
                (
                    'title',
                    'salesproject.title'
                ),
                'orders'        => array
                (
                    array('title'    => 'ASC'),
                ),
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
        'hours' => Array
        (
            'title' => 'hours',
            'storage' => null,
            'type' => 'composite',
            'type_config' => Array
            (
                'child_class'                 => 'org_openpsa_projects_hour_report',
                'child_schemadb'              => 'file:/org/openpsa/projects/config/schemadb_default_hours_dm2.inc',
                'child_foreign_key_fieldname' => 'task',
                'style_element_name'          => 'hours',
                'area_element'                => 'tr',
                'orders'                      => array
                (
                    'date'    => 'ASC',
                    'created' => 'ASC',
                ),
                'defaults'                    => array
                (
                    'date'    => time(),
                    'person'  => $_MIDGARD['user'],
                ),
                'wide_mode'                 => true,
            ),
            'widget' => 'composite',
        ),
    )
),