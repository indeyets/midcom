'default' => array
(
    'description' => 'salesproject',
    'fields'      => array
    (
        'code' => array
        (
            'description' => 'code',
            'location'  => 'code',
            'datatype'  => 'text',
            'required'  => true,
        ),
        'title' => array
        (
            'description' => 'title',
            'location'  => 'title',
            'datatype'  => 'text',
            'required'  => true,
        ),
        'owner' => array
        (
            'description' => 'owner',
            'location'  => 'owner',
            'datatype'  => 'integer',
            'widget'    => 'contactchooser',
            'default'   => $_MIDGARD['user'],
        ),
        'status' => array
        (
            'description' => 'status',
            'location'  => 'status',
            'datatype'  => 'integer',
            'default'   => ORG_OPENPSA_SALESPROJECTSTATUS_ACTIVE,
            'widget'    => 'select',
            'widget_select_choices' => array
            (
                ORG_OPENPSA_SALESPROJECTSTATUS_ACTIVE   => 'active',
                ORG_OPENPSA_SALESPROJECTSTATUS_CANCELED => 'canceled',
                ORG_OPENPSA_SALESPROJECTSTATUS_LOST     => 'lost',
                ORG_OPENPSA_SALESPROJECTSTATUS_WON      => 'won',
            ),
        ),
        'probability' => array
        (
            'description' => 'probability',
            'location'  => 'config',
            'config_domain'  => 'org.openpsa.sales',
            'config_key'  => 'probability',
            'widget'    => 'select',
            'datatype'  => 'integer',
            'widget_select_choices' => array
            (
                25 => '25%',
                50 => '50%',
                75 => '75%',
                100 => '100%',
            ),
            'default'   => 50,
        ),
        'value' => array
        (
            'description' => 'value',
            'location'  => 'config',
            'config_domain'  => 'org.openpsa.sales',
            'config_key'  => 'value',
            'datatype'  => 'text',
            'default'   => 0,
        ),
        'profit' => array
        (
            'description' => 'profit',
            'location'  => 'config',
            'config_domain'  => 'org.openpsa.sales',
            'config_key'  => 'profit',
            'datatype'  => 'text',
            'default'   => 0,
        ),
        'close_est' => array (
            'description' => 'estimated closing date',
            'datatype'    => 'unixdate',
            'location'  => 'config',
            'config_domain'  => 'org.openpsa.sales',
            'config_key'  => 'close_est',
            'widget_date_enable_outside_ais' => true,
        ),
        'description' => array
        (
            'description' => 'description',
            'location'  => 'description',
            'datatype'  => 'markdown',
        ),
        'contacts' => array
        (
            'description' => 'contacts',
            'location'  => 'contacts',
            'datatype'  => 'array',
            'widget'    => 'contactchooser',
        ),
        'customer' => array (
            'description' => 'customer',
            'datatype'    => 'integer',
            'location'    => 'customer',
        ),
    ),
),
'newsalesproject' => array
(
    'description' => 'salesproject',
    'fields'      => array
    (
        'status' => array
        (
            'description' => 'status',
            'location'  => 'status',
            'datatype'  => 'integer',
            'default'   => ORG_OPENPSA_SALESPROJECTSTATUS_ACTIVE,
            'widget'    => 'select',
            'widget_select_choices' => array
            (
                ORG_OPENPSA_SALESPROJECTSTATUS_ACTIVE   => 'active',
                ORG_OPENPSA_SALESPROJECTSTATUS_LOST     => 'lost',
                ORG_OPENPSA_SALESPROJECTSTATUS_WON      => 'won',
            ),
            'hidden'    => true,
        ),
        'code' => array
        (
            'description' => 'code',
            'location'  => 'code',
            'datatype'  => 'text',
            'required'  => true,
        ),
        'title' => array
        (
            'description' => 'title',
            'location'  => 'title',
            'datatype'  => 'text',
            'required'  => true,
        ),
        'owner' => array
        (
            'description' => 'owner',
            'location'  => 'owner',
            'datatype'  => 'integer',
            'widget'    => 'contactchooser',
            'default'   => $_MIDGARD['user'],
        ),
        'contacts' => array
        (
            'description' => 'contacts',
            'location'  => 'contacts',
            'datatype'  => 'array',
            'widget'    => 'contactchooser',
        ),
    ),
),