'default' => array
(
    'description' => 'Default Publication',
    'fields'      => array
    (
        'title' => Array
        (
            // COMPONENT-REQUIRED
            'title' => 'Title',
            'storage' => 'title',
            'required' => true,
            'type' => 'text',
            'widget' => 'text',
        ),
        'author' => Array
        (
            // COMPONENT-REQUIRED
            'title' => 'author',
            'storage' => 'author',
            'required' => true,
            'type' => 'text',
            'widget' => 'text',
        ),
        'category' => Array
        (
            'title' => 'category',
            'description' => '',
            'helptext' => '',

            'storage' => null,
            'type' => 'mnrelation',
            'type_config' => Array
            (
                'option_callback' => 'net_nehmer_publications_callbacks_categorylister',
                'option_callback_arg' => 1,
                'mapping_class_name' => 'net_nehmer_publications_categorymap',
                'master_fieldname' => 'publication',
                'member_fieldname' => 'category',
            ),
            'widget' => 'select',
        ),


        'abstract' => Array
        (
            // COMPONENT-REQUIRED
            'title' => 'Abstract',
            'storage' => 'abstract',
            'required' => true,
            'type' => 'text',
            'widget' => 'textarea',
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
            'title' => 'image',
            'storage' => null,
            'type' => 'image',
            'type_config' => Array
            (
                'filter_chain' => 'resize(800,600)',
                'auto_thumbnail' => Array(200,200),
            ),
            'widget' => 'image',
        ),
        'downloads' => Array
        (
            'title' => 'downloads',
            'storage' => null,
            'type' => 'blobs',
            'widget' => 'downloads',
        ),


    )
), // default

