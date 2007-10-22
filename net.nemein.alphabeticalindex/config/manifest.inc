'name' => 'net.nemein.alphabeticalindex',
/* if the module is a purecode one, then you cannot use it 
 * to create direct webpages.*/ 
'purecode' => false,
/* when using pear packaging, versions > 1 are stable */
'version' => '0.0.1',
'state' => 'devel',
/* use this to set default privileges */
'privileges' => array(),
/* use this to initialize your own MgdSchema classes */
'class_definitions' => array
(
    'midcomdba.inc'
),
'watches' => array
(
    array
    (
        'classes' => array
        (
            'midcom_baseclasses_database_topic',
            'midcom_baseclasses_database_article',
        ),
        'operations' =>   MIDCOM_OPERATION_DBA_CREATE
                        | MIDCOM_OPERATION_DBA_UPDATE
                        | MIDCOM_OPERATION_DBA_DELETE,
    ),
),
/**
 * This is used by the PEAR packaging.
 */
'package.xml' => array
(
    'summary' => 'Alphabetical index',
    'description' => 'Lists defined content in alphabetical order',
    'maintainers' => array
    (
        'w_i' => array
        (
            'name' => 'Jerry Jalava',
            'email' => 'jerry.jalava@gmail.com',
            'role' => 'lead',
        ),
    ),
    /* Define what modules this module depends on.  */
    'dependencies' => Array
    (
        'midcom.helper.datamanager2' => array(),
    ),
),


