'name' => 'net.nemein.registrations',
'purecode' => false,
'version' => '8.09.0',
'state' => 'stable',
'privileges' => array
(
    'manage' => MIDCOM_PRIVILEGE_DENY,
),
'class_definitions' => array
(
    'midcom_dba_classes.inc',
),
'package.xml' => array
(
    'summary' => 'Event registration tool',
    'description' => 'This component allows you to provide your web site users a tool they can use to register for events you prepare for them.',
    'maintainers' => array
    (
        'torben' => array
        (
            'name' => 'Torben Nehmer',
            'email' => 'torben@nehmer.net',
            'role' => 'lead',
            'active' => 'no',
        ),
        'bergie' => array
        (
            'name' => 'Henri Bergius',
            'email' => 'henri.bergius@iki.fi',
            'role' => 'lead',
            'active' => 'yes',
        ),
        'rambo' => array
        (
            'name' => 'Eero af Heurlin',
            'email' => 'rambo@nemein.com',
            'role' => 'lead',
            'active' => 'yes',
        ),
        'netblade' => array
        (
            'name' => 'Oskari Kokko',
            'email' => 'oskari.kokko@iki.fi',
            'role' => 'lead',
        ),
    ),
    'dependencies' => array
    (
        'midcom.helper.datamanager2' => array(),
        'org.openpsa.mail' => array(),
        'net.nemein.calendar' => array(),
        'midcom' => array(),
    ),
),
