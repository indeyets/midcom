'default' => array
(
    'description' => 'report',
    'fields'      => Array
    (
        'date' => Array
        (
            // COMPONENT-REQUIRED
            'title' => 'date',
            'storage' => 'date',
            'type' => 'date',
            'type_config' => Array 
            ( 
                'storage_type' => 'UNIXTIME',
            ),
            'widget' => 'jsdate',
            'widget_config' => Array 
            ( 
                'show_time' => false,
            ),
        ),
        'sendername' => Array
        (
            // COMPONENT-REQUIRED
            'title' => 'pilot',
            'storage' => 'sendername',
            'required' => true,
            'type' => 'text',
            'widget' => 'text',
        ),    
        'senderemail' => Array
        (
            // COMPONENT-REQUIRED
            'title' => 'email',
            'storage' => 'senderemail',
            'required' => true,
            'type' => 'text',
            'widget' => 'text',
        ),             
        'aerodrome' => Array
        (
            // COMPONENT-REQUIRED
            'title' => 'aerodrome',
            'storage' => 'aerodrome',
            'required' => true,
            'type' => 'text',
            'widget' => 'text',
        ),
        'organization' => Array
        (
            // COMPONENT-REQUIRED
            'title' => 'club',
            'storage' => 'organization',
            'type' => 'select',
            'type_config' => Array
            (
                'options' => Array
                (
                    'FLK' => 'FLK',                
                    'MIK' => 'MIK',
                    'MILK' => 'MILK',
                    'PIK' => 'PIK',
                ),
            ),
            'required' => true,
            'widget' => 'select',
        ),
        'plane' => Array
        (
            // COMPONENT-REQUIRED
            'title' => 'plane registration',
            'storage' => 'plane',
            'type' => 'select',
            'type_config' => Array
            (
                'options' => Array
                (
                    'OH-CAU' => 'OH-CAU',
                    'OH-CAY' => 'OH-CAY',
                    'OH-COX' => 'OH-COX',
                ),
            ),
            'required' => true,
            'widget' => 'select',
        ),
        'score' => Array
        (
            // COMPONENT-REQUIRED
            'title' => 'score',
            'storage' => 'score',
            'type' => 'select',
            'type_config' => Array
            (
                'options' => Array
                (
                    '1.0' => '1',
                    '1.5' => '1.5',
                    '2.0' => '2',
                    '3.0' => '3',
                ),
            ),
            'required' => true,
            'widget' => 'select',
        ),
        'comment' => Array
        (
            // COMPONENT-REQUIRED
            'title' => 'comment',
            'storage' => 'comment',
            'type' => 'text',
            'widget' => 'textarea',
        ),
    )
),

