'name' => 'net.fernmark.pedigree',
/* if the module is a purecode one, then you cannot use it 
 * to create direct webpages.*/ 
'purecode' => false,
/* when using pear packaging, versions > 1 are stable */
'version' => '0.0.1',
'state' => 'devel',
/* use this to set default privileges */
'privileges' => array(),
/* use this to initialize your own MgdSchema classes */
'class_definitions' => array('midcom_dba_classes.inc'),
/**
 * This is used by the PEAR packaging.
 */
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
    /* Define what modules this module depends on.  */
    'dependencies' => Array
    (
        'midcom.helper.datamanager2' => array(),
        'org.openpsa.contacts'       => array(),
    ),
),


