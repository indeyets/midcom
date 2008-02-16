'name' => 'midcom.admin.styleeditor',
'description' => 'Style editor for Midgard style templates',
'purecode' => true,
'version' => '0.3.0beta1',
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
        'midcom.admin.help',
        'midcom' => array(),
    ),
),
'state' => 'beta',
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
