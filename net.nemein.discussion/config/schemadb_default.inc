'default' => array
(
    'description' => 'comment',
    'fields'      => array
    (
        'subject' => Array
        (
            'title' => 'subject',
            'storage' => 'subject',
            'required' => false,
            'type' => 'text',
            'widget' => 'text',
        ),        
        'sendername' => Array
        (
            // COMPONENT-REQUIRED
            'title' => 'sender',
            'storage' => 'sendername',
            'required' => true,
            'type' => 'text',
            'widget' => 'text',
        ),
        'senderemail' => Array
        (
            'title' => 'sender email',
            'storage' => 'senderemail',
            'hidden' => true,
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
            'type_config' => Array ( 'output_mode' => 'html' ),
            'widget' => 'textarea',
        ),
    )
),

