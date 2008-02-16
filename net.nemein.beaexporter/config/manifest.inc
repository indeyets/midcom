'name' => 'net.nemein.beaexporter',
'purecode' => true,
'privileges' => array(),
'class_definitions' => array
(
    'midcom_dba_classes.inc',
),
'version' => '0.1.0beta1',
'state' => 'beta',
'package.xml' => array
(
    'summary' => 'BEA BulkLoader exporter',
    'description' => 'Exports content from supported components to format used by BEA BulkLoader',
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
'watches' => array
(
    array
    (
        'classes' => array
        (
            'midcom_baseclasses_database_article',
        ),
        'operations' => 'MIDCOM_OPERATION_DBA_ALL',
    ),
),
