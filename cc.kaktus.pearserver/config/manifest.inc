'name' => 'cc.kaktus.pearserver',
'description' => 'PEAR server',
'purecode' => false,
'version' => '0.1.0beta1',
'state' => 'beta',
'package.xml' => array
(
    'summary' => 'PEAR server',
    'description' => 'Handle and serve PEAR packages',
    'maintainers' => array
    (
        'adrenalin' => array
        (
            'name' => 'Arttu Manninen',
            'email' => 'arttu@kaktus.cc',
            'role' => 'lead',
        ),
    ),
    'dependencies' => array
    (
        'midcom' => array
        (
            'version' => '2.8.0beta',
        ),
        'midgard' => array
        (
            'version' => 1.8,
        ),
        'midcom.helper.datamanager2' => array(),
    ),
),
