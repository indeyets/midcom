'default' => array
(
    'description' => 'downloadable release',
    'fields'      => array
    (
        'name' => Array
        (
            'title'    => 'url name',
            'storage'  => 'name',
            'readonly' => !$_MIDGARD['admin'],
            'type'     => 'text',
            'widget'   => 'text',
        ),
        'release' => Array
        (
            'title'    => 'release name',
            'storage'  => 'title',
            'required' => true,
            'type'     => 'text',
            'widget'   => 'text',
        ),
        'content' => Array
        (
            // COMPONENT-REQUIRED
            'title' => 'content',
            'storage' => 'content',
            'type' => 'text',
            'type_config' => Array 
            ( 
                'output_mode' => 'html' 
            ),
            'widget' => 'tinymce',
        ),
        'download' => Array
        (
            'title'    => 'downloadable files',
            'storage'  => null,
            'type'     => 'blobs',
            'widget'   => 'downloads',
        ),
    )
),