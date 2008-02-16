'name' => 'midcom.helper.replicator',
'icon' => 'midcom.helper.replicator/replicate-server-16.png',
'purecode' => true,
'version' => '0.1.0beta1',
'state' => 'beta',
'privileges' => array(),
'class_definitions' => array
(
    'midcom_dba_classes.inc',
),
'watches' => array
(
    array
    (
        'classes' => '',
        'operations' => 'MIDCOM_OPERATION_DBA_WWMETE',
    ),
),
'package.xml' => array
(
    'summary' => 'Content replication',
    'description' => 'Content replication and backup system',
    'maintainers' => array
    (
        'bergie' => array
        (
            'name' => 'Henri Bergius',
            'email' => 'henri.bergius@iki.fi',
            'role' => 'lead',
        ),
        'rambo' => array
        (
            'name' => 'Eero af Heurlin',
            'email' => 'eero.afheurlin@iki.fi',
            'role' => 'lead',
        ),
    ),
    'dependencies' => array
    (
        'midcom' => array
        (
            'org_openpsa_httplib',
        ),
    ),
),
'customdata' => array
(
    'asgard_plugin' => array
    (
        'class' => 'midcom_helper_replicator_manager',
        'src' => 'file:/midcom/helper/replicator/manager.php',
        'name' => 'replication subscriptions',
        'config' => '',
    ),
    'midcom.services.cron' => array
    (
        array
        (
            'handler' => 'midcom_helper_replicator_cron_processqueue',
            'recurrence' => 'MIDCOM_CRON_MINUTE',
        ),
        array
        (
            'handler' => 'midcom_helper_replicator_cron_queuenondba',
            'recurrence' => 'MIDCOM_CRON_HOUR',
        ),
    ),
),
