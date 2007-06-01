
  'name' => 'net.nehmer.comments',
  'version' => '1.0.1beta1',
  'class_definitions' => 
  array (
    0 => 'midcomdba.inc',
  ),
  'watches' => 
  array (
    0 => 
    array (
      'classes' => NULL,
      'operations' => MIDCOM_OPERATION_DBA_DELETE,
    ),
  ),
  'package.xml' => 
  array (
    'summary' => 'Page commenting',
    'description' => 'Page commenting tool',
    'maintainers' => 
    array (
      'torben' => 
      array (
        'name' => 'Torben Nehmer',
        'email' => 'torben@nehmer.net',
        'role' => 'lead',
        'active' => 'no',
      ),
      'bergie' => 
      array (
        'name' => 'Henri Bergius',
        'email' => 'henri.bergius@iki.fi',
        'role' => 'lead',
        'active' => 'yes',
      ),
    ),
    'dependencies' => 
    array (
      'midcom.helper.datamanager2' => 
      array (
      ),
      'net.nehmer.markdown' => 
      array (
      ),
      'org.openpsa.notifications' => 
      array (
      ),
      'Text_Password' => 
      array (
        'channel' => 'pear.php.net',
      ),
      'midcom' => 
      array (
      ),
    ),
  ),
  'customdata' => 
  array (
    'org.openpsa.notifications' => 
    array (
      'comment_posted' => 
      array (
        'default' => 'email',
      ),
    ),
  ),
  'state' => 'beta',
