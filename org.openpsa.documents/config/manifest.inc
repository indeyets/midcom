'name' => 'org.openpsa.documents',
'purecode' => false,
'privileges' =>  Array(),
'class_definitions' => Array('midcom_dba_classes.inc'),
'version' => '2.0.0',
'package.xml' => Array(
    'summary' => 'Document management',
    'description' => 'Organize and manage documents with metadata, search inside documents.',
    'maintainers' => Array(
        'bergie' => Array(
            'name' => 'Henri Bergius',
            'email' => 'henri.bergius@iki.fi',
            'role' => 'lead',
        ),  
    ),
    'dependencies' => Array(
        'org.openpsa.core'          => Array(),
        'org.openpsa.helpers'       => Array(),        
        'midcom.helper.datamanager' => Array(),
    ),
),
