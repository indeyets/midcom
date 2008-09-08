'name' => 'org.openpsa.invoices',
'icon' => 'stock-icons/16x16/stock_mail-open.png',
'purecode' => false,
'version' => '1.1.0beta1',
'state' => 'beta',
'privileges' => array(),
'class_definitions' => array
(
    'midcom_dba_classes.inc',
),
'package.xml' => array
(
    'summary' => 'Invoice management',
    'description' => 'Simple invoicing status tracker',
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
        'midcom.helper.datamanager' => array(),
        'org.openpsa.contactwidget' => array(),
        'org.openpsa.contacts' => array(),
        'org.openpsa.projects' => array(),
        'org.openpsa.relatedto' => array(),
        'midcom' => array(),
    ),
),
