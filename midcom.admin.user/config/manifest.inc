'name' => 'midcom.admin.user',
'description' => 'User management tool',
'purecode' => true,
'version' => '1.1.0beta1',
'privileges' => array(),
'class_definitions' => array(),
'package.xml' => array
(
    'license' => 'LGPL',
    'maintainers' => array
    (
        'bergie' => array
        (
            'name' => 'Henri Bergius',
            'email' => 'henri.bergius@iki.fi',
            'role' => 'lead',
        ),
        'adrenalin' => array
        (
            'name' => 'Arttu Manninen',
            'email' => 'arttu@kaktus.cc',
            'role' => 'lead',
        ),
    ),
    'dependencies' => array
    (
        'midcom.admin.help' => array(),
        'midcom.helper.datamanager2' => array(),
        'midcom' => array(),
    ),
),
'state' => 'beta',
'customdata' => array
(
    'asgard_plugin' => array
    (
        'class' => 'midcom_admin_user_plugin',
        'src' => 'file:/midcom/admin/user/plugin.php',
        'name' => 'User Management',
        'config' => '',
    ),
),
