'name' => 'fi.mik.lentopaikkakisa',
'purecode' => false,
'version' => '0.0.2',
'state'   => 'devel',
'privileges' =>  Array
(
	'moderation' => MIDCOM_PRIVILEGE_DENY, 
),
'class_definitions' => Array('midcom_dba_classes.inc'),
'package.xml' => Array(
    'summary' => 'Travel flight competition',
    'description' => 'Competition about flying to different aerodromes',
    'maintainers' => Array(
        'bergie' => Array(
            'name' => 'Henri Bergius',
            'email' => 'henri.bergius@iki.fi',
            'role' => 'lead',
        ),
    ),
    'dependencies' => Array(
        'midcom.helper.datamanager2' => Array(),
        'midcom.helper.xml' => Array(),
        'org.openpsa.qbpager' => Array(),
    ),
),
