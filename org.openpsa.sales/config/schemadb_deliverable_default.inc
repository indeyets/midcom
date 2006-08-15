'default' => array
(
    'description' => 'single delivery',
    'fields'      => array
    (   
        'title' => Array
        (
            // COMPONENT-REQUIRED
            'title' => 'title',
            'storage' => 'title',
            'required' => true,
            'type' => 'text',
            'widget'  => 'text',
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
        'pricePerUnit' => Array
        (
            'title' => 'price per unit',
            'storage' => 'pricePerUnit',
            'type' => 'number',
            'widget'  => 'text',
        ),
        'units' => Array
        (
            'title' => 'units',
            'storage' => 'units',
            'type' => 'number',
            'widget'  => 'text',
        ),
        'unit' => Array
        (
            'title' => 'unit',
            'storage' => 'unit',
            'type' => 'select',
            'type_config' => Array
            (
                'options' => Array
                (
                    'p' => 'unit',       // per piece
                    'h' => 'hour',    // per hour
                    'd' => 'day',     // per day
                    'm' => 'month',   // per month
                    'q' => 'quarter', // per quarter
                    'y' => 'year',    // per annum
                ),
            ),
            'readonly' => true,
            'widget' => 'select',
        ),
        'price' => Array
        (
            'title' => 'price',
            'storage' => 'price',
            'type' => 'number',
            'widget'  => 'text',
            'readonly' => true,
        ),
        'end' => Array
        (
            'title' => 'estimated delivery',
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
        'components' => Array
        (
            'title' => 'components',
            'storage' => null,
            'type' => 'composite',
            'type_config' => Array
            (
                'child_class' => 'org_openpsa_sales_salesproject_deliverable',
                'child_schemadb' => 'file:/org/openpsa/sales/config/schemadb_deliverable_component_default.inc',
                'child_foreign_key_fieldname' => 'up',
                'style_element_name' => 'deliverable',
                'enable_creation' => false,
            ),
            'widget' => 'composite',
        ),
    )
),
'subscription' => array
(
    'description' => 'recurring subscription',
    'fields'      => array
    (   
        'title' => Array
        (
            // COMPONENT-REQUIRED
            'title' => 'title',
            'storage' => 'title',
            'required' => true,
            'type' => 'text',
            'widget'  => 'text',
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
        'pricePerUnit' => Array
        (
            'title' => 'price per unit',
            'storage' => 'pricePerUnit',
            'type' => 'number',
            'widget'  => 'text',
        ),
        'units' => Array
        (
            'title' => 'units',
            'storage' => 'units',
            'type' => 'number',
            'widget'  => 'text',
        ),
        'unit' => Array
        (
            'title' => 'invoicing period',
            'storage' => 'unit',
            'type' => 'select',
            'type_config' => Array
            (
                'options' => Array
                (
                    'm' => 'month',   // per month
                    'q' => 'quarter', // per quarter
                    'y' => 'year',    // per annum
                ),
            ),
            'readonly' => true,
            'widget' => 'select',
        ),
        'price' => Array
        (
            'title' => 'price',
            'storage' => 'price',
            'type' => 'number',
            'widget'  => 'text',
            'readonly' => true,
        ),
        'start' => Array
        (
            'title' => 'subscription begins',
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
            'title' => 'subscription ends',
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
        'components' => Array
        (
            'title' => 'components',
            'storage' => null,
            'type' => 'composite',
            'type_config' => Array
            (
                'child_class' => 'org_openpsa_sales_salesproject_deliverable',
                'child_schemadb' => 'file:/org/openpsa/sales/config/schemadb_deliverable_component_default.inc',
                'child_foreign_key_fieldname' => 'up',
                'style_element_name' => 'deliverable',
                'enable_creation' => false,
            ),
            'widget' => 'composite',
        ),
    )
),