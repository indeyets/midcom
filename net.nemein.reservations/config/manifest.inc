'name' => 'net.nemein.reservations',
'purecode' => false,
'version' => '8.09.0',
'state' => 'stable',
'privileges' => array(),
'class_definitions' => array(),
'package.xml' => array
(
    'summary' => 'Resource reservation calendar',
    'description' => 'Allows reserving resources (like meeting rooms)',
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
            'role' => 'developer',
        ),
        'netblade' => array
        (
            'name' => 'Oskari Kokko',
            'email' => 'oskari.kokko@iki.fi',
            'role' => 'developer',
        ),
    ),
    'dependencies' => array
    (
        'midcom.helper.datamanager2' => array(),
        'org.openpsa.calendar' => array(),
        'org.openpsa.calendarwidget' => array(),
        'org.openpsa.contactwidget' => array(),
        'net.nemein.repeathandler' => array(),
        'midcom' => array(),
    ),
),
'customdata' => array
(
    'midgard.admin.asgard.acl' => array
    (
        'extra_privileges' => array
        (
            'org.openpsa.calendar:reserve',
        ),
    ),
),
