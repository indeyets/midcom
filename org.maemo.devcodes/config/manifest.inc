'name' => 'org.maemo.devcodes',
/* if the module is a purecode one, then you cannot use it 
 * to create direct webpages.*/ 
'purecode' => false,
/* when using pear packaging, versions > 1 are stable */
'version' => '0.0.1',
'state' => 'devel',
/* use this to set default privileges */
'privileges' => array
(
    'manage' => MIDCOM_PRIVILEGE_DENY,
    'read' => MIDCOM_PRIVILEGE_DENY,
),
/* use this to initialize your own MgdSchema classes */
'class_definitions' => array('midcom_dba_classes.inc'),
/**
 * This is used by the PEAR packaging.
 */
'package.xml' => array
(
    'summary' => 'Manage developer discount codes',
    'description' => 'Manage developer discount codes',
    'maintainers' => array
    (
        'rambo' => array
        (
            'name' => 'Eero af Heurlin',
            'email' => 'eero.afheurlin@nemein.com',
            'role' => 'lead',
        ),
    ),
    /* Define what modules this module depends on.  */
    'dependencies' => Array
    (
        'midcom.helper.datamanager2' => array(),
        'org.openpsa.directmarketing' => array(),
        'org.openpsa.contacts' => array(),
    ),
),


