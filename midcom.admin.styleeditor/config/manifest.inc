'name' => 'midcom.admin.styleeditor',
'description' => 'Style editor for Midgard style templates',
'icon' => 'stock-icons/16x16/text-x-generic-template.png',
'purecode' => true,
'version' => '8.09.0',
'privileges' => array(),
'class_definitions' => array(),
'package.xml' => array
(
    'license' => 'LGPL',
    'maintainers' => array
    (
        'adrenalin' => array
        (
            'name' => 'Arttu Manninen',
            'email' => 'arttu.manninen@protie.fi',
            'role' => 'lead',
        ),
        'bergie' => array
        (
            'name' => 'Henri Bergius',
            'email' => 'henri.bergius@iki.fi',
            'role' => 'lead',
        ),
    ),
    'dependencies' => array
    (
        'midcom.admin.help' => array(),
        'midcom' => array(),
        'JavaScript_Scriptaculous' => array
        (
            'channel' => 'pearified.com',
        ),
    ),
),
'state' => 'stable',
'customdata' => array
(
    'asgard_plugin' => array
    (
        'class' => 'midcom_admin_styleeditor_viewer',
        'src' => 'file:/midcom/admin/styleeditor/viewer.php',
        'name' => 'midcom.admin.styleeditor',
        'config' => '',
    ),
),
