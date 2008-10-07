'name' => 'org.openpsa.sales',
'icon' => 'stock-icons/16x16/currency.png',
'purecode' => false,
'version' => '8.09.0',
'state' => 'stable',
'class_definitions' => array
(
    'midcom_dba_classes.inc',
),
'privileges' => array(),
'package.xml' => array
(
    'summary' => 'Sales',
    'description' => 'Sales process manager',
    'maintainers' => array
    (
        'bergie' => array
        (
            'name' => 'Henri Bergius',
            'email' => 'henri.bergius@iki.fi',
            'role' => 'lead',
            'active' => 'no',
        ),
        'rambo' => array
        (
            'name' => 'Eero af Heurlin',
            'email' => 'eero.afheurlin@iki.fi',
            'role' => 'lead',
        ),
    ),
    'dependencies' => array
    (
        'midcom.helper.datamanager' => array(),
        'midcom.helper.datamanager2' => array(),
        'midcom.services.at' => array(),
        'org.openpsa.contacts' => array(),
        'org.openpsa.contactwidget' => array(),
        'net.nemein.wiki' => array(),
        'net.nemein.tag' => array(),
        'org.openpsa.calendar' => array(),
        'org.openpsa.projects' => array(),
        'org.openpsa.products' => array(),
        'org.openpsa.invoices' => array(),
        'Calendar' => array
        (
            'channel' => 'pear.php.net',
        ),
        'midcom' => array(),
    ),
),
'customdata' => array
(
    'org.openpsa.notifications' => array
    (
        'new_subscription_cycle' => array
        (
            'default' => 'email',
        ),
    ),
),
