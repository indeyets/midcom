'default' => array
(
    'description' => 'Default Article',
    'fields'      => array
    (
        'name' => Array
        (
            // COMPONENT-REQUIRED
            'title' => 'URL Name',
            'storage' => 'name',
            'type' => 'text',
            'widget' => 'text',
        ),
        'title' => Array
        (
            // COMPONENT-REQUIRED
            'title' => 'Title',
            'storage' => 'title',
            'required' => true,
            'type' => 'text',
            'widget' => 'text',
        ),
        'content' => Array
        (
            // COMPONENT-REQUIRED
            'title' => 'Content',
            'storage' => 'content',
            'required' => true,
            'type' => 'text',
            'type_config' => Array ( 'output_mode' => 'html' ),
            'widget' => 'tinymce',
        ),


        'image' => Array
        (
            'title' => 'Image',
            'storage' => null,
            'type' => 'image',
            'type_config' => Array
            (
                'filter_chain' => 'resize(800,600)',
                'auto_thumbnail' => Array(200,200),
            ),
            'widget' => 'image',
        ),

    )
), // default

