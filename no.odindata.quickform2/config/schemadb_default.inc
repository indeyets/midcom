'default' => array
(
    'description' => 'Form',
    'operations' => Array('save' => 'Send'),
    'fields'      => array
    (
        'name' => Array
        (
            'title' => 'name',
            'storage' => null,
            'type' => 'text',
            'widget' => 'text',
        ),
        'email' => Array
        (
            // COMPONENT-REQUIRED - > if a recpit is needed
            'title' => 'email',
            'storage' => null,
            'required' => true,
            'type' => 'text',
            'widget' => 'text',
            'validation' => Array
            (
                'type' => 'email',
                'message' => 'E-Mails do not match.'
            ),
        ),
        'hidden'  => Array 
        (  
            'title'   => 'Hidden value',
            'storage' => null,
            'required' => true,
            'type' => 'text',
            'widget' => 'hidden',


        ),
        'comment' => Array
        (
            'title' => 'Comment',
            'storage' => null,
            'required' => false,
            'type' => 'text',
            'type_config' => Array ( 'output_mode' => 'markdown' ),
            'widget' => 'textarea',
            
        ),

    )
), // default

