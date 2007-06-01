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
        'street' => array 
        (
            'title'       => 'street address',
            'type'        => 'text',
            'widget'      => 'text',
            'storage'     => 'street'
        ),
        'street' => array 
        (
            'title'       => 'street address',
            'type'        => 'text',
            'widget'      => 'text',
            'storage'     => 'street'
        ),
        'postcode' => array 
        (
            'title'       => 'postal code',
            'type'        => 'text',
            'widget'      => 'text',
            'storage'     => 'postcode'
        ),
        'city' => array 
        (
            'title'       => 'city',
            'type'        => 'text',
            'widget'      => 'text',
            'storage'     => 'city'
        ),
        'country' => array 
        (
            'title'       => 'country',
            'type'        => 'text',
            'widget'      => 'text',
            'storage'     => 'country'
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
