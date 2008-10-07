'name' => 'net.nehmer.jobmarket',
'version' => '8.09.0',
'state' => 'stable',
'class_definitions' => array
(
    'midcomdba.inc',
),
'watches' => array
(
    array
    (
        'classes' => array
        (
            'midgard_person',
        ),
        'operations' => MIDCOM_OPERATION_DBA_DELETE,
    ),
),
'customdata' => array
(
    'midcom.services.cron' => array
    (
        array
        (
            'handler' => 'net_nehmer_jobmarket_cron_cleanup',
            'recurrence' => MIDCOM_CRON_DAY,
        ),
    ),
),
'package.xml' => array
(
    'summary' => 'Job market',
    'description' => 'Job posting bulletin board',
    'maintainers' => array
    (
        'torben' => array
        (
            'name' => 'Torben Nehmer',
            'email' => 'torber@nehmer.net',
            'role' => 'lead',
            'active' => 'no',
        ),
    ),
    'dependencies' => array
    (
        'midcom' => array(),
    ),
),
