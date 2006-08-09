'name' => 'org.openpsa.products',
'purecode' => false,
'version' => '0.0.1',
'state' => 'stable',
'privileges' =>  Array(),
'class_definitions' => Array('midcom_dba_classes.inc'),
/**
 * This is used by the PEAR packaging.
 */
'package.xml' => Array
(
    'summary' => 'Product database',
    'description' => 'Product database',
    'maintainers' => Array
    (
        'bergie' => Array
        (
            'name' => 'Henri Bergius',
            'email' => 'henri.bergius@iki.fi',
            'role' => 'lead',
        ),
    ),
/* Define what modules this module depends on.  */
    'dependencies' => Array
    (
        'midcom.helper.datamanager2' => Array(),
        'org.openpsa.contacts' => Array(),
    ),
),


