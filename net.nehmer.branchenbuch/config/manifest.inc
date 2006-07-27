'name' => 'net.nehmer.branchenbuch',
'version' => 1,
'class_definitions' => Array('midcomdba.inc'),
'privileges' => Array
(
    'manage_categories' => MIDCOM_PRIVILEGE_DENY,
    'view_entries' => MIDCOM_PRIVILEGE_DENY,
),
'watches' => Array
(
    Array
    (
        'classes' => Array ('midgard_person'),
        'operations' => MIDCOM_OPERATION_DBA_DELETE,
    ),
),