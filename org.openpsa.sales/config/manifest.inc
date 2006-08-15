'name' => 'org.openpsa.sales',
'purecode' => false,
'version' => '1.0.0',
'class_definitions' => Array('midcom_dba_classes.inc'),
'privileges' =>  Array(),
'package.xml' => Array(
    'summary' => 'Sales',
    'description' => 'Sales process manager',
    'maintainers' => Array(
        'bergie' => Array(
            'name' => 'Eero af Heurlin',
            'email' => 'eero.afheurlin@iki.fi',
            'role' => 'lead',
        ),
    ),
    'dependencies' => Array
    (
        'midcom.helper.datamanager' => Array(),
        'org.openpsa.contacts' => Array(),
        'org.openpsa.contactwidget' => Array(),
        'net.nemein.wiki' => Array(),
        'org.openpsa.calendar' => Array(),
        'org.openpsa.projects' => Array(),
        'org.openpsa.products' => Array(),
        'org.openpsa.invoices' => Array(),
        'Calendar' => Array
        (
            'channel' => 'pear.php.net',
        ),
    ),
),
