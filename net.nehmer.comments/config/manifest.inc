'name' => 'net.nehmer.comments',
'version' => '1.1.0beta1',
'class_definitions' => array
(
    'midcomdba.inc',
),
'watches' => array
(
    array
    (
        'classes' => array(),
        'operations' => MIDCOM_OPERATION_DBA_DELETE,
    ),
),
'package.xml' => array
(
    'summary' => 'Page commenting',
    'description' => 'Page commenting tool',
    'maintainers' => array
    (
        'torben' => array
        (
            'name' => 'Torben Nehmer',
            'email' => 'torben@nehmer.net',
            'role' => 'lead',
            'active' => 'no',
        ),
        'bergie' => array
        (
            'name' => 'Henri Bergius',
            'email' => 'henri.bergius@iki.fi',
            'role' => 'lead',
            'active' => 'yes',
        ),
        'xfade' => array
        (
            'name' => 'Niels Breet',
            'email' => 'nbreet@gmail.com',
            'role' => 'developer',
            'active' => 'yes',
        ),
    ),
    'dependencies' => array
    (
        'midcom.helper.datamanager2' => array(),
        'net.nehmer.markdown' => array(),
        'org.openpsa.notifications' => array(),
        'org.openpsa.qbpager' => array(),
        'Text_Password' => array
        (
            'channel' => 'pear.php.net',
        ),
        'midcom' => array(),
    ),
),
'customdata' => array
(
    'org.openpsa.notifications' => array
    (
        'comment_posted' => array
        (
            'default' => 'email',
        ),
    ),
),
'state' => 'beta',
