// This is for a midcom_db_person object
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
            'index_method' => 'noindex',
        ),
        'name' => array 
        (
            // COMPONENT-RECOMMENDED
            'title'       => 'full name',
            'storage'     => 'name',
            'hidden'      => true,
            'type'        => 'text',
            'widget'      => 'text',
            'index_method' => 'title',
        ),
        'rname' => array 
        (
            // COMPONENT-RECOMMENDED
            'title'       => 'full name (reversed)',
            'storage'     => 'rname',
            'hidden'      => true,
            'type'        => 'text',
            'widget'      => 'text',
            'index_method' => 'noindex',
        ),
        
        
        'firstname' => array 
        (
            'title'       => 'firstname',
            'type'        => 'text',
            'widget'      => 'text',
            'storage'     => 'firstname',
            'index_method' => 'noindex',
        ),
        'lastname' => array 
        (
            'title'       => 'lastname',
            'type'        => 'text',
            'widget'      => 'text',
            'storage'     => 'lastname',
            'index_method' => 'noindex',
        ),
        'title' => array 
        (
            'title'       => 'job title',
            'type'        => 'text',
            'widget'      => 'text',
            'storage'     => 'parameter',
            'index_method' => 'text',
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
        ),
    ),
),

// This is for a midcom_db_group object
'group' => array
(
    'description' => 'group card',
    'fields'      => array
    (
        'official' => array
        (
            // COMPONENT-RECOMMENDED
            'title'       => 'name',
            'storage'     => 'name',
            'type'        => 'text',
            'widget'      => 'text',
            'required'    => true,
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
            'storage'     => 'street',
        ),
        'postcode' => array
        (
            'title'       => 'postal code',
            'type'        => 'text',
            'widget'      => 'text',
            'storage'     => 'postcode',
        ),
        'city' => array
        (
            'title'       => 'city',
            'type'        => 'text',
            'widget'      => 'text',
            'storage'     => 'city',
        ),
        'description' => array
        (
            'title'       => 'description',
            'type'        => 'text',
            'type_config' => array
            (
                'output_mode' => 'html',
            ),
            'widget'      => 'tinymce',
            'storage'     => 'extra',
        ),
    ),
),