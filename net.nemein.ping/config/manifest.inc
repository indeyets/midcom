'name' => 'net.nemein.ping',
'purecode' => true,
'version' => 1,
'state' => 'alpha',
'privileges' =>  Array(),
'class_definitions' => Array(),
'watches' => Array(
    Array(
        'classes' => Array(
            'midcom_baseclasses_database_article',
        ),
        'operations' => 
              MIDCOM_OPERATION_DBA_UPDATE
            | MIDCOM_OPERATION_DBA_CREATE,
    ),
),
'package.xml' => Array(
    'summary' => 'Weblog pinger service',
    'description' => 'Tool that pings weblogs.com and other services when de.linkm.newsticker postings have been updated',
    'maintainers' => Array(
        'bergie' => Array(
            'name' => 'Henri Bergius',
            'email' => 'henri.bergius@iki.fi',
            'role' => 'lead',
        ),
    ),
    'dependencies' => Array(
        'de.linkm.newsticker' => Array(),
        'XML_RPC' => Array(
            'channel' => 'pear.php.net',
        ),
    ),
),