'name' => 'midcom.admin.settings',
'icon' => 'stock-icons/16x16/configuration.png',
'description' => 'This module handles MidCOM local configuration.',
'purecode' => true,
'version' => '0.3.0beta1',
'state' => 'beta',
'privileges' => array(),
'class_definitions' => array(),
'package.xml' => array
(
    'license' => 'LGPL',
    'maintainers' => array
    (
        'solt' => array
        (
            'name' => 'Marcin Sołtysiak',
            'email' => 'olga@olga.pl',
            'role' => 'lead',
        ),
        'bergie' => array
        (
            'name' => 'Henri Bergius',
            'email' => 'henri.bergius@iki.fi',
            'role' => 'developer',
        ),
        'adrenalin' => array
        (
            'name' => 'Arttu Manninen',
            'email' => 'arttu@kaktus.cc',
            'role' => 'developer',
        ),
    ),
    'dependencies' => array
    (
        'midcom.helper.datamanager2' => array(),
        'midcom' => array(),
        'midcom.admin.folder' => array(),
    ),
),
'customdata' => array
(
    'asgard_plugin' => array
    (
        'class' => 'midcom_admin_settings_editor',
        'src' => 'file:/midcom/admin/settings/editor.php',
        'name' => 'midcom.admin.settings',
        'config' => '',
    ),
),
