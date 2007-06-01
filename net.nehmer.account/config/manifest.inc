
  'name' => 'net.nehmer.account',
  'version' => '1.0.4beta2',
  'state' => 'beta',
  'package.xml' => 
  array (
    'summary' => 'Account management and registration',
    'description' => 'Allows users to manage their accounts. New users can register for new accounts as well.',
    'maintainers' => 
    array (
      'torben' => 
      array (
        'name' => 'Torben Nehmer',
        'email' => 'torben@nehmer.net',
        'role' => 'lead',
      ),
    ),
    'dependencies' => 
    array (
      'midcom.helper.datamanager2' => 
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
        'handler' => 'net_nehmer_account_cron_cleanup',
        'recurrence' => MIDCOM_CRON_DAY,
      ),
    ),
  ),
