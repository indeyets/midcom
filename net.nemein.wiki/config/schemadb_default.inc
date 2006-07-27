'default' => array
(
    'description' => 'wiki page',
    'fields'      => array
    (
        'title' => Array
        (
            'title' => 'title',
            'storage' => 'title',
            'readonly' => true,
            'type' => 'text',
            'widget' => 'text',
        ),        
        'content' => Array
        (
            // COMPONENT-REQUIRED
            'title' => 'content',
            'storage' => 'content',
            'required' => true,
            'type' => 'text',
            'type_config' => Array ( 
                'output_mode' => 'markdown' 
            ),
            'widget' => 'textarea',
        ),
    )
),