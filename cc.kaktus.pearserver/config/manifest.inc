'name' => 'cc.kaktus.pearserver',
'description' => 'PEAR server',
'purecode'    => false,
'version' => '0.0.1',
'state' => 'alpha',
'package.xml' => Array
(
    'summary' => 'PEAR server',
    'description' => 'Handle and serve PEAR packages',
    'maintainers' => Array
    (
        'adrenalin' => array
        (
            'name' => 'Arttu Manninen',
            'email' => 'arttu@kaktus.cc',
            'role' => 'developer',
        ),
    ),
    'dependencies' => array
    (
//        'PHP5' => array (),
        'midcom' => array
        (
//            'version' => '2.8'
        ),
        'midgard' => array
        (
            'version' => '1.8',
        ),
        'midcom.helper.datamanager2' => array (),
    ),
),
