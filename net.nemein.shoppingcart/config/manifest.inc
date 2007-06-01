'name' => 'net.nemein.shoppingcart',
/* if the module is a purecode one, then you cannot use it 
 * to create direct webpages.*/ 
'purecode' => false,
/* when using pear packaging, versions > 1 are stable */
'version' => '0.5.0',
'state' => 'beta',
/* use this to set default privileges */
'privileges' => array(),
/* use this to initialize your own MgdSchema classes */
'class_definitions' => array(),
/**
 * This is used by the PEAR packaging.
 */
'package.xml' => array
(
    'summary' => 'Simple shopping cart',
    'description' => 'Shopping cart to use with the product database',
    'maintainers' => array
    (
        'rambo' => array
        (
            'name' => 'Eero af Heurlin',
            'email' => 'eero.afheurlin@iki.fi',
            'role' => 'lead',
        ),
    ),
    /* Define what modules this module depends on.  */
    'dependencies' => Array
    (
        'midcom.helper.datamanager2' => array(),
        'org.openpsa.products' => array(),
        'org.openpsa.mail' => array(),
    ),
),

