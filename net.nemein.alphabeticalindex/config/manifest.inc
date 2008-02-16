'name' => 'net.nemein.alphabeticalindex',
'purecode' => false,
'version' => '1.2.0beta1',
'state' => 'beta',
'privileges' => array(),
'class_definitions' => array
(
    'midcomdba.inc',
),
'watches' => array
(
    array
    (
        'classes' => array
        (
            'midcom_baseclasses_database_topic',
            'midcom_baseclasses_database_article',
        ),
        'operations' => 'MIDCOM_OPERATION_DBA_WWMETE',
    ),
),
'package.xml' => array
(
    'summary' => 'Alphabetical index',
    'description' => 'Lists defined content in alphabetical order',
    'maintainers' => array
    (
        'w_i' => array
        (
            'name' => 'Jerry Jalava',
            'email' => 'jerry.jalava@gmail.com',
            'role' => 'lead',
        ),
    ),
    'dependencies' => array
    (
        'midcom.helper.datamanager2' => array(),
    ),
),
