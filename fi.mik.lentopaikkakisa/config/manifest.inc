'name' => 'fi.mik.lentopaikkakisa',
'purecode' => false,
'version' => '0.3.0beta1',
'state' => 'beta',
'privileges' => array
(
    'moderation' => MIDCOM_PRIVILEGE_DENY,
),
'class_definitions' => array
(
    'midcom_dba_classes.inc',
),
'package.xml' => array
(
    'summary' => 'Travel flight competition',
    'description' => 'Competition about flying to different aerodromes',
    'maintainers' => array
    (
        'bergie' => array
        (
            'name' => 'Henri Bergius',
            'email' => 'henri.bergius@iki.fi',
            'role' => 'lead',
        ),
        'tigert' => array
        (
            'name' => 'Tuomas Kuosmanen',
            'email' => 'tigert@gmail.com',
            'role' => 'developer',
        ),
    ),
    'dependencies' => array
    (
        'org.openpsa.calendar' => array(),
        'org.openpsa.contacts' => array(),
        'org.openpsa.qbpager' => array(),
        'org.routamc.positioning' => array(),
        'midcom' => array(),
    ),
),
