'name' => 'org.openpsa.invoices',
'purecode' => false,
'version' => '1.0.2',
'privileges' =>  Array(),
'class_definitions' => Array('midcom_dba_classes.inc'),
'package.xml' => Array(
    'summary' => 'Invoice management',
    'description' => 'Simple invoicing status tracker',
    'maintainers' => Array(
        'bergie' => Array(
            'name' => 'Henri Bergius',
            'email' => 'henri.bergius@iki.fi',
            'role' => 'lead',
        ),
    ),
    'dependencies' => Array(
        'midcom.helper.datamanager' => Array(),
        'org.openpsa.contactwidget' => Array(),
        'org.openpsa.contacts' => Array(),
        'org.openpsa.projects' => Array(),
        'org.openpsa.relatedto' => Array(),
    ),
),
