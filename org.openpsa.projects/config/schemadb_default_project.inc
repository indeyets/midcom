'default' => array (

    'name'        => 'default',
    'description' => 'project',
    'fields'      => array (
        // Metadata
        //Hidden field for orgOpenpsaObtype
        'object_type'  => array(
            'default'  => ORG_OPENPSA_OBTYPE_PROJECT,
            'hidden'   => true,
            'location' => 'orgOpenpsaObtype',
            'datatype' => 'integer',
        ),
        'title' => array (
            'description' => 'title',
            'datatype'    => 'text',
            'location'    => 'title',
            'start_fieldgroup' => array (
                'title' => 'basic information',
                'css_group' => 'area meta',
            ),
            'required'    => true,
        ),
        'description' => array (
            'description' => 'description',
            'datatype'    => 'markdown',
            'location'    => 'description',
            'widget_text_inputstyle' => 'longtext',
        ),
        'manager' => array (
            'description' => 'manager',
            'datatype'    => 'integer',
            'location'    => 'manager',
            'widget'      => 'contactchooser',
        ),
        'resources' => array (
            'description' => 'resources',
            'datatype'    => 'array',
            'location'    => 'resources',
            'widget'      => 'contactchooser',
        ),
        'contacts' => array (
            'description' => 'contacts',
            'datatype'    => 'array',
            'location'    => 'contacts',
            'widget'      => 'contactchooser',
        ),
        'customer' => array (
            'description' => 'customer',
            'datatype'    => 'integer',
            'location'    => 'customer',
        ),
        'start' => array (
            'description' => 'start time',
            'datatype'    => 'unixdate',
            'location'    => 'start',
            'widget_date_enable_outside_ais' => true,
        ),
        'end' => array (
            'description' => 'deadline',
            'datatype'    => 'unixdate',
            'location'    => 'end',
            'widget_date_enable_outside_ais' => true,
        ),
        'invoiceable_default' => array (
            'description' => 'hours are invoiceable by default',
            'datatype'    => 'boolean',
            'location'    => 'hoursInvoiceableDefault',
            'end_fieldgroup' => '',
        ),
        'orgOpenpsaAccesstype' => array (
            'description' => 'access type',
            'location'    => 'orgOpenpsaAccesstype',
            'datatype'     => 'integer',
            'widget'       => 'select',
            'widget_select_choices' => $GLOBALS['org_openpsa_core_acl_options'],
            'start_fieldgroup' => array (
                'title' => 'access control',
                'css_group' => 'area acl',
            ),
        ),
        'orgOpenpsaOwnerWg' => array (
            'description' => 'workgroup',
            'location'    => 'orgOpenpsaOwnerWg',
            'datatype'     => 'text',
            'widget'       => 'select',
            'widget_select_choices' => org_openpsa_helpers_workgroups(),
            'end_fieldgroup' => '',
        ),
    )
),
'newproject' => array (

    'name'        => 'newproject',
    'description' => 'project',
    'fields'      => array (
        // Metadata
        //Hidden field for orgOpenpsaObtype
        'object_type'  => array(
            'default'  => ORG_OPENPSA_OBTYPE_PROJECT,
            'hidden'   => true,
            'location' => 'orgOpenpsaObtype',
            'datatype' => 'integer',
        ),
        'title' => array (
            'description' => 'Title',
            'datatype'    => 'text',
            'location'    => 'title',
            'start_fieldgroup' => array (
                'title' => 'new project',
                'css_group' => 'area meta',
            ),
            'required'    => true,
        ),
        'resources' => array (
            'description' => 'resources',
            'datatype'    => 'array',
            'location'    => 'resources',
            'widget'      => 'contactchooser',
            'default'     => org_openpsa_helpers_resources(),
        ),
        'contacts' => array (
            'description' => 'contacts',
            'datatype'    => 'array',
            'location'    => 'contacts',
            'widget'      => 'contactchooser',
        ),
        'end' => array (
            'description' => 'deadline',
            'datatype'    => 'unixdate',
            'location'    => 'end',
            'widget_date_enable_outside_ais' => true,
        ),
        'invoiceable_default' => array (
            'description' => 'hours are invoiceable by default',
            'datatype'    => 'boolean',
            'location'    => 'hoursInvoiceableDefault',
            'end_fieldgroup' => '',
        ),
        'orgOpenpsaAccesstype' => array (
            'description' => 'access type',
            'location'    => 'orgOpenpsaAccesstype',
            'datatype'     => 'integer',
            'widget'       => 'select',
            'widget_select_choices' => $GLOBALS['org_openpsa_core_acl_options'],
            'start_fieldgroup' => array (
                'title' => 'access control',
                'css_group' => 'area acl',
            ),
        ),
        'orgOpenpsaOwnerWg' => array (
            'description' => 'workgroup',
            'location'    => 'orgOpenpsaOwnerWg',
            'datatype'     => 'text',
            'widget'       => 'select',
            'widget_select_choices' => org_openpsa_helpers_workgroups(),
            'default' => $GLOBALS['org_openpsa_core_workgroup_filter'],
            'end_fieldgroup' => '',
        ),
    )
)