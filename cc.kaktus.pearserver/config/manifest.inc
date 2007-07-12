'name' => 'cc.kaktus.pearserver',
'description' => 'PEAR server',
'purecode'    => false,
'version' => '0.0.1',
'state' => 'alpha',
/* Check the ACL's in some point
'privileges' => array
(
    'upload' => MIDCOM_PRIVILEGE_ALLOW,
),
/* */
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
            'role' => 'lead',
        ),
    ),
    'dependencies' => array
    (
//        'PHP5' => array (),
        'midcom' => array
        (
            'version' => '2.8.0beta'
        ),
        'midgard' => array
        (
            'version' => '1.8',
        ),
        'midcom.helper.datamanager2' => array (),
    ),
),
