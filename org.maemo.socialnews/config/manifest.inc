'name' => 'org.maemo.socialnews',
'purecode' => false,
'version' => '0.1.0beta1',
'state' => 'beta',
'privileges' => array(),
'class_definitions' => array
(
    'midcom_dba_classes.inc',
),
'watches' => array
(
    array
    (
        'classes' => array
        (
            'net_nemein_favourites_favourite',
        ),
        'operations' => 'MIDCOM_OPERATION_DBA_CREATE',
    ),
),
'package.xml' => array
(
    'summary' => 'Social news aggregator',
    'description' => 'Social news aggregator',
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
        'net.nemein.attention' => array(),
        'net.nehmer.blog' => array(),
        'org.openpsa.httplib' => array(),
        'midcom.services.at' => array(),
    ),
),
'customdata' => array
(
    'midcom.services.cron' => array
    (
        array
        (
            'handler' => 'org_maemo_socialnews_cron_refreshscores',
            'recurrence' => 'MIDCOM_CRON_HOUR',
        ),
        array
        (
            'handler' => 'org_maemo_socialnews_cron_archive',
            'recurrence' => 'MIDCOM_CRON_DAY',
        ),
    ),
),
