'name' => 'net.nehmer.marketplace',
'version' => '1.1.0beta1',
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
            'handler' => 'net_nehmer_marketplace_cron_cleanup',
            'recurrence' => MIDCOM_CRON_DAY,
        ),
    ),
),
'package.xml' => array
(
    'summary' => 'Marketplace',
    'description' => 'Marketplace for bids and offers on products',
    'maintainers' => array
    (
        'torben' => array
        (
            'name' => 'Torben Nehmer',
            'email' => 'torben@nehmer.net',
            'role' => 'lead',
        ),
    ),
    'dependencies' => array
    (
        'midcom.helper.datamanager2' => array(),
        'midcom' => array(),
    ),
),
'state' => 'beta',
