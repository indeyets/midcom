'name' => 'net.nemein.bannedwords',
'purecode' => true,
'privileges' => array(),
'class_definitions' => array
(
    'midcom_dba_classes.inc',
),
'version' => '8.09.0',
'state' => 'stable',
'package.xml' => array
(
    'summary' => 'Library for searching banned words from content and usernames',
    'maintainers' => array
    (
        'juhana' => array
        (
            'name' => 'Juhana Hirvilahti',
            'email' => 'ihirvila@cc.hut.fi',
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
