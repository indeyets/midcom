'config' => array (
    'name'        => 'config',
    'description' => 'Default Configuration Schema', /* This is a topic */
    'locktimeout' => 0, /* Don't lock this, we always display the edit view */
    'fields'      => array
    (
        'checkout_backend' => array
        (
            'description' => 'checkout backend module',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nemein.shoppingcart',
            'config_key' => 'checkout_backend',
            'widget' => 'select',
            'widget_select_choices' => Array
            (
                '' => 'default setting',
                'email' => 'simple email handler',
            ),
            'start_fieldgroup' => array
            (
                'title' => 'order handling settings'
            ),
        ),
        'backend_email_sendto' => array
        (
            'description' => 'send order emails to',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nemein.shoppingcart',
            'config_key' => 'backend_email_sendto',
            'widget' => 'text',
            'validation' => 'email',
        ),
        'backend_email_subject' => array
        (
            'description' => 'subject to order emails with',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nemein.shoppingcart',
            'config_key' => 'backend_email_subject',
            'widget' => 'text',
            'end_fieldgroup' => '',
        ),
        'schemadb' => array
        (
            'description' => 'schema database',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nemein.personnel',
            'config_key' => 'schemadb',
            'start_fieldgroup' => array
            (
                'title' => 'advanced schema and data settings'
            ),
            'end_fieldgroup' => '',
        ),
    ),
),
