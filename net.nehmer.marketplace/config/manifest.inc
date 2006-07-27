'name' => 'net.nehmer.marketplace',
'version' => 1,
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
            'handler' => 'net_nehmer_marketplace_cron_cleanup',
            'recurrence' => MIDCOM_CRON_DAY,
        )
    ),
),