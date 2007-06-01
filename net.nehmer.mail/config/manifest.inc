'name' => 'net.nehmer.mail',
'version' => '1.0.0',
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
