// Exhibition page
'exhibition' => array
(
    'description' => 'exhibition',
    'fields' => array
    (
        'extra' => array
        (
            'title' => 'name',
            'storage' => 'extra',
            'type' => 'text',
            'widget' => 'text',
            'hidden' => !$_MIDCOM->auth->admin,
        ),
        'title' => array
        (
            'title' => 'artist',
            'storage' => 'title',
            'type' => 'text',
            'widget' => 'text',
            'required' => true,
        ),
        'exhibition' => array
        (
            'title' => 'exhibition name',
            'storage' => 'parameter',
            'type' => 'text',
            'widget' => 'text',
        ),
        'start' => array
        (
            'title' => 'start',
            'storage' => 'start',
            'type' => 'date',
            'type_config' => array
            (
                'storage_type' => 'UNIXTIME',
            ),
            'widget' => 'jsdate',
            'widget_config' => array
            (
                'show_time' => false,
            ),
            'required' => true,
        ),
        'end' => array
        (
            'title' => 'end',
            'storage' => 'end',
            'type' => 'date',
            'type_config' => array
            (
                'storage_type' => 'UNIXTIME',
            ),
            'widget' => 'jsdate',
            'widget_config' => array
            (
                'show_time' => false,
            ),
            'required' => true,
        ),
        
        'description' => array
        (
            'title' => 'description',
            'storage' => 'description',
            'type' => 'text',
            'type_config' => array
            (
                'output_mode' => 'html',
            ),
            'widget' => 'tinymce',
        ),
    ),
),

// Exhibition subpage
'subpage' => array
(
    'description' => 'subpage',
    'fields' => array
    (
        'extra' => array
        (
            'title' => 'name',
            'storage' => 'extra',
            'type' => 'text',
            'widget' => 'text',
            'hidden' => true,
        ),
        'title' => array
        (
            'title' => 'name',
            'storage' => 'extra',
            'type' => 'text',
            'widget' => 'text',
            'hidden' => true,
        ),
        'title' => array
        (
            'title' => 'title',
            'storage' => 'title',
            'type' => 'text',
            'widget' => 'text',
            'required' => true,
        ),
        'description' => array
        (
            'title' => 'description',
            'storage' => 'description',
            'type' => 'text',
            'type_config' => array
            (
                'output_mode' => 'html',
            ),
            'widget' => 'tinymce',
        ),
    ),
),

// Attachment
'attachment' => array
(
    'description' => 'attachment',
    'fields' => array
    (
        'extra' => array
        (
            'title' => 'name',
            'storage' => 'extra',
            'type' => 'text',
            'widget' => 'text',
            'hidden' => true,
        ),
        'title' => array
        (
            'title' => 'title',
            'storage' => 'title',
            'type' => 'text',
            'widget' => 'text',
            'required' => true,
        ),
        'image' => array
        (
            'title' => 'picture',
            'storage' => null,
            'type' => 'image',
            'type_config' => array
            (
                'filter_chain' => 'resize(400,350)',
                'auto_thumbnail' => Array(60,60),
            ),
            'widget' => 'image',
        ),
    ),
),