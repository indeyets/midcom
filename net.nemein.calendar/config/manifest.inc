'name' => 'net.nemein.calendar',
'purecode' => false,
'version' => '2.0.0beta7',
'state' => 'beta',
'privileges' => array (),
'class_definitions' => array
(
    'midcom_dba_classes.inc',
),
'package.xml' => array
(
    'summary' => 'Event calendar',
    'description' => 'Event calendar',
    'maintainers' => array
    (
        'bergie' => array
        (
            'name' => 'Henri Bergius',
            'email' => 'henri.bergius@iki.fi',
            'role' => 'lead',
        ),
        'adrenalin' => array
        (
            'name' => 'Arttu Manninen',
            'email' => 'arttu@kaktus.cc',
            'role' => 'developer',
        ),
    ),
    'dependencies' => array
    (
        'net.nemein.repeathandler' => array (),
        'org.openpsa.calendarwidget' => array
        (
            'min' => '2.1.6',
        ),
        'org.routamc.positioning' => array (),
        'de.bitfolge.feedcreator' => array(),
        'midcom.helper.dm2config' => array (),
        'Calendar' => array
        (
            'channel' => 'pear.php.net',
        ),
        'midcom' => array (),
    ),
),
