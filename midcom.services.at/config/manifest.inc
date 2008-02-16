'name' => 'midcom.services.at',
'purecode' => true,
'privileges' => array(),
'class_definitions' => array
(
    'midcom_dba_classes.inc',
),
'version' => '1.1.0beta1',
'state' => 'beta',
'package.xml' => array
(
    'summary' => 'Library for stored queries',
    'description' => 'AT service',
    'maintainers' => array
    (
        'rambo' => array
        (
            'name' => 'Eero af Heurlin',
            'email' => 'eero.afheurlin@iki.fi',
            'role' => 'lead',
        ),
    ),
    'dependencies' => array
    (
        'midcom' => array(),
    ),
),
'customdata' => array
(
    'midcom.services.cron' => array
    (
        array
        (
            'handler' => 'midcom_services_at_cron_check',
            'recurrence' => 'MIDCOM_CRON_MINUTE',
        ),
    ),
),
