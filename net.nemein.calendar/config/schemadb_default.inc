'event' => Array
(
    'description' => 'event',
    'fields' => Array
    (
        'name' => Array
        (
            // COMPONENT-REQUIRED
            'title'   => 'url name',
            'storage' => 'extra',
            'type'    => 'text',
            'widget'  => 'text',
            'hidden'  => (!$_MIDCOM->auth->admin),
        ),
        'title' => Array
        (
            // COMPONENT-REQUIRED
            'title' => 'title',
            'storage' => 'title',
            'required' => true,
            'type' => 'text',
            'widget' => 'text',
        ),

        'start' => Array
        (
            // COMPONENT-REQUIRED
            'title' => 'start',
            'storage' => 'start',
            'type' => 'date',
            'type_config' => Array ( 'storage_type' => 'UNIXTIME' ),
            'widget' => 'jsdate',
        ),
        'end' => Array
        (
            // COMPONENT-REQUIRED
            'title' => 'end',
            'storage' => 'end',
            'type' => 'date',
            'type_config' => Array ( 'storage_type' => 'UNIXTIME' ),
            'widget' => 'jsdate',
        ),
               
        /* End of component required fields */
        'description' => Array
        (
            'title' => 'description',
            'storage' => 'description',
            'type' => 'text',
            'type_config' => Array ( 'output_mode' => 'html' ),
            'widget' => 'tinymce',
        ),
        
    )
),

