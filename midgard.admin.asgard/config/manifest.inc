'name' => 'midgard.admin.asgard',
'icon' => 'midgard.admin.asgard/asgard2-16.png',
'purecode' => true,
'version' => '8.09.1',
'state' => 'stable',
'privileges' => array
(
    'access' => MIDCOM_PRIVILEGE_DENY,
),
'class_definitions' => array(),
'package.xml' => array
(
    'summary' => 'Next generation admin interface',
    'description' => 'Next generation admin interface',
    'maintainers' => array
    (
        'bergie' => array
        (
            'name' => 'Henri Bergius',
            'email' => 'henri.bergius@iki.fi',
            'role' => 'lead',
        ),
        'rambo' => array
        (
            'name' => 'Eero af Heurlin',
            'email' => 'eero.afheurlin@iki.fi',
            'role' => 'lead',
        ),
        'adrenalin' => array
        (
            'name' => 'Arttu Manninen',
            'email' => 'arttu@kaktus.cc',
            'role' => 'lead',
        ),
        'solt' => array
        (
            'name' => 'Marcin Sołtysiak',
            'email' => 'olga@olga.pl',
            'role' => 'developer',
        ),
        'w_i' => array
        (
            'name' => 'Jerry Jalava',
            'email' => 'jerry.jalava@gmail.com',
            'role' => 'developer',
        ),
    ),
    'dependencies' => array
    (
        'midcom.helper.reflector' => array(),
        'midcom.helper.datamanager2' => array
        (
            'version' => '1.0.4beta43',
        ),
        'midcom.admin.help' => array(),
    ),
),
