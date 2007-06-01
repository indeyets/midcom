
  'name' => 'net.nemein.beaexporter',
  'purecode' => true,
  'privileges' => 
  array (
  ),
  'class_definitions' => 
  array (
    0 => 'midcom_dba_classes.inc',
  ),
  'version' => '0.0.3beta1',
  'state' => 'beta',
  'package.xml' => 
  array (
    'summary' => 'BEA BulkLoader exporter',
    'description' => 'Exports content from supported components to format used by BEA BulkLoader',
    'maintainers' => 
    array (
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
    ),
  ),
  'watches' => 
  array (
    0 => 
    array (
      'classes' => 
      array (
        0 => 'midcom_baseclasses_database_article',
      ),
      'operations' => MIDCOM_OPERATION_DBA_ALL,
    ),
  ),
