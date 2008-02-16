'name' => 'org.maemo.devcodes',
'purecode' => false,
'version' => '0.1.0beta1',
'state' => 'beta',
'privileges' => array
(
    'manage' => MIDCOM_PRIVILEGE_DENY,
    'read' => MIDCOM_PRIVILEGE_DENY,
),
'class_definitions' => array
(
    'midcom_dba_classes.inc',
),
'package.xml' => array
(
    'summary' => 'Manage developer discount codes',
    'description' => 'Manage developer discount codes',
    'maintainers' => array
    (
        'rambo' => array
        (
            'name' => 'Eero af Heurlin',
            'email' => 'eero.afheurlin@nemein.com',
            'role' => 'lead',
        ),
    ),
    'dependencies' => array
    (
        'midcom.helper.datamanager2' => array(),
        'org.openpsa.directmarketing' => array(),
        'org.openpsa.contacts' => array(),
    ),
),
