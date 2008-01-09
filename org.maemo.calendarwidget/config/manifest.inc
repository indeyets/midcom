'name' => 'org.maemo.calendarwidget',
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
    'summary' => 'Community calendar for maemo platform',
    'description' => 'Community calendar for maemo platform',
    'maintainers' => array
    (
        'w_i' => array
        (
            'name' => 'Jerry Jalava',
            'email' => 'jerry.jalava@nemein.com',
            'role' => 'lead',
        ),
    ),
    /* Define what modules this module depends on.  */
    'dependencies' => array
    (
        'midcom' => array (),
        'org.maemo.calendarpanel' => array(),
    ),
),


