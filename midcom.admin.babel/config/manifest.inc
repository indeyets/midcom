  'name' => 'midcom.admin.babel',
  'description' => 'MidCOM localization utility',
  'purecode' => true,
  'version' => '0.1.2beta1',
  'privileges' =>
  array (
  ),
  'class_definitions' =>
  array (
  ),
  'package.xml' =>
  array (
    'license' => 'GPL',
    'maintainers' =>
    array (
      'solt' =>
      array (
        'name' => 'Marcin Sołtysiak',
        'email' => 'olga@olga.pl',
        'role' => 'lead',
      ),
    ),
    'dependencies' =>
    array (
      'midcom' =>
      array (
      ),
    ),
  ),
  'state' => 'beta',
  'customdata' => array
  (
      'asgard_plugin' => array
      (
          'class' => 'midcom_admin_babel_plugin',
          'src' => 'file:/midcom/admin/babel/plugin.php',
          'name' => 'Localization tool',
          'config' => null,
      ),
  ),

