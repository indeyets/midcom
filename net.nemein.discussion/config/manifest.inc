
  'name' => 'net.nemein.discussion',
  'purecode' => false,
  'version' => '2.0.3beta1',
  'state' => 'beta',
  'privileges' => 
  array (
    'moderation' => MIDCOM_PRIVILEGE_DENY,
  ),
  'class_definitions' => 
  array (
    0 => 'midcom_dba_classes.inc',
  ),
  'package.xml' => 
  array (
    'summary' => 'Discussion forum',
    'description' => 'Discussion forum and blog commenting tool.',
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
      'midcom.helper.datamanager2' => 
      array (
      ),
      'de.bitfolge.feedcreator' => 
      array (
      ),
      'org.openpsa.qbpager' => 
      array (
      ),
      'net.nehmer.markdown' => 
      array (
      ),
      'midcom.helper.xml' => 
      array (
      ),
      'midcom' => 
      array (
      ),
    ),
  ),
