'name' => 'com.magnettechnologies.contactgrabber',
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
    'license' => 'GPL',
    'summary' => 'Extensive package that can be used to grab user contact lists from the most important email and social networking sites: Rediff, Orkut, MySpace, Yahoo, Gmail and MSN Messenger.',
    'description' => 'Extensive package that can be used to grab user contact lists from the most important email and social networking sites: Rediff, Orkut, MySpace, Yahoo, Gmail and MSN Messenger.',
    'maintainers' => array
    (
        'juhana' => array
        (
            'name' => 'Juhana Hirvilahti',
            'email' => 'juhana@nemein.com',
            'role' => 'lead',
        ),
    ),
    /* Define what modules this module depends on.  */
    'dependencies' => Array
    (
        'midcom.helper.datamanager2' => array(),
    ),
),


