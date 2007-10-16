'event' => Array
(
    'description' => 'event',
    'fields' => Array
    (
        'name' => Array
        (
            // COMPONENT-REQUIRED
            'title'   => 'url name',
            'storage' => 'name',
            'type'    => 'text',
            'widget'  => 'text',
            'write_privilege' => array
            (
                'privilege' => 'midcom:urlname',
            ),
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
            'type_config' => Array ( 'storage_type' => 'ISO' ),
            'widget' => 'jsdate',
        ),
        'end' => Array
        (
            // COMPONENT-REQUIRED
            'title' => 'end',
            'storage' => 'end',
            'type' => 'date',
            'type_config' => Array ( 'storage_type' => 'ISO' ),
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
        'location' => Array
        (
            'title' => 'location',
            'storage' => 'location',
            'type' => 'text',
            'widget' => 'text',
        ),
        'categories' => Array
        (
            'title' => 'categories',
            'storage' => array
            (
                'location' => 'configuration',
                'domain'   => 'net.nemein.calendar',
                'name'     => 'categories',
            ),
            'type' => 'select',
            'widget' => 'select',
            'type_config' => Array 
            ( 
                'options'        => array(),
                'allow_other'    => false,
                'allow_multiple' => true,
                'multiple_storagemode' => 'imploded_wrapped',
            ),    
            'widget_config' => Array 
            ( 
                'height' => 3,
            ),        
        ),        
    )
),

