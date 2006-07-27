'name' => 'midcom.admin.content2',
'description' => 'This module handles the administration of other modules.',
'purecode' => true,
'version' => '0.4.0',
'privileges' =>  Array(),
'class_definitions' => Array(),
'package.xml' => Array(  
    'license' => 'GPL',  
    'maintainers' => Array( 
        'tarjei' => Array(  
            'name' => 'Tarjei Huse',  
            'email' => 'tarjei@nu.no',  
            'role' => 'lead',  
        ),  
    ),  
    'dependencies' => Array(
    'midcom.admin.aegir' => array(),
    'midcom.admin.simplecontent' => array(),
    'no.bergfald.rcs' => array(),
    'midcom.helper.datamanager' => Array(),
    'midcom.helper.datamanager2' => Array(),
    ),  
),
