'name' => 'net.nemein.bannedwords',
'purecode' => true,
'privileges' => array(),
'class_definitions' => array
(
    'midcom_dba_classes.inc',
),
'version' => '0.1.0beta1',
'state' => 'beta',
'package.xml' => array
(
    'summary' => 'Library for searching banned words from content and usernames',
    'maintainers' => array
    (
        'juhana' => array
        (
            'name' => 'Juhana Hirvilahti',
            'email' => 'juhana@nemein.com',
            'role' => 'lead',
            'active' => 'no',
        ),
    ),
    'dependencies' => array
    (
        'midcom' => array(),
    ),
),
'customdata' => array
(
    'request_handler_plugin' => array
    (
        'class' => 'net_nemein_bannedwords_edit_handler',
        'src' => 'file:/net/nemein/bannedwords/editor.php',
        'name' => 'Banned words editor',
        'config' => '',
    ),
),
