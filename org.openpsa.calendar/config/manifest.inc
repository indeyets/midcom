'name' => 'org.openpsa.calendar',
'purecode' => false,
'privileges' =>  Array
(
	'read' => MIDCOM_PRIVILEGE_ALLOW,
),
'class_definitions' => Array('midcom_dba_classes.inc'),
'version' => '2.0.3',
'package.xml' => Array(
    'summary' => 'Group calendar',
    'description' => 'Group calendar for workgroups.',
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
        'midcom.helper.datamanager' => Array(),
        'org.openpsa.core'          => Array(),
        'org.openpsa.mail'          => Array(),
        'org.openpsa.helpers'       => Array(),
        'org.openpsa.calendarwidget' => Array(),
        'org.openpsa.contacts'      => Array(),
        'org.openpsa.notifications' => Array(),
        'net.nemein.wiki' => Array(),
    ),
),
'customdata' => array
(
    'midcom.services.cron' => array
    (
        array
        (
            'handler' => 'org_openpsa_calendar_cron_reporthours',
            'recurrence' => MIDCOM_CRON_HOUR,
        ),
    ),
),
