'default' => array
(
    'description' => 'comment',
    'fields'      => array
    (
        'subject' => array
        (
            'title' => 'subject',
            'storage' => 'subject',
            'required' => false,
            'type' => 'text',
            'widget' => 'text',
            'index_method' => 'title',
        ),        
        'sendername' => array
        (
            // COMPONENT-REQUIRED
            'title' => 'sender',
            'storage' => 'sendername',
            'required' => true,
            'type' => 'text',
            'widget' => 'text',
            'index_method' => 'author',
        ),
        'senderemail' => array
        (
            'title' => 'sender email',
            'storage' => 'senderemail',
            'hidden' => true,
            'type' => 'text',
            'widget' => 'text',
            'index_method' => 'noindex',
        ),
        'content' => array
        (
            // COMPONENT-REQUIRED
            'title' => 'content',
            'storage' => 'content',
            'required' => true,
            'type' => 'text',
            'type_config' => array
            ( 
                'output_mode' => 'markdown' 
            ),
            'widget' => 'markdown',
            'widget_config' => Array
            (
                'height' => 24,
            ),
        ),
    )
),
'email' => array
(
    'description' => 'emailed comment',
    'fields'      => array
    (
        'subject' => array
        (
            'title' => 'subject',
            'storage' => 'subject',
            'required' => false,
            'type' => 'text',
            'widget' => 'text',
            'index_method' => 'title',
        ),        
        'sendername' => array
        (
            // COMPONENT-REQUIRED
            'title' => 'sender',
            'storage' => 'sendername',
            'required' => true,
            'type' => 'text',
            'widget' => 'text',
            'index_method' => 'author',
        ),
        'senderemail' => array
        (
            'title' => 'sender email',
            'storage' => 'senderemail',
            'hidden' => true,
            'type' => 'text',
            'widget' => 'text',
            'index_method' => 'noindex',
        ),
        'content' => array
        (
            // COMPONENT-REQUIRED
            'title' => 'content',
            'storage' => 'content',
            'required' => true,
            'type' => 'text',
            'type_config' => array
            ( 
                'output_mode' => 'markdown' 
            ),
            'widget' => 'textarea',
            'widget_config' => Array
            (
                'height' => 24,
            ),
        ),
    )
),


