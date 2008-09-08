'name' => 'org.openpsa.queries',
'icon' => 'org.openpsa.queries/folder-saved-search.png',
'purecode' => true,
'privileges' => array(),
'class_definitions' => array
(
    'midcom_dba_classes.inc',
),
'version' => '1.1.0beta1',
'state' => 'beta',
'package.xml' => array
(
    'summary' => 'Library for stored queries',
    'description' => 'Library for handling stored QB queries',
    'maintainers' => array
    (
        'rambo' => array
        (
            'name' => 'Eero af Heurlin',
            'email' => 'eero.afheurlin@iki.fi',
            'role' => 'lead',
        ),
    ),
    'dependencies' => array
    (
        'midcom' => array(),
    ),
),
