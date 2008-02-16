'name' => 'net.nehmer.branchenbuch',
'version' => '1.1.0beta1',
'class_definitions' => array
(
    'midcomdba.inc',
),
'privileges' => array
(
    'manage_categories' => MIDCOM_PRIVILEGE_DENY,
    'view_entries' => MIDCOM_PRIVILEGE_DENY,
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
'state' => 'beta',
