'name' => 'net.nehmer.comments',
'version' => '1.0.0beta2',
'class_definitions' => Array('midcomdba.inc'),

'watches' => Array
(
    Array
    (
        'classes' => null,
        'operations' => MIDCOM_OPERATION_DBA_DELETE,
    ),
),

'package.xml' => Array(
    'summary' => 'Page commenting',
    'description' => 'Page commenting tool',
    'maintainers' => Array(
        'bergie' => Array(
            'name' => 'Henri Bergius',
            'email' => 'henri.bergius@iki.fi',
            'role' => 'lead',
        ),    
        'torben' => Array(
            'name' => 'Torben Nehmer',
            'email' => 'torben@nehmer.net',
            'role' => 'lead',
        ),
    ),
    'dependencies' => Array(
        'midcom.helper.datamanager2' => Array(),
    	'net.nehmer.markdown' => Array(),
    	'Text_Password' => Array(
    	   'channel' => 'pear.php.net',
    	),
    ),
),


