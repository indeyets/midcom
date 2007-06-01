
  'name' => 'net.nemein.rss',
  'purecode' => true,
  'version' => '2.0.4beta1',
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
    'summary' => 'RSS and Atom aggregator',
    'description' => 'Aggregator that supports populating news items from remote RSS and Atom sources.',
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
      'net.nemein.tag' => 
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
        'handler' => 'net_nemein_rss_cron_fetchfeeds',
        'recurrence' => MIDCOM_CRON_HOUR,
      ),
    ),
  ),
