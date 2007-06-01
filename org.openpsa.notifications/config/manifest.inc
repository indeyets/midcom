
  'name' => 'org.openpsa.notifications',
  'purecode' => true,
  'privileges' => 
  array (
  ),
  'version' => '0.0.7beta1',
  'state' => 'beta',
  'class_definitions' => 
  array (
    0 => 'midcom_dba_classes.inc',
  ),
  'package.xml' => 
  array (
    'summary' => 'Notification manager',
    'description' => 'System for sending notifications to users based on their preferences',
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
      'org.openpsa.mail' => 
      array (
      ),
      'midcom' => 
      array (
      ),
    ),
  ),
