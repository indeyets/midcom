
  'name' => 'midcom.admin.user',
  'description' => 'User management tool',
  'purecode' => true,
  'version' => '0.0.1alpha',
  'privileges' => 
  array (
  ),
  'class_definitions' => 
  array (
  ),
  'package.xml' => 
  array (
    'license' => 'LGPL',
    'maintainers' => 
    array (
      'bergie' => 
      array (
        'name' => 'Henri Bergius',
        'email' => 'henri.bergius@iki.fi',
        'role' => 'lead',
      ),
    ),
    'dependencies' => 
    array (
      0 => 'midcom.admin.help',
      'midcom' => 
      array (
      ),
    ),
  ),
  'state' => 'alpha',
  'customdata' => array
  (
      'asgard_plugin' => array
      (
          'class' => 'midcom_admin_user_plugin',
          'src' => 'file:/midcom/admin/user/plugin.php',
          'name' => 'User Management',
          'config' => null,
      ),
  ),
