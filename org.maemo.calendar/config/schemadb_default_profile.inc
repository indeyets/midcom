'default' => array
(
    'description'   => 'person',
    'l10n_db' => 'org.maemo.calendar',
    'fields'  => array
    (
        'firstname' => array
        (
            'title'    => 'firstname',
            'storage'  => 'firstname',
            'type'     => 'text',
            'widget'   => 'text',
            'required' => true,
        ),
        'lastname' => array
        (
            'title'    => 'lastname',
            'storage'  => 'lastname',
            'type'     => 'text',
            'widget'   => 'text',
        ),
        'person_homepage' => array
        (
            'title'    => 'homepage',
            'storage'  => 'homepage',
            'type'     => 'text',
            'widget'   => 'text',
        ),
        'email' => array
        (
            'title'    => 'email',
            'storage'  => 'email',
            'type'     => 'text',
            'widget'   => 'text',
            'validation' => 'email',
        ),
        'handphone' => array
        (
            'title'    => 'mobile phone',
            'storage'  => 'handphone',
            'type'     => 'text',
            'widget'   => 'text',
        ),
        'skype' => array
        (
            'title'    => 'skype name',
            'storage' => array
            (
                'location' => 'configuration',
                'domain'   => 'org.openpsa.skype',
                'name'     => 'name',
            ),
            'type'     => 'text',
            'widget'   => 'text',
        ),
        'jid' => array
        (
            'title'    => 'jabber id',
            'storage' => array
            (
                'location' => 'configuration',
                'domain'   => 'org.openpsa.jabber',
                'name'     => 'jid',
            ),
            'type'     => 'text',
            'widget'   => 'text',
            'validation' => 'email',
        ),
        'timezone_identifier' => array
        (
            'title'    => 'timezone',
            'storage' => array
            (
                'location' => 'parameter',
                'domain'   => 'org.maemo.calendar:preferences',
            ),
            'type'     => 'select',
            'type_config' => array
            (
                'option_callback' => 'org_maemo_calendar_callbacks_timezones',
                'allow_multiple' => false,
            ),
            'widget'   => 'select',
        ),   
    )
),