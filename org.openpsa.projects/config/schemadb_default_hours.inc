'default' => array (

    'name'        => 'default',
    'description' => 'hour report',
    'fields'      => array (
        // Metadata
        //Hidden field for orgOpenpsaObtype
        'object_type'  => array(
            'default'  => ORG_OPENPSA_OBTYPE_HOUR_REPORT,
            'hidden'   => true,
            'location' => 'orgOpenpsaObtype',
            'datatype' => 'integer',
            'widget'   => 'text',
        ),
        'guid'  => array(
            'hidden'   => true,
            'location' => 'guid',
            'datatype' => 'text',
        ),        
        'task' => array (
            'hidden'   => true,        
            'description' => 'task',
            'datatype'    => 'integer',
            'location'    => 'task',
            'required'    => true,
        ),
        'date' => array (
            'description' => 'date',
            'datatype'    => 'unixdate',
            'location'    => 'date',
            'widget_date_enable_outside_ais' => true,
        ),
        'hours' => array (
            'description' => 'hours',
            'datatype'    => 'number',
            'location'    => 'hours',
            'required'    => true,
            'number_precision'   => 2,
        ),
        'invoiceable' => array (
            'description' => 'invoiceable',
            'location'    => 'invoiceable',
            'datatype'    => 'boolean',
        ),
        /* Testing
        'report_type' => array (
            'datatype'    => 'text',
            'description' => 'type',
            'location'    => 'reportType',
            'widget'      => 'select',
            'widget_select_choices' => array (
                'test type' => 'test type',
                'other type' => 'other type',
            ),
        ),
        */
        'person' => array (
            'datatype'    => 'integer',
            'description' => 'reporter',
            'location'    => 'person',
            'widget'      => 'select',
            'widget_select_choices' => Array(),
            'default'     => $_MIDGARD['user'],
        ),
        'description' => array (
            'description' => 'description',
            'datatype'    => 'text',
            'location'    => 'description',
            'widget_text_inputstyle' => 'longtext',
            'widget_text_height' => '5em',
        ),
    )
),