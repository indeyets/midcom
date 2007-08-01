'account' => Array
(
    'description' => 'person',
    'l10n_db' => 'org.maemo.calendar',
    'fields' => Array
    (
        'firstname' => Array
        (
            'title' => 'firstname',
            'description' => '',
            'helptext' => '',

            'storage' => 'firstname',
            'required' => true,
            'type' => 'text',
            'type_config' => Array
            (
                'maxlength' => 50,
            ),
            'widget' => 'text',
            'customdata' => Array
            (
                'visible_mode' => 'always',
            ),
        ),
        'lastname' => Array
        (
            'title' => 'lastname',
            'description' => '',
            'helptext' => '',

            'storage' => 'lastname',
            'required' => true,
            'type' => 'text',
            'type_config' => Array
            (
                'maxlength' => 50,
            ),
            'widget' => 'text',
            'customdata' => Array
            (
                'visible_mode' => 'always',
            ),
        ),
        'person_homepage' => array
        (
            'title'    => 'homepage',
            'storage'  => 'homepage',
            'type'     => 'text',
            'widget'   => 'text',
            'customdata' => Array
            (
                'visible_mode' => 'user',
            ),
        ),
        'email' => array
        (
            'title'    => 'email',
            'storage'  => 'email',
            'type'     => 'text',
            'widget'   => 'text',
            'validation' => 'email',
            'customdata' => Array
            (
                'visible_mode' => 'user',
            ),
        ),
        'handphone' => array
        (
            'title'    => 'mobile phone',
            'storage'  => 'handphone',
            'type'     => 'text',
            'widget'   => 'text',
            'customdata' => Array
            (
                'visible_mode' => 'user',
            ),
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
            'customdata' => Array
            (
                'visible_mode' => 'user',
            ),
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
            'customdata' => Array
            (
                'visible_mode' => 'user',
            ),
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
            'customdata' => Array
            (
                'visible_mode' => 'always',
            ),
        ),
        'birthdate' => Array
        (
            'title' => 'birthdate',
            'description' => '',
            'helptext' => '',

            'storage' => 'birthdate',
            'type' => 'date',
            'widget' => 'simpledate',
            'widget_config' => Array
            (
                'format' => 'dmY',
                'minyear' => 1900,
                'maxyear' => 2010,
            ),
            'customdata' => Array
            (
                'visible_mode' => 'never',
            ),
        ),
    ),
),