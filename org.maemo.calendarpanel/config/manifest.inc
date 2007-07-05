'name' => 'org.maemo.calendarpanel',
/* if the module is a purecode one, then you cannot use it 
 * to create direct webpages.*/ 
'purecode' => true,
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
    'summary' => 'Panel for Maemo community calendar',
    'description' => 'Panel for Maemo community calendar',
    'maintainers' => array
    (
        'username' => array
        (
            'name' => 'Jerry Jalava',
            'email' => 'jerry.jalava@nemein.net',
            'role' => 'lead',
        ),
    ),
    /* Define what modules this module depends on.  */
    'dependencies' => Array
    (
        'midcom' => array (),
    ),
),


