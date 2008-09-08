'name' => 'net.nehmer.mail',
'icon' => 'stock-icons/16x16/mail-reply-all.png',
'version' => '1.9.0beta1',
'state' => 'beta',
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
        'net.nehmer.markdown' => array(),
        'net.nemein.tag' => array(),
        'org.openpsa.notifications' => array(),
    ),
),
'class_definitions' => array
(
    'midcomdba.inc',
),
'privileges' => array
(
    'ignore_quota' => MIDCOM_PRIVILEGE_DENY,
    'browse_other_mailboxes' => MIDCOM_PRIVILEGE_DENY,
    'list_mails' => array
    (
        MIDCOM_PRIVILEGE_DENY,
        MIDCOM_PRIVILEGE_ALLOW,
    ),
),
'watches' => array
(
    array
    (
        'classes' => array
        (
            'midgard_person',
        ),
        'operations' => MIDCOM_OPERATION_DBA_DELETE,
    ),
),
'customdata' => array
(
    'org.openpsa.notifications' => array
    (
        'new_mail' => array
        (
            'default' => 'email',
        ),
    ),
),
