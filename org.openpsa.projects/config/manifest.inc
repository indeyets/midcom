'name' => 'org.openpsa.projects',
'purecode' => false,
'privileges' =>  Array(),
'class_definitions' => Array('midcom_dba_classes.inc'),
'version' => '2.1.0',
'package.xml' => Array(
    'summary' => 'Project/Task management and hour reporting',
    'description' => 'Manage tasks and projects, report hours towards them.',
    'maintainers' => Array(
        'bergie' => Array(
            'name' => 'Henri Bergius',
            'email' => 'henri.bergius@iki.fi',
            'role' => 'lead',
        ),  
        'rambo' => Array(
            'name' => 'Eero af Heurlin',
            'email' => 'eero.afheurlin@iki.fi',
            'role' => 'lead',
        ),
    ),
    'dependencies' => Array(
        'org.openpsa.core'          => Array(),
        'org.openpsa.helpers'       => Array(),
        'midcom.helper.datamanager' => Array(),
        'org.openpsa.contactwidget' => Array(),
    ),
),
