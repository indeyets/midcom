'name' => 'org.openpsa.documents',
'icon' => 'stock-icons/16x16/folder.png',
'purecode' => false,
'privileges' => array(),
'class_definitions' => array
(
    'midcom_dba_classes.inc',
),
'version' => '2.1.0beta1',
'package.xml' => array
(
    'summary' => 'Document management',
    'description' => 'Organize and manage documents with metadata, search inside documents.',
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
        'org.openpsa.core' => array(),
        'org.openpsa.helpers' => array(),
        'midcom.helper.datamanager' => array(),
        'midcom' => array(),
    ),
),
'state' => 'beta',
