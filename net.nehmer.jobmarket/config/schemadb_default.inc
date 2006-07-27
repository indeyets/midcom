'jobs_example' => Array
(
    'description' => 'jobs example',
    'fields' => Array
    (
        'sector' => Array
        (
            'title' => 'sector',
            'description' => '',
            'helptext' => '',

            'storage' => 'sector',
            'required' => true,
            'type' => 'select',
            'type_config' => Array
            (
                'option_callback' => 'net_nehmer_jobmarket_callbacks_configlists',
                'option_callback_arg' => 'sector',
            ),
            'widget' => 'select',
        ),
        'location' => Array
        (
            'title' => 'location',
            'description' => '',
            'helptext' => '',

            'storage' => 'location',
            'required' => true,
            'type' => 'select',
            'type_config' => Array
            (
                'option_callback' => 'net_nehmer_jobmarket_callbacks_configlists',
                'option_callback_arg' => 'location',
            ),
            'widget' => 'select',
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

        'contact' => Array
        (
            'title' => 'contact',
            'description' => '',
            'helptext' => '',

            'storage' => 'contact',
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
    ),
),
