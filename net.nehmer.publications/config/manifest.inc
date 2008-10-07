'name' => 'net.nehmer.publications',
'version' => '8.09.0',
'class_definitions' => array
(
    'midcomdba.inc',
),
'state' => 'stable',
'package.xml' => array
(
    'summary' => 'Publication management system',
    'description' => 'Manages publicaitons in several (yet hard-corded and not DB-normalized) categories',
    'maintainers' => array
    (
        'torben' => array
        (
            'name' => 'Torben Nehmer',
            'email' => 'torben@nehmer.net',
            'role' => 'lead',
            'active' => 'no',
        ),
    ),
    'dependencies' => array
    (
        'midcom.helper.datamanager' => array(),
        'midcom.helper.datamanager2' => array(),
        'midcom' => array(),
    ),
),
