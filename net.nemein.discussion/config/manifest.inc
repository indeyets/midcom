'name' => 'net.nemein.discussion',
'icon' => 'stock-icons/16x16/stock-discussion.png',
'purecode' => false,
'version' => '2.0.3beta2',
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
    'summary' => 'Discussion forum',
    'description' => 'Discussion forum and blog commenting tool.',
    'maintainers' => array
    (
        'bergie' => array
        (
            'name' => 'Henri Bergius',
            'email' => 'henri.bergius@iki.fi',
            'role' => 'lead',
        ),
    ),
    'dependencies' => array
    (
        'midcom.helper.datamanager2' => array(),
        'de.bitfolge.feedcreator' => array(),
        'org.openpsa.notifications' => array(),
        'org.openpsa.qbpager' => array(),
        'net.nehmer.markdown' => array(),
        'net.nemein.tag' => array(),
        'midcom.helper.xml' => array(),
        'midcom' => array(),
    ),
),
'customdata' => array
(
    'org.openpsa.notifications' => array 
    (
        'reported_abuse' => array
        (
            'default' => 'email',
        ),
    ),
),