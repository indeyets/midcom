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
            'start_fieldset' => array (
                'title' => 'basic information',
                'css_group' => 'area meta',
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

        'invoiceable_default' => array 
        (
            'title' => 'hours are invoiceable by default',
            'type' => 'boolean',
            'storage' => 'hoursInvoiceableDefault',
            'widget' => 'checkbox',
            'end_fieldset' => '',
        ),
        'orgOpenpsaAccesstype' => array 
        (
            'title' => 'access type',
            'storage' => 'orgOpenpsaAccesstype',
            'type' => 'select',
            'type_config' => array
            (
                 'options' => $GLOBALS['org_openpsa_core_acl_options'],
            ),
            'widget' => 'select',
            'start_fieldset' => array 
            (
                'title' => 'access control',
                'css_group' => 'area acl',
            ),
        ),
        'orgOpenpsaOwnerWg' => array 
        (
            'title' => 'workgroup',
            'storage' => 'orgOpenpsaOwnerWg',
            'type' => 'select',
            'type_config' => array
            (
                'options' => org_openpsa_helpers_workgroups(),
            ),
            'widget' => 'select',
            'end_fieldset' => '',
        ),
    )
),