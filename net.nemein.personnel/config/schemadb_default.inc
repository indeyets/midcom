'person' => array 
(
    'description' => 'person card',
    'fields'      => array 
    (
        'username' => array 
        (
            // COMPONENT-REQUIRED
            'title'       => 'username',
            'storage'     => 'username',
            'hidden'      => true,
            'type'        => 'text',
            'widget'      => 'text',
        ),
        'name' => array 
        (
            // COMPONENT-RECOMMENDED
            'title'       => 'full name',
            'storage'     => 'name',
            'hidden'      => true,
            'type'        => 'text',
            'widget'      => 'text',
        ),
        'rname' => array 
        (
            // COMPONENT-RECOMMENDED
            'title'       => 'full name (reversed)',
            'storage'     => 'rname',
            'hidden'      => true,
            'type'        => 'text',
            'widget'      => 'text',
        ),
        
        
        'firstname' => array 
        (
            'title'       => 'firstname',
            'type'        => 'text',
            'widget'      => 'text',
            'storage'     => 'firstname',
        ),
        'lastname' => array 
        (
            'title'       => 'lastname',
            'type'        => 'text',
            'widget'      => 'text',
            'storage'     => 'lastname'
        ),
        'title' => array 
        (
            'title'       => 'job title',
            'type'        => 'text',
            'widget'      => 'text',
            'storage'     => 'parameter'
        ),
        'email' => array 
        (
            'title'       => 'email',
            'type'        => 'text',
            'widget'      => 'text',
            'storage'     => 'email',
            'validation'  => 'email',
        ),
        'workphone' => array 
        (
            'title'       => 'work phone',
            'type'        => 'text',
            'widget'      => 'text',
            'storage'     => 'workphone'
        ),
        'description' => array 
        (
            'title'       => 'description',
            'type'        => 'text',
            'type_config' => Array ( 'output_mode' => 'html' ),
            'widget'      => 'tinymce',
            'storage'     => 'extra',
        ),
        'image' => array 
        (
            'title'       => 'image',
            'storage'     => null,
            'type'        => 'image',
            'type_config' => Array
            (
                'filter_chain' => 'resize(800,600)',
                'auto_thumbnail' => Array(150,150),
            ),
            'widget'      => 'image',
        )
    )

)
