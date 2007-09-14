'name' => 'org.maemo.gforgeprofileupdater',
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
    'summary' => 'MidCOM module org.maemo.gforgeprofileupdater',
    'description' => 'MidCOM module org.maemo.gforgeprofileupdater',
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
        'soap' => array ('channel' => 'pear.php.net'),
    ),
),
'watches' => array
(
    array
    (
        'classes' => array
        (
            'midcom_baseclasses_database_person',
            'midcom_org_openpsa_person',
        ),
        'operations' => MIDCOM_OPERATION_DBA_ALL,
    ),
),
