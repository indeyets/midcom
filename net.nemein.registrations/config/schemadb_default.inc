'event' => Array
(
    'description' => 'Registration Event',
    'fields' => Array
    (
        'name' => Array
        (
            // COMPONENT-REQUIRED
            'title' => 'name',
            'storage' => 'name',
            'required' => false,
            'type' => 'text',
            'widget' => 'text',
            'hidden' => !($_MIDCOM->auth->admin),
        ),
        'title' => Array
        (
            // COMPONENT-REQUIRED
            'title' => 'title',
            'storage' => 'title',
            'required' => true,
            'type' => 'text',
            'widget' => 'text',
        ),

        'description' => Array
        (
            'title' => 'description',
            'storage' => 'description',
            'required' => true,
            'type' => 'text',
            'type_config' => Array ( 'output_mode' => 'html' ),
            'widget' => 'tinymce',
        ),

        'start' => Array
        (
            // COMPONENT-REQUIRED
            'title' => 'start date',
            'storage' => 'start',
            'type' => 'date',
            'type_config' => Array ( 'storage_type' => 'ISO' ),
            'widget' => 'jsdate',
        ),
        'end' => Array
        (
            // COMPONENT-REQUIRED
            'title' => 'end date',
            'storage' => 'end',
            'type' => 'date',
            'type_config' => Array ( 'storage_type' => 'ISO' ),
            'widget' => 'jsdate',
        ),

        'open_registration' => Array
        (
            // COMPONENT-REQUIRED
            'title' => 'open registration at',
            'storage' => 'openregistration',
            'type' => 'date',
            'type_config' => Array ( 'storage_type' => 'ISO' ),
            'widget' => 'jsdate',
        ),
        'close_registration' => Array
        (
            // COMPONENT-REQUIRED
            'title' => 'close registration at',
            'storage' => 'closeregistration',
            'type' => 'date',
            'type_config' => Array ( 'storage_type' => 'ISO' ),
            'widget' => 'jsdate',
        ),
        'additional_questions' => Array
        (
            // COMPONENT-REQUIRED
            'title' => 'additional questions schema',
            'storage' => 'parameter',
            'type' => 'select',
            'required' => true,
            'type_config' => Array
            (
                'option_callback' => 'net_nemein_registrations_aqcallback',
            ),
            'widget' => 'select',
        ),
        'notification_email' => Array
        (
            // COMPONENT-REQUIRED
            'title' => 'override global notification e-mail',
            'storage' => 'parameter',
            'type' => 'text',
            'widget' => 'text',
            'validation' => 'email',
        ),
        'auto_approve' => Array
        (
            'title' => 'auto_approve',
            'storage' => 'parameter',
            'type' => 'boolean',
            'widget' => 'checkbox',
        ),
        'anonymous_registration' => Array
        (
            'title' => 'anonymous_registration',
            'storage' => null,
            'type' => 'privilegeset',
            'type_config' => Array
            (
                'privileges' => Array
                (
                    Array('midgard:create', 'EVERYONE', MIDCOM_PRIVILEGE_ALLOW),
                ),
            ),
            'widget' => 'privilegecheckbox',
        ),
    ),
), // end of 'event'

'person' => Array
(
    'description' => 'Registred Person Record',
    'operations'  => array
    (
        'cancel' => 'cancel',
        'save'   => 'register',
    ),
    'fields' => Array
    (
        'p_firstname' => Array
        (
            'title' => 'first name',
            'storage' => 'firstname',
            'required' => true,
            'type' => 'text',
            'widget' => 'text',
        ),
        'p_lastname' => Array
        (
            'title' => 'last name',
            'storage' => 'lastname',
            'required' => true,
            'type' => 'text',
            'widget' => 'text',
        ),
        'p_email' => Array
        (
            // COMPONENT-REQUIRED
            'title' => 'emailaddress',
            'storage' => 'email',
            'required' => true,
            'type' => 'text',
            'widget' => 'text',
            'validation' => 'email',
        ),


        /* Hidden fields required for component operation */
        'p_name' => Array
        (
            'title' => 'full name',
            'storage' => 'name',
            'hidden' => true,
            'type' => 'text',
            'widget' => 'text',
            'readonly' => true,
        ),
        'p_rname' => Array
        (
            'title' => 'full name reversed',
            'storage' => 'rname',
            'hidden' => true,
            'type' => 'text',
            'widget' => 'text',
            'readonly' => true,
        ),
    )
), // end of 'person'

'aq-default' => Array
(
    'description' => 'default additional questions schema',
    'fields' => Array
    (
        'aq_notes' => Array
        (
            'title' => 'notes',
            'storage' => 'extra',
            'type' => 'text',
            'type_config' => Array ( 'output_mode' => 'markdown' ),
            'widget' => 'textarea',
        ),
        'reg_refno' => array
        (
            'title' => 'reference',
            'storage' => 'reference',
            'type' => 'text',
            'widget' => 'text',
            'readonly' => true,
            'hidden' => !($_MIDCOM->auth->admin),
        ),
        'reg_price' => array
        (
            'title' => 'price',
            'storage' => 'price',
            'type' => 'number',
            'type_config' => array('precision' => 2),
            'widget' => 'text',
            'readonly' => true,
            'hidden' => !($_MIDCOM->auth->admin),
        ),
        'reg_paid' => array
        (
            'title' => 'registration paid',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'net.nemein.registrations',
                'name' => 'paid',
            ),
            'type' => 'date',
            'type_config' => array('storage_type' => 'ISO'),
            'widget' => 'jsdate',
            'readonly' => true,
            'hidden' => !($_MIDCOM->auth->admin),
        ),
        'created' => array
        (
            'title' => 'registration date',
            'type' => 'date',
            'storage' => 'metadata',
            'type_config' => array('storage_type' => 'ISO'),
            'widget' => 'jsdate',
            'readonly' => true,
            'hidden' => !($_MIDCOM->auth->admin),
        ),
    ),
), // end of 'aq-default'
