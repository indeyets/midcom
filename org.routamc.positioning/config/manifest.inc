'name' => 'org.routamc.positioning',
'purecode' => true,
'privileges' =>  Array(),
'class_definitions' => Array('midcom_dba_classes.inc'),
'version' => '0.0.6',
'state' => 'devel',
'package.xml' => Array(
    'summary' => 'Geopositioning library',
    'maintainers' => Array(
        'bergie' => Array(
            'name' => 'Henri Bergius',
            'email' => 'henri.bergius@iki.fi',
            'role' => 'lead',
        ),
    ),
    'dependencies' => Array(
    ),
),
'customdata' => array
(
    'midcom.services.cron' => array
    (
        array
        (
            'handler' => 'org_routamc_positioning_cron_fetchplazes',
            'recurrence' => MIDCOM_CRON_HOUR,
        ),
    ),
),
