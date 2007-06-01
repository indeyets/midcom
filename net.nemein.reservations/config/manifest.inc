'name' => 'net.nemein.reservations',
'purecode' => false,
'version' => '2.0.5',
'state' => 'stable',
'privileges' =>  array(),
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
        'org.openpsa.calendar' => array
        (
            'min' => '2.0.15',
        ),
        'org.openpsa.calendarwidget' => array(),
        'org.openpsa.contactwidget' => array(),
        'midgard.admin.acl' => array
        (
            'min' => '1.0.3',
        ),
        'net.nemein.repeathandler' => array
        (
            'min' => '1.0.1',
        ),
        'midcom' => array (),
    ),
),
'customdata' => array
(
    'midgard.admin.acl' => array
    (
        'extra_privileges' => array
        (
            'org.openpsa.calendar:reserve',
        ),
    ),
),
