
'style' => Array
(
    'description' => 'Midcom Style',
    'fields' => Array
    (
        'name' => Array
        (
            'title' => 'Name',
            'description' => '',
            'helptext' => '',

            'storage' => 'name',
            'required' => true,
            'type' => 'text',
            'widget' => 'text',
        ),
        'id' => Array
        (
            'title' => 'ID: ',
            'description' => ' ',
            'helptext' => ' ',
            'storage' => 'id',
            'required' => false,
            'hidden' => false,
            'widget' => 'text',
            'type' => 'number',
            'readonly' => true,
        ),  
        'up' => Array
        (
            'title' => 'Parent style',
            'description' => '',
            'helptext' => '',
            'storage' => 'up',
            'required' => false,
            'hidden' => false,
            'widget' => 'select',
            'type' => 'select',
            'type_config' => Array
            (
                'options' => null,
                'option_callback' => 'midcom_admin_styleeditor_callbacks_stylelist',
                'allow_multiple' => false,
                'allow_other' => false,
            ),
        ),   
        /*     
        'shared' => Array
        (
            'title' => 'shared style',
            'description' => 'Tip this off if you want to share the style with others.',
            'helptext' => '',
            'storage' => Array
                (
                    'location' => 'parameter',
                    'domain' => 'display',
                ),
            'type' => 'boolean',
            'type_config' => Array
            (
            // note these options are not in use!!!
                'options' => array ("YES" => "Yes", "NO" => "NO"),
            ),
            'widget' => 'checkbox',
    ),*/
   
    ), // fields
),
