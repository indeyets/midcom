
  'name' => 'org.routamc.positioning',
  'icon' => 'stock-icons/16x16/geoclue.png',
  'purecode' => true,
  'privileges' => 
  array (
  ),
  'class_definitions' => 
  array (
    0 => 'midcom_dba_classes.inc',
  ),
  'version' => '1.1.2beta3',
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
      'w_i' => 
      array (
        'name' => 'Jerry Jalava',
        'email' => 'jerry.jalava@gmail.com',
        'role' => 'developer',
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
  'customdata' => array 
  (
        'net.nehmer.account.socialweb' => array
        (
            'plazes_username' => array
            (
                'storage' => array
                (
                    'location'   => 'configuration',
                    'domain' => 'org.routamc.positioning:plazes',
                    'name'   => 'username',
                ),
            ),
            'plazes_password' => array
            (
                'storage' => array
                (
                    'location'   => 'configuration',
                    'domain' => 'org.routamc.positioning:plazes',
                    'name'   => 'password',
                ),
            ),
            'georss_url' => array
            (
                'storage' => array
                (
                    'location'   => 'configuration',
                    'domain' => 'org.routamc.positioning:georss',
                    'name'   => 'georss_url',
                ),
            ),
        ),
        'midcom.services.cron' => array
        (
            0 => array
            (
                'handler' => 'org_routamc_positioning_cron_fetchplazes',
                'recurrence' => MIDCOM_CRON_HOUR,
            ),
            1 => array
            (
                'handler' => 'org_routamc_positioning_cron_fetchicbm',
                'recurrence' => MIDCOM_CRON_HOUR,
            ),
            2 => array
            (
                'handler' => 'org_routamc_positioning_cron_fetchgeorss',
                'recurrence' => MIDCOM_CRON_HOUR,
            ),
        ),
  ),
