'name' => 'org.maemo.gforgeprofileupdater',
'purecode' => true,
'version' => '0.1.0beta1',
'state' => 'beta',
'privileges' => array(),
'class_definitions' => array(),
'package.xml' => array
(
    'summary' => 'MidCOM module org.maemo.gforgeprofileupdater',
    'description' => 'MidCOM module org.maemo.gforgeprofileupdater',
    'maintainers' => array
    (
        'rambo' => array
        (
            'name' => 'Eero af Heurlin',
            'email' => 'eero.afheurlin@nemein.com',
            'role' => 'lead',
        ),
    ),
    'dependencies' => array
    (
        'midcom.helper.datamanager2' => array(),
        'soap' => array
        (
            'channel' => 'pear.php.net',
        ),
    ),
),
'watches' => array
(
    array
    (
        'classes' => array
        (
            'midcom_baseclasses_database_person',
            'midcom_org_openpsa_person',
        ),
        'operations' => 'MIDCOM_OPERATION_DBA_ALL',
    ),
),
