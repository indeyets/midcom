'name' => 'org.openpsa.products',
'purecode' => false,
'version' => '1.2.0beta1',
'state' => 'beta',
'privileges' => array(),
'class_definitions' => array
(
    'midcom_dba_classes.inc',
),
'package.xml' => array
(
    'summary' => 'Product database',
    'description' => 'Product database',
    'maintainers' => array
    (
        'bergie' => array
        (
            'name' => 'Henri Bergius',
            'email' => 'henri.bergius@iki.fi',
            'role' => 'lead',
        ),
        'netblade' => array
        (
            'name' => 'Oskari Kokko',
            'email' => 'oskari.kokko@nemein.com',
            'role' => 'developer',
        ),
        'xfade' => array
        (
            'name' => 'Niels Breet',
            'email' => 'nbreet@gmail.com',
            'role' => 'developer',
        ),
    ),
    'dependencies' => array
    (
        'de.bitfolge.feedcreator' => array(),
        'midcom.helper.datamanager2' => array(),
        'org.openpsa.qbpager' => array(),
        'midcom' => array(),
    ),
),
