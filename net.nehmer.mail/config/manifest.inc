'name' => 'net.nehmer.mail',
'version' => '1.0.0',
'package.xml' => array
(
    'summary' => 'Community Mailing System',
    'description' => 'System for displaying and managing community messages',
    'maintainers' => array
    (
        'torben' => array
        (
            'name' => 'Torben Nehmer',
            'email' => 'torben@nehmer.net',
            'role' => 'lead',
            'active' => 'no',
        ),
        'w_i' => array
        (
            'name' => 'Jerry Jalava',
            'email' => 'jerry.jalava@gmail.com',
            'role' => 'lead',
            'active' => 'yes',
        ),
    ),
    'dependencies' => array
    (
        'midcom.helper.datamanager2' => array(),
        'net.nehmer.markdown'        => array(),
        'net.nemein.tag'             => array(),
    ),
),
'class_definitions' => Array('midcomdba.inc'),
'privileges' => Array
(
    'ignore_quota' => MIDCOM_PRIVILEGE_DENY,
    'browse_other_mailboxes' => MIDCOM_PRIVILEGE_DENY,
    'list_mails' => Array(MIDCOM_PRIVILEGE_DENY, MIDCOM_PRIVILEGE_ALLOW),
),
'watches' => Array
(
    Array
    (
        'classes' => Array ('midgard_person'),
        'operations' => MIDCOM_OPERATION_DBA_DELETE,
    ),
),
