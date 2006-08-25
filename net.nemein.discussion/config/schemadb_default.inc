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
            'index_method' => 'title',
        ),        
        'sendername' => Array
        (
            // COMPONENT-REQUIRED
            'title' => 'sender',
            'storage' => 'sendername',
            'required' => true,
            'type' => 'text',
            'widget' => 'text',
            'index_method' => 'author',
        ),
        'senderemail' => Array
        (
            'title' => 'sender email',
            'storage' => 'senderemail',
            'hidden' => true,
            'type' => 'text',
            'widget' => 'text',
            'index_method' => 'noindex',
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

