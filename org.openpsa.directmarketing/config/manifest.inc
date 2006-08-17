'name' => 'org.openpsa.directmarketing',
'purecode' => false,
'privileges' =>  Array(),
'class_definitions' => Array('midcom_dba_classes.inc'),
'version' => '2.0.4',
'package.xml' => Array(
    'summary' => 'Direct marketing and mailing lists',
    'description' => 'Application for sending emails, newsletters and SMS messages to registered users.',
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
        'org.openpsa.mail'          => Array(),        
        'org.openpsa.helpers'       => Array(),        
        'midcom.helper.datamanager' => Array(),
        'org.openpsa.queries'       => Array(),
        'org.openpsa.contactwidget' => Array(),
        'org.openpsa.smslib'        => Array(),
        'org.openpsa.qbpager'       => Array(),
        'midcom.services.at'        => Array(),
        'Contact_Vcard_Parse'        => Array(
            'channel' => 'pear.php.net',
        ),
    ),    
),
'customdata' => array
(
    'midcom.services.cron' => array
    (
        array
        (
            'handler' => 'org_openpsa_directmarketing_cron_updatemembers',
            'recurrence' => MIDCOM_CRON_DAY,
        ),
        array
        (
            'handler' => 'org_openpsa_directmarketing_cron_cleartokens',
            'recurrence' => MIDCOM_CRON_DAY,
        ),
    ),
),
'watches' => Array(
    Array(
        'classes' => Array(
            'net_nemein_discussion_post_dba',
        ),
        'operations' => MIDCOM_OPERATION_DBA_CREATE,
    ),
),
