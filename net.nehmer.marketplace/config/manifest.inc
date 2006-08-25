'name' => 'net.nehmer.marketplace',
'version' => '1.0.0',
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

'package.xml' => Array
(
    'summary' => 'Marketplace',
    'description' => 'Marketplace for bids and offers on products',
    'maintainers' => Array
    (
        'torben' => Array
        (
            'name' => 'Torben Nehmer',
            'email' => 'torben@nehmer.net',
            'role' => 'lead',
        ),
    ),
    'dependencies' => Array
    (
        'midcom.helper.datamanager2' => Array(),
    ),
),