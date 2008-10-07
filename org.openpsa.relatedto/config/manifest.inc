'name' => 'org.openpsa.relatedto',
'icon' => 'stock-icons/16x16/hidden.png',
'purecode' => true,
'privileges' => array(),
'class_definitions' => array
(
    'midcom_dba_classes.inc',
),
'version' => '8.09.0',
'state' => 'stable',
'package.xml' => array
(
    'summary' => 'Library for handling "related to" links',
    'maintainers' => array
    (
        'rambo' => array
        (
            'name' => 'Eero af Heurlin',
            'email' => 'eero.afheurlin@iki.fi',
            'role' => 'lead',
        ),
        'flack' => array
        (
            'name' => 'Andreas Flack',
            'email' => 'flack@contentcontrol-berlin.de',
            'role' => 'developer',
        ),
        'bergie' => array
        (
            'name' => 'Henri Bergius',
            'email' => 'henri.bergius@iki.fi',
            'role' => 'developer',
            'active' => 'no',
        ),
    ),
    'dependencies' => array
    (
        'org.openpsa.helpers' => array(),
        'org.openpsa.contactwidget' => array(),
        'JavaScript_Scriptaculous' => array
        (
            'channel' => 'pearified.com',
        ),
        'midcom' => array(),
    ),
),
