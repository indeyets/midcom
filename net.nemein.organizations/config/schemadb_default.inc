'group' => array 
(
    'description' => 'group card',
    'fields'      => array 
    (
        'name' => array 
        (
            // COMPONENT-RECOMMENDED
            'title'       => 'url name',
            'storage'     => 'name',
            'type'        => 'text',
            'widget'      => 'text',
            'index_method' => 'noindex',
            'write_privilege' => array
            (
                'privilege' => 'midcom:urlname',
            ),
        ),
        'official' => array 
        (
            'title'       => 'official name',
            'type'        => 'text',
            'widget'      => 'text',
            'storage'     => 'official',
            'index_method' => 'title',
        ),
        'email' => array 
        (
            'title'       => 'email',
            'type'        => 'text',
            'widget'      => 'text',
            'storage'     => 'email',
            'validation'  => 'email',
        ),
        'location' => array 
        (
            'title'       => 'location',
            'type'        => 'position',
            'widget'      => 'position',
            'storage'     => null
        ),
        'description' => array 
        (
            'title'       => 'description',
            'type'        => 'text',
            'type_config' => array 
            ( 
                'output_mode' => 'html' 
            ),
            'widget'      => 'tinymce',
            'storage'     => 'extra',
        ),
    )

)
