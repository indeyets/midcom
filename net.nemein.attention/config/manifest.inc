'name' => 'net.nemein.attention',
'icon' => 'net.nemein.attention/apml-icon-16x16.png',
'purecode' => true,
'version' => '0.0.1',
'state' => 'devel',
'privileges' => array(),
'class_definitions' => array('midcom_dba_classes.inc'),
'package.xml' => array
(
    'summary' => 'Attention profiler',
    'description' => 'Attention profiler',
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
        'org.openpsa.httplib' => array(),
    ),
),
'customdata' => array 
(
    'net.nehmer.account.socialweb' => array
    (
        'delicious_username' => array
        (
            'storage' => array
            (
                'location'   => 'configuration',
                'domain' => 'net.nemein.attention:delicious',
                'name'   => 'username',
            ),
        ),
        'lastfm_username' => array
        (
            'storage' => array
            (
                'location'   => 'configuration',
                'domain' => 'net.nemein.attention:lastfm',
                'name'   => 'username',
            ),
        ),
    ),
    'midcom.services.cron' => array
    (
        array
        (
            'handler' => 'net_nemein_attention_cron_fetchdelicious',
            'recurrence' => MIDCOM_CRON_DAY,
        ),
        array
        (
            'handler' => 'net_nemein_attention_cron_fetchfavourites',
            'recurrence' => MIDCOM_CRON_DAY,
        ),
        array
        (
            'handler' => 'net_nemein_attention_cron_fetchlastfm',
            'recurrence' => MIDCOM_CRON_DAY,
        ),
    ),
),


