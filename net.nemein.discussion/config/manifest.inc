'name' => 'net.nemein.discussion',
'purecode' => false,
'version' => '2.0.0beta1',
'state'   => 'devel',
'privileges' =>  Array
(
	'moderation' => MIDCOM_PRIVILEGE_DENY, 
),
'class_definitions' => Array('midcom_dba_classes.inc'),
'package.xml' => Array(
    'summary' => 'Discussion forum',
    'description' => 'Discussion forum and blog commenting tool.',
    'maintainers' => Array(
        'bergie' => Array(
            'name' => 'Henri Bergius',
            'email' => 'henri.bergius@iki.fi',
            'role' => 'lead',
        ),
    ),
    'dependencies' => Array(
        'midcom.helper.datamanager2' => Array(),
        'de.bitfolge.feedcreator' => Array(),
        'org.openpsa.qbpager' => Array(),
	'net.nehmer.markdown' => Array(),
    ),
),
