
  'name' => 'org.routamc.gallery',
  'purecode' => false,
  'version' => '1.0.2beta1',
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
    'summary' => 'Photo gallery',
    'description' => 'Collection of photos in photostream, based on different configurable criteria',
    'maintainers' => 
    array (
      'bergie' => 
      array (
        'name' => 'Henri Bergius',
        'email' => 'henri.bergius@nemein.com',
        'role' => 'lead',
      ),
      'rambo' => 
      array (
        'name' => 'Eero af Heurlin',
        'email' => 'eero.afheurlin@nemein.com',
        'role' => 'lead',
      ),
    ),
    'dependencies' => 
    array (
      'org.routamc.photostream' => 
      array (
      ),
      'midcom.helper.datamanager2' => 
      array (
      ),
      'net.nemein.tag' => 
      array (
      ),
      'org.openpsa.qbpager' => 
      array (
      ),
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
        'handler' => 'org_routamc_gallery_cron_gallery_sync',
        'recurrence' => MIDCOM_CRON_DAY,
      ),
    ),
  ),
