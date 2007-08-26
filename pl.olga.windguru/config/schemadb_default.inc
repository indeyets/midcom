'default' => array
(
    'description' => 'spot',
    'fields'      => array
    (

        'title' => Array
        (
            // COMPONENT-REQUIRED
            'title' => 'title',
            'storage' => 'title',
            'required' => true,
            'type' => 'text',
            'widget' => 'text',
        ),
		'spotid' => Array
        (
            // COMPONENT-REQUIRED
            'title' => 'spot id',
            'storage' => 'extra1',
            'required' => true,
            'type' => 'text',
            'widget' => 'text',
        ),
		'key' => Array
        (
            // COMPONENT-REQUIRED
            'title' => 'code',
            'storage' => 'extra2',
            'required' => true,
            'type' => 'text',
            'widget' => 'text',
        ),
        'comment' => Array
        (
            // COMPONENT-REQUIRED
            'title' => 'comment',
            'storage' => 'content',
            'required' => true,
            'type' => 'text',
			'type_config' => array
			(
				'output_mode' => 'html',
			),
            'widget' => 'tinymce',
        ),



    )
), // default

