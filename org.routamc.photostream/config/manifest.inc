
  'name' => 'org.routamc.photostream',
  'purecode' => false,
  'version' => '1.0.4beta1',
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
    'summary' => 'Collection of user\'s photos',
    'description' => 'Collection of user\'s photos',
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
      'midcom.helper.datamanager2' => 
      array (
      ),
      'net.nemein.tag' => 
      array (
      ),
      'org.openpsa.qbpager' => 
      array (
      ),
      'net.nehmer.buddylist' => 
      array (
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
            'flickr_username' => array
            (
                'storage' => array
                (
                    'location'   => 'configuration',
                    'domain' => 'org.routamc.photostream:flickr',
                    'name'   => 'username',
                ),
            ),
        ),  
        'midcom.services.cron' => array
        (
            0 => array
            (
                'handler' => 'org_routamc_photostream_cron_fetchflickr',
                'recurrence' => MIDCOM_CRON_HOUR,
            ),
        ),
  ),
