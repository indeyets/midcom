'name' => 'no.odindata.quickform2',
'description' => 'Submit forms to email ',
'purecode' => false,
'version' => '1.2.0beta1',
'state' => 'beta',
'privileges' => array (),
'class_definitions' => array (),
'package.xml' => array
(
    'summary' => 'Quickform module for mailing forms. ',
    'description' => 'Quickform module for mailing forms',
    'maintainers' => array
    (
        'tarjei' => array
        (
            'name' => 'Tarjei Huse',
            'email' => 'tarjei@nu.no',
            'role' => 'lead',
        ),
        'adrenalin' => array
        (
            'name' => 'Arttu Manninen',
            'email' => 'arttu@kaktus.cc',
            'role' => 'developer',
        ),
    ),
    'dependencies' => array
    (
        'midcom.helper.datamanager2' => array (),
        'midcom.helper.dm2config' => array (),
        'midcom' => array (),
        'Net_SMTP' => array
        (
            'channel' => 'pear.php.net',
        ),
    ),
),
