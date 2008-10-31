'name' => 'org.openpsa.expenses',
'purecode' => false,
'version' => '8.09.1',
'state' => 'stable',
'privileges' => array(),
'class_definitions' => array
(
    'midcom_dba_classes.inc',
),
'package.xml' => array
(
    'summary' => 'Expense tracking',
    'description' => 'Tracking of work hours, expenses and mileages',
    'maintainers' => array
    (
        'bergie' => array
        (
            'name' => 'Henri Bergius',
            'email' => 'henri.bergius@iki.fi',
            'role' => 'lead',
            'active' => 'no',
        ),
    ),
    'dependencies' => array
    (
        'org.openpsa.projects' => array(),
        'org.openpsa.core' => array(),
        'midcom.helper.datamanager2' => array(),
        'Calendar' => array
        (
            'channel' => 'pear.php.net',
        ),
        'midcom' => array(),
    ),
),
