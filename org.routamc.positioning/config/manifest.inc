
  'name' => 'org.routamc.positioning',
  'purecode' => true,
  'privileges' => 
  array (
  ),
  'class_definitions' => 
  array (
    0 => 'midcom_dba_classes.inc',
  ),
  'version' => '1.1.2beta1',
  'state' => 'beta',
  'package.xml' => 
  array (
    'summary' => 'Geopositioning library',
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
      'org.openpsa.httplib' => 
      array (
      ),
      'net.nemein.rss' => 
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
  'customdata' => 
  array (
    'midcom.services.cron' => 
    array (
      0 => 
      array (
        'handler' => 'org_routamc_positioning_cron_fetchplazes',
        'recurrence' => MIDCOM_CRON_HOUR,
      ),
      1 => 
      array (
        'handler' => 'org_routamc_positioning_cron_fetchicbm',
        'recurrence' => MIDCOM_CRON_HOUR,
      ),
      2 => 
      array (
        'handler' => 'org_routamc_positioning_cron_fetchgeorss',
        'recurrence' => MIDCOM_CRON_HOUR,
      ),
    ),
  ),
