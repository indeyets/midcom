'name' => 'midcom.services.at',
'purecode' => true,
'privileges' =>  Array(),
'class_definitions' => Array('midcom_dba_classes.inc'),
'version' => '1.0.1',
'state' => 'stable',
'package.xml' => Array(
    'summary' => 'Library for stored queries',
    'description' => 'AT service',
    'maintainers' => Array(
        'rambo' => Array(
            'name' => 'Eero af Heurlin',
            'email' => 'eero.afheurlin@iki.fi',
            'role' => 'lead',
        ),
    ),
),
'customdata' => array
(
    'midcom.services.cron' => array
    (
        array
        (
            'handler' => 'midcom_services_at_cron_check',
            'recurrence' => MIDCOM_CRON_MINUTE,
        ),
    ),
),

