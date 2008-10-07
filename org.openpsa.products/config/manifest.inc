'name' => 'org.openpsa.products',
'icon' => 'stock-icons/16x16/package.png',
'purecode' => false,
'version' => '8.09.0',
'state' => 'stable',
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
            'role' => 'lead',
        ),
        'xfade' => array
        (
            'name' => 'Niels Breet',
            'email' => 'nbreet@gmail.com',
            'role' => 'lead',
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
