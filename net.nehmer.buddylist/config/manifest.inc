
  'name' => 'net.nehmer.buddylist',
  'version' => '1.0.3beta1',
  'class_definitions' => 
  array (
    0 => 'midcomdba.inc',
  ),
  'watches' => 
  array (
    0 => 
    array (
      'classes' => 
      array (
        0 => 'midgard_person',
      ),
      'operations' => MIDCOM_OPERATION_DBA_DELETE,
    ),
  ),
  'package.xml' => 
  array (
    'summary' => 'Buddy list manager',
    'description' => 'Buddy list and social network manager',
    'maintainers' => 
    array (
      'torben' => 
      array (
        'name' => 'Torben Nehmer',
        'email' => 'torben@nehmer.net',
        'role' => 'lead',
      ),
      'rambo' => 
      array (
        'name' => 'Eero af Heurlin',
        'email' => 'eero.afheurlin@iki.fi',
        'role' => 'lead',
      ),
    ),
    'dependencies' => 
    array (
      'midcom' => 
      array (
      ),
      'org.openpsa.qbpager' => 
      array (
      ),
    ),
  ),
  'state' => 'beta',
