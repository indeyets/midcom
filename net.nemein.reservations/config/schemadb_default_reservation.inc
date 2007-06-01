'default' => Array
(
    'description' => 'reservation',
    'fields' => Array
    (
        'title' => Array
        (
            'title' => 'title',
            'storage' => 'title',
            'type' => 'text',
            'widget' => 'text',
            'required' => true,
        ),
        'start' => Array
        (
            // COMPONENT-REQUIRED
            'title' => 'start',
            'storage' => 'start',
            'type' => 'date',
            'type_config' => array
            ( 
                'storage_type' => 'UNIXTIME'
            ),
            'widget' => 'jsdate',
        ),
        'end' => Array
        (
            // COMPONENT-REQUIRED
            'title' => 'end',
            'storage' => 'end',
            'type' => 'date',
            'type_config' => array
            ( 
                'storage_type' => 'UNIXTIME' 
            ),
            'widget' => 'jsdate',
        ),
               
        /* End of component required fields */ 
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
    )
),

