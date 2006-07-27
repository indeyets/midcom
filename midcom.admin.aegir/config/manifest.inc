'name' => 'midcom.admin.aegir',
'purecode' => false,
'version' => '0.8.0',
'privileges' =>  Array(),
'class_definitions' => Array(),
'customdata' => array (
    'midcom.admin.aegir' => array (
        'portal' => array(),
        'menu' => array (
            'help' => array ('url' => 'help/test', 'text' => 'About Aegir'),
            'portal' => array ('url' => 'prefs', 'text' => 'Preferences'),
            'aegir' => array ('url'  => 'login/logout', 'text' => 'Log out'),
        ),
    ),   
),


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
    'midcom.helper.datamanager' => Array(),
    'midcom.helper.datamanager2' => Array(),
    ),  
),
