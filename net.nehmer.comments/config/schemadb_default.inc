'comment' => Array
(
    'description' => 'default schema',
    'fields' => Array
    (
        'author' => Array
        (
            'title' => 'author',
            'description' => '',
            'helptext' => '',

            'storage' => 'author',
            'required' => true,
            'type' => 'text',
            'widget' => 'text',
        ),

        'title' => Array
        (
            'title' => 'title',
            'description' => '',
            'helptext' => '',

            'storage' => 'title',
            'required' => true,
            'type' => 'text',
            'widget' => 'text',
        ),
        'content' => Array
        (
            'title' => 'content',
            'description' => '',
            'helptext' => '',

            'required' => 'true',
            'storage' => 'content',
            'type' => 'text',
            'type_config' => Array ( 'output_mode' => 'markdown' ),
            'widget' => 'textarea',
        ),
    ),
),
