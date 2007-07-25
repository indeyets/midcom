'name' => 'org.maemo.socialnews',
/* if the module is a purecode one, then you cannot use it 
 * to create direct webpages.*/ 
'purecode' => false,
/* when using pear packaging, versions > 1 are stable */
'version' => '0.0.1',
'state' => 'devel',
/* use this to set default privileges */
'privileges' => array(),
/* use this to initialize your own MgdSchema classes */
'class_definitions' => array(),
/**
 * This is used by the PEAR packaging.
 */
'package.xml' => array
(
    'summary' => 'Social news aggregator',
    'description' => 'Social news aggregator',
    'maintainers' => array
    (
        'username' => array
        (
            'name' => 'Firstname Lastname',
            'email' => 'user@example.net',
            'role' => 'lead',
        ),
    ),
    /* Define what modules this module depends on.  */
    'dependencies' => Array
    (
        'midcom.helper.datamanager2' => array(),
    ),
),


