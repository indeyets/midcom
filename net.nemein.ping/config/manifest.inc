
  'name' => 'net.nemein.ping',
  'purecode' => true,
  'version' => '1.0.2beta1',
  'state' => 'beta',
  'privileges' => 
  array (
  ),
  'class_definitions' => 
  array (
  ),
  'watches' => 
  array (
    0 => 
    array (
      'classes' => 
      array (
        0 => 'midcom_baseclasses_database_article',
      ),
      'operations' => 
          MIDCOM_OPERATION_DBA_UPDATE
        | MIDCOM_OPERATION_DBA_CREATE,
    ),
  ),
  'package.xml' => 
  array (
    'summary' => 'Weblog pinger service',
    'description' => 'Tool that pings weblogs.com and other services when net.nehmer.blog postings have been updated',
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
      'net.nehmer.blog' => 
      array (
      ),
      'midcom.services.at' => 
      array (
      ),
      'XML_RPC' => 
      array (
        'channel' => 'pear.php.net',
      ),
      'midcom' => 
      array (
      ),
    ),
  ),
