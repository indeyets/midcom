'name' => 'midcom.helper.replicator',
'icon' => 'midcom.helper.replicator/replicate-server-16.png',
'purecode' => true,
'version' => '8.09.0',
'state' => 'stable',
'privileges' => array
(
    'manage' => MIDCOM_PRIVILEGE_DENY,
),
'class_definitions' => array
(
    'midcom_dba_classes.inc',
),
'watches' => array
(
    array
    (
        'classes' => array(),
        'operations' => MIDCOM_OPERATION_DBA_CREATE 
                | MIDCOM_OPERATION_DBA_UPDATE 
                | MIDCOM_OPERATION_DBA_DELETE,
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
            'midcom_helper_reflector',
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
            'recurrence' => MIDCOM_CRON_MINUTE,
        ),
        array
        (
            'handler' => 'midcom_helper_replicator_cron_queuenondba',
            'recurrence' => MIDCOM_CRON_HOUR,
        ),
    ),
),
