'default' => Array
(
    'description' => 'default schema',
    'fields' => Array
    (
        'category' => Array
        (
            'title' => 'category',
            'description' => '',
            'helptext' => '',

            'storage' => 'category',
            'required' => true,
            'type' => 'select',
            'type_config' => Array
            (
                'option_callback' => 'net_nehmer_marketplace_callbacks_categorylister',
            ),
            'widget' => 'select',
        ),
        'published' => Array
        (
            'title' => 'published',
            'description' => '',
            'helptext' => '',

            'storage' => 'published',
            'hidden' => true,
            'type' => 'date',
            'type_config' => Array
            (
                'storage_type' => 'UNIXTIME',
            ),
            'widget' => 'simpledate',
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
        'abstract' => Array
        (
            'title' => 'abstract',
            'description' => '',
            'helptext' => '',

            'storage' => 'abstract',
            'type' => 'text',
            'widget' => 'textarea',
        ),
        'description' => Array
        (
            'title' => 'description',
            'description' => '',
            'helptext' => '',

            'storage' => 'description',
            'type' => 'text',
            'widget' => 'textarea',
        ),
        'price' => Array
        (
            'title' => 'price',
            'description' => '',
            'helptext' => '',

            'storage' => 'price',
            'type' => 'number',
            'type_config' => Array ('precision' => 2),
            'widget' => 'text',
        ),

        'firstname' => Array
        (
            'title' => 'firstname',
            'description' => '',
            'helptext' => '',

            'storage' => 'firstname',
            'required' => true,
            'type' => 'text',
            'widget' => 'text',
        ),
        'lastname' => Array
        (
            'title' => 'lastname',
            'description' => '',
            'helptext' => '',

            'storage' => 'lastname',
            'required' => true,
            'type' => 'text',
            'widget' => 'text',
        ),
        'company' => Array
        (
            'title' => 'company',
            'description' => '',
            'helptext' => '',

            'storage' => 'company',
            'type' => 'text',
            'widget' => 'text',
        ),
        'address' => Array
        (
            'title' => 'street',
            'description' => '',
            'helptext' => '',

            'storage' => 'address',
            'type' => 'text',
            'widget' => 'text',
        ),
        'postcode' => Array
        (
            'title' => 'postcode',
            'description' => '',
            'helptext' => '',

            'storage' => 'postcode',
            'type' => 'text',
            'widget' => 'text',
        ),
        'city' => Array
        (
            'title' => 'city',
            'description' => '',
            'helptext' => '',

            'storage' => 'city',
            'type' => 'text',
            'widget' => 'text',
        ),
        'homepage' => Array
        (
            'title' => 'homepage',
            'description' => '',
            'helptext' => '',

            'storage' => 'homepage',
            'type' => 'text',
            'widget' => 'text',
        ),
        'email' => Array
        (
            'title' => 'email',
            'description' => '',
            'helptext' => '',

            'storage' => 'email',
            'type' => 'text',
            'widget' => 'text',
        ),
        'workphone' => Array
        (
            'title' => 'workphone',
            'description' => '',
            'helptext' => '',

            'storage' => 'workphone',
            'type' => 'text',
            'widget' => 'text',
        ),
        'mobilephone' => Array
        (
            'title' => 'mobilephone',
            'description' => '',
            'helptext' => '',

            'storage' => 'mobilephone',
            'type' => 'text',
            'widget' => 'text',
        ),
        'image_logo' => Array
        (
            'title' => 'Logo',
            'description' => '',
            'helptext' => '',

            'storage' => null,
            'type' => 'image',
            'type_config' => Array
            (
                'filter_chain' => 'resize(360,360);',
                'auto_thumbnail' => Array(60,60),
            ),
            'widget' => 'image',
        ),
        'image_image1' => Array
        (
            'title' => 'image',
            'description' => '',
            'helptext' => '',

            'storage' => null,
            'type' => 'image',
            'type_config' => Array
            (
                'filter_chain' => 'resize(360,360);',
            ),
            'widget' => 'image',
        ),
        'image_image2' => Array
        (
            'title' => 'image',
            'description' => '',
            'helptext' => '',

            'storage' => null,
            'type' => 'image',
            'type_config' => Array
            (
                'filter_chain' => 'resize(360,360);',
            ),
            'widget' => 'image',
        ),
        'image_image3' => Array
        (
            'title' => 'image',
            'description' => '',
            'helptext' => '',

            'storage' => null,
            'type' => 'image',
            'type_config' => Array
            (
                'filter_chain' => 'resize(360,360);',
            ),
            'widget' => 'image',
        ),
    ),
),
