'name' => 'net.nemein.calendar',
'icon' => 'stock-icons/16x16/stock_event.png',
'purecode' => false,
'version' => '8.09.2beta1',
'state' => 'stable',
'privileges' => array(),
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
        'net.nemein.repeathandler' => array(),
        'org.openpsa.calendarwidget' => array(),
        'org.routamc.positioning' => array(),
        'de.bitfolge.feedcreator' => array(),
        'Calendar' => array
        (
            'channel' => 'pear.php.net',
        ),
        'midcom' => array(),
    ),
),
