'name' => 'net.nehmer.jobmarket',
'version' => 1,
'state' => 'stable',
'class_definitions' => Array('midcomdba.inc'),
'watches' => Array
(
    Array
    (
        'classes' => Array ('midgard_person'),
        'operations' => MIDCOM_OPERATION_DBA_DELETE,
    ),
),

'customdata' => Array
(
    'midcom.services.cron' => Array
    (
        Array
        (
            'handler' => 'net_nehmer_jobmarket_cron_cleanup',
            'recurrence' => MIDCOM_CRON_DAY,
        )
    ),
),
'package.xml' => Array(
    'summary' => 'Job market',
    'description' => 'Job posting bulletin board',
    'maintainers' => Array(
        'torben' => Array(
            'name' => 'Torben Nehmer',
            'email' => 'torber@nehmer.net',
            'role' => 'lead',
        ),
    ),
),