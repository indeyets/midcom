'name' => 'org.openpsa.invoices',
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
