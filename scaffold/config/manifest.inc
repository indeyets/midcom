'name' => '${module}',
/* if the module is a purecode one, then you cannot use it 
 * to create direct webpages.*/ 
'purecode' => false,
/* when using pear packaging, versions > 1 are stable */
'version' => '0.0.1',
'state' => 'stable',
/* use this to set default privileges */
'privileges' =>  Array(),
/* use this to initialize your own MgdSchema classes */
'class_definitions' => Array(),
/**
 * This is used by the PEAR packaging.
 */
'package.xml' => Array
(
    'summary' => '${module_description}',
    'description' => '${module_description}',
    'maintainers' => Array
    (
        'torben' => Array
        (
            'name' => 'Insert your name here',
            'email' => 'and your email here',
            'role' => 'lead',
        ),
    ),
/* Define what modules this module depends on.  */
    'dependencies' => Array
    (
        'midcom.helper.datamanager2' => Array(),
    ),
),


