'name' => 'org.routamc.statusmessage',
'purecode' => false,
'version' => '0.1.0beta1',
'state' => 'beta',
'privileges' => array(),
'class_definitions' => array
(
    'midcom_dba_classes.inc',
),
'package.xml' => array
(
    'summary' => 'Quick status messages',
    'description' => 'Quick status messages',
    'maintainers' => array
    (
        'bergie' => array
        (
            'name' => 'Henri Bergius',
            'email' => 'henri.bergius@iki.fi',
            'role' => 'lead',
        ),
    ),
    'dependencies' => array
    (
        'midcom.helper.datamanager2' => array(),
        'org.openpsa.httplib' => array(),
        'midcom' => array(),
    ),
),
'customdata' => array
(
    'midcom.services.cron' => array
    (
        array
        (
            'handler' => 'org_routamc_statusmessage_cron_fetchtwitter',
            'recurrence' => 'MIDCOM_CRON_MINUTE',
        ),
        array
        (
            'handler' => 'org_routamc_statusmessage_cron_fetchjaiku',
            'recurrence' => 'MIDCOM_CRON_MINUTE',
        ),
    ),
),
