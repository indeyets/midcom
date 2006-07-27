'name' => 'net.nehmer.buddylist',
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

'package.xml' => Array(
    'summary' => 'Buddy list manager',
    'description' => 'Buddy list and social network manager',
    'maintainers' => Array(
        'torben' => Array(
            'name' => 'Torben Nehmer',
            'email' => 'torben@nehmer.net',
            'role' => 'lead',
        ),
    ),
),


//'customdata' => Array
//(
//    'midcom.services.cron' => Array
//    (
//        Array
//        (
//            'handler' => 'net_nehmer_buddylist_cron_cleanup',
//            'recurrence' => MIDCOM_CRON_DAY,
//        )
//    ),
//),
