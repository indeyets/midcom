'default' => array
(
    'description' => 'salesproject',
    'fields'      => array
    (
        'code' => Array
        (
            // COMPONENT-REQUIRED
            'title' => 'code',
            'storage' => 'code',
            'required' => true,
            'type' => 'text',
            'widget'  => 'text',
        ),
        'title' => Array
        (
            // COMPONENT-REQUIRED
            'title' => 'title',
            'storage' => 'title',
            'required' => true,
            'type' => 'text',
            'widget'  => 'text',
        ),
        'status' => Array
        (
            'title' => 'status',
            'storage' => 'status',
            'type' => 'select',
            'type_config' => Array
            (
                'options' => Array
                (
                    ORG_OPENPSA_SALESPROJECTSTATUS_ACTIVE   => 'active',
                    ORG_OPENPSA_SALESPROJECTSTATUS_LOST     => 'lost',
                    ORG_OPENPSA_SALESPROJECTSTATUS_WON      => 'won',
                ),
            ),
            'widget' => 'select',
        ),
        'probability' => Array
        (
            'title' => 'probability',
            'storage' => Array(
                'location' => 'configuration',
                'domain'   => 'org.openpsa.sales',
                'name'     => 'probability',
            ),
            'type' => 'select',
            'type_config' => Array
            (
                'options' => Array
                (
                    25 => '25%',
                    50 => '50%',
                    75 => '75%',
                    100 => '100%',
                ),
            ),
            'widget' => 'select',
        ),
        'description' => Array
        (
            'title' => 'description',
            'storage' => 'description',
            'type' => 'text',
            'type_config' => Array (
                'output_mode' => 'markdown'
            ),
            'widget' => 'textarea',
        ),
        'value' => Array
        (
            'title' => 'value',
            'storage' => Array(
                'location' => 'configuration',
                'domain'   => 'org.openpsa.sales',
                'name'     => 'value',
            ),
            'type' => 'number',
            'widget'  => 'text',
        ),
        'close_est' => Array
        (
            'title' => 'estimated closing date',
            'storage' => Array(
                'location' => 'configuration',
                'domain'   => 'org.openpsa.sales',
                'name'     => 'close_est',
            ),
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
    )
),