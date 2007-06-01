'default' => array
(
    'description'   => 'person',
    'l10n_db' => 'org.openpsa.contacts',
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
        'notes' => array
        (
            'title' => 'notes',
            'storage' => 'extra',
            'type' => 'text',
            'type_config' => array
            (
                'output_mode' => 'markdown'
            ),
            'widget' => 'textarea',
        ),
    )
),
'employee' => array
(
    'description'   => 'employee',
    'l10n_db' => 'org.openpsa.contacts',
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
        'homepage' => array
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
        'weekly_workhours' => array
        (
            'title'    => 'weekly workhours',
            'storage' => array
            (
                'location' => 'configuration',
                'domain'   => 'org.openpsa.reports.projects',
                'name'     => 'weekly_workhours',
            ),
            'type'     => 'number',
            'widget'   => 'text',
        ),
        'competence' => Array
        (
            'title' => 'competence areas',
            'storage' => null,
            'type' => 'tags',
            'widget' => 'text',
        ),
        'notes' => array
        (
            'title' => 'notes',
            'storage' => 'extra',
            'type' => 'text',
            'type_config' => array
            (
                'output_mode' => 'markdown'
            ),
            'widget' => 'textarea',
        ),
    )
),