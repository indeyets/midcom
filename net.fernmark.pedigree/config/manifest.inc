'name' => 'net.fernmark.pedigree',
'purecode' => false,
'version' => '0.1.0beta1',
'state' => 'beta',
'privileges' => array(),
'class_definitions' => array
(
    'midcom_dba_classes.inc',
),
'package.xml' => array
(
    'summary' => 'Database of dog pedigrees',
    'description' => 'Database of dog pedigrees',
    'maintainers' => array
    (
        'rambo' => array
        (
            'name' => 'Eero af Heurlin',
            'email' => 'rambo@iki.fi',
            'role' => 'lead',
        ),
    ),
    'dependencies' => array
    (
        'midcom.helper.datamanager2' => array(),
        'org.openpsa.contacts' => array(),
    ),
),
