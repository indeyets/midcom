
  'name' => 'net.nemein.tag',
  'purecode' => true,
  'version' => '1.0.6beta1',
  'state' => 'beta',
  'privileges' => 
  array (
  ),
  'class_definitions' => 
  array (
    0 => 'midcom_dba_classes.inc',
  ),
  'package.xml' => 
  array (
    'summary' => 'Tag library',
    'description' => 'Tag library',
    'maintainers' => 
    array (
      'rambo' => 
      array (
        'name' => 'Eero af Heurlin',
        'email' => 'eero.afheurlin@iki.fi',
        'role' => 'lead',
      ),
      'bergie' => 
      array (
        'name' => 'Henri Bergius',
        'email' => 'henri.bergius@iki.fi',
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
  'customdata' => 
  array (
    'midcom.services.cron' => 
    array (
      0 => 
      array (
        'handler' => 'net_nemein_tag_cron_clean',
        'recurrence' => MIDCOM_CRON_DAY,
      ),
    ),
  ),
