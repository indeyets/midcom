'name' => 'org.openpsa.reports',
'purecode' => false,
'privileges' =>  Array(),
'class_definitions' => Array(),
'version' => '2.0.3',
'package.xml' => Array(
    'summary' => 'OpenPSA reporting module',
    'description' => 'Run reports on data from various other modules',
    'maintainers' => Array(
        'rambo' => Array(
            'name' => 'Eero af Heurlin',
            'email' => 'eero.afheurlin@iki.fi',
            'role' => 'lead',
        ),
        'bergie' => Array(
            'name' => 'Henri Bergius',
            'email' => 'henri.bergius@iki.fi',
            'role' => 'lead',
        ),  
    ),
    'dependencies' => Array(
        'org.openpsa.core'          => Array(),
        'org.openpsa.queries'       => Array(),
        'org.openpsa.helpers'       => Array(),
        'midcom.helper.datamanager' => Array(),
    ),
),
'customdata' => array
(
    'midcom.services.cron' => array
    (
        array
        (
            'handler' => 'org_openpsa_reports_cron_clearold',
            'recurrence' => MIDCOM_CRON_DAY,
        ),
    ),
),
