'name' => 'cc.kaktus.todo',
'purecode' => false,
'version' => '0.1.0beta1',
'state' => 'beta',
'class_definitions' => array
(
    'midcom_dba_classes.inc',
),
'package.xml' => array
(
    'summary' => 'TODO listing',
    'description' => 'Lists tasks that require attention.',
    'maintainers' => array
    (
        'adrenalin' => array
        (
            'name' => 'Arttu Manninen',
            'email' => 'arttu.manninen@protie.fi',
            'role' => 'lead',
        ),
    ),
    'dependencies' => array
    (
        'midcom.helper.datamanager2' => array(),
        'midcom' => array(),
    ),
),
