
  'name' => 'midcom.helper.replicator',
  'purecode' => true,
  'version' => '0.0.3beta1',
  'state' => 'beta',
  'privileges' => 
  array (
  ),
  'class_definitions' => 
  array (
    0 => 'midcom_dba_classes.inc',
  ),
  'watches' => 
  array (
    0 => 
    array (
      'classes' => NULL,
      'operations' =>
          MIDCOM_OPERATION_DBA_CREATE
        | MIDCOM_OPERATION_DBA_UPDATE
        | MIDCOM_OPERATION_DBA_DELETE,
    ),
  ),
  'package.xml' => 
  array (
    'summary' => 'Content replication',
    'description' => 'Content replication and backup system',
    'maintainers' => 
    array (
      'bergie' => 
      array (
        'name' => 'Henri Bergius',
        'email' => 'henri.bergius@iki.fi',
        'role' => 'lead',
      ),
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
  'customdata' => array 
    (
        'request_handler_plugin' => array
        (
            'class' => 'midcom_helper_replicator_manager',
            'src' => 'file:/midcom/helper/replicator/manager.php',
            'name' => 'Replication management',
            'config' => null,
        ),
        'midcom.services.cron' => array
        (
            0 => array 
            (
                'handler' => 'midcom_helper_replicator_cron_processqueue',
                'recurrence' => MIDCOM_CRON_MINUTE,
            ),
            1 => array 
            (
                'handler' => 'midcom_helper_replicator_cron_queuenondba',
                'recurrence' => MIDCOM_CRON_HOUR,
            ),
        ),
    ),