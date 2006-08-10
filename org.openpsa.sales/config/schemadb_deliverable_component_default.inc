'default' => array
(
    'description' => 'product component',
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
    )
),