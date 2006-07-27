'name' => 'net.nemein.opendeploydumper',
'purecode' => true,
'privileges' =>  Array(),
'class_definitions' => Array('midcom_dba_classes.inc'),
'version' => '0.0.1',
'state' => 'beta',
'package.xml' => Array(
    'summary' => 'Interwoves Opendeploy dumper',
    'description' => 'Dumps data from supported components to format used by OpenDeploy',
    'maintainers' => Array(
        'rambo' => Array(
            'name' => 'Eero af Heurlin',
            'email' => 'eero.afheurlin@iki.fi',
            'role' => 'lead',
        ),  
    ),
),
'watches' => array
(
    array
    (
        'classes' => array
        (
            'midcom_baseclasses_database_article',
        ),
        'operations' => MIDCOM_OPERATION_DBA_ALL,
    ),
),
