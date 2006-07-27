'event' => Array
(
    'description' => 'Registration Event',
    'fields' => Array
    (
        'name' => Array
        (
            // COMPONENT-REQUIRED
            'title' => 'URL Name',
            'storage' => 'extra',
            'required' => true,
            'type' => 'text',
            'widget' => 'text',
        ),
        'title' => Array
        (
            // COMPONENT-REQUIRED
            'title' => 'Event Title',
            'storage' => 'title',
            'required' => true,
            'type' => 'text',
            'widget' => 'text',
        ),

        'description' => Array
        (
            'title' => 'Event Description',
            'storage' => 'description',
            'required' => true,
            'type' => 'text',
            'type_config' => Array ( 'output_mode' => 'html' ),
            'widget' => 'tinymce',
        ),

        'start' => Array
        (
            // COMPONENT-REQUIRED
            'title' => 'Start of Event',
            'storage' => 'start',
            'type' => 'date',
            'type_config' => Array ( 'storage_type' => 'UNIXTIME' ),
            'widget' => 'jsdate',
        ),
        'end' => Array
        (
            // COMPONENT-REQUIRED
            'title' => 'End of Event',
            'storage' => 'end',
            'type' => 'date',
            'type_config' => Array ( 'storage_type' => 'UNIXTIME' ),
            'widget' => 'jsdate',
        ),

        'open_registration' => Array
        (
            // COMPONENT-REQUIRED
            'title' => 'Open registration for event at',
            'storage' => 'parameter',
            'type' => 'date',
            'widget' => 'jsdate',
        ),
        'close_registration' => Array
        (
            // COMPONENT-REQUIRED
            'title' => 'Close registration for event at',
            'storage' => 'parameter',
            'type' => 'date',
            'widget' => 'jsdate',
        ),
        'additional_questions' => Array
        (
            // COMPONENT-REQUIRED
            'title' => 'Schema to use for additional questions',
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
            'title' => 'Override global notification E-Mail; leave empty to use the global default',
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
    'fields' => Array
    (
        'p_firstname' => Array
        (
            'title' => 'First Name',
            'storage' => 'firstname',
            'required' => true,
            'type' => 'text',
            'widget' => 'text',
        ),
        'p_lastname' => Array
        (
            'title' => 'Last Name',
            'storage' => 'lastname',
            'required' => true,
            'type' => 'text',
            'widget' => 'text',
        ),
        'p_email' => Array
        (
            // COMPONENT-REQUIRED
            'title' => 'E-Mail Address',
            'storage' => 'email',
            'required' => true,
            'type' => 'text',
            'widget' => 'text',
            'validation' => 'email',
        ),


        /* Hidden fields required for component operation */
        'p_name' => Array
        (
            'title' => 'Full Name',
            'storage' => 'name',
            'hidden' => true,
            'type' => 'text',
            'widget' => 'text',
        ),
        'p_rname' => Array
        (
            'title' => 'Full Name (reversed)',
            'storage' => 'rname',
            'hidden' => true,
            'type' => 'text',
            'widget' => 'text',
        ),
    )
), // end of 'person'

'aq-default' => Array
(
    'description' => 'Default "Additional Questions" schema',
    'fields' => Array
    (
        'aq_notes' => Array
        (
            'title' => 'Notes',
            'storage' => 'extra',
            'type' => 'text',
            'type_config' => Array ( 'output_mode' => 'midgard_f' ),
            'widget' => 'textarea',
        ),

    ),
), // end of 'aq-default'
