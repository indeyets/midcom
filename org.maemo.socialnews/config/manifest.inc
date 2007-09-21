'name' => 'org.maemo.socialnews',
/* if the module is a purecode one, then you cannot use it 
 * to create direct webpages.*/ 
'purecode' => false,
/* when using pear packaging, versions > 1 are stable */
'version' => '0.0.1',
'state' => 'devel',
/* use this to set default privileges */
'privileges' => array(),
/* use this to initialize your own MgdSchema classes */
'class_definitions' => Array('midcom_dba_classes.inc'),
'watches' => array
(
    array
    (
        'classes' => array
        (
            'net_nemein_favourites_favourite',
        ),
        'operations' => MIDCOM_OPERATION_DBA_CREATE,
    ),
),
/**
 * This is used by the PEAR packaging.
 */
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
    /* Define what modules this module depends on.  */
    'dependencies' => Array
    (
        'net.nehmer.blog' => array(),
        'org.openpsa.httplib' => array(),
    ),
),
'customdata' => array
(
    'midcom.services.cron' => array
    (
        0 => array
        (
            'handler' => 'org_maemo_socialnews_cron_refreshscores',
            'recurrence' => MIDCOM_CRON_HOUR,
        ),
        1 => array
        (
            'handler' => 'org_maemo_socialnews_cron_archive',
            'recurrence' => MIDCOM_CRON_DAY,
        ),
    )
),


