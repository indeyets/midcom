
  'name' => 'net.nehmer.jobmarket',
  'version' => 'beta1',
  'state' => 'beta',
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
  'customdata' => 
  array (
    'midcom.services.cron' => 
    array (
      0 => 
      array (
        'handler' => 'net_nehmer_jobmarket_cron_cleanup',
        'recurrence' => MIDCOM_CRON_DAY,
      ),
    ),
  ),
  'package.xml' => 
  array (
    'summary' => 'Job market',
    'description' => 'Job posting bulletin board',
    'maintainers' => 
    array (
      'torben' => 
      array (
        'name' => 'Torben Nehmer',
        'email' => 'torber@nehmer.net',
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
