'name' => 'org.openpsa.mail',
'purecode' => true,
'privileges' =>  Array(),
'class_definitions' => Array(),
'version' => '2.0.6',
'state' => 'stable',
'package.xml' => Array(
    'summary' => 'Email sending and decoding library',
    'description' => 'Library for handling outgoing and incoming emails, including HTML emails and attachments',
    'maintainers' => Array(
        'rambo' => Array(
            'name' => 'Eero af Heurlin',
            'email' => 'eero.afheurlin@iki.fi',
            'role' => 'lead',
        ),
    ),
    'dependencies' => Array(
        'org.openpsa.helpers' => Array(),
        'Mail' => Array
        (
            'channel' => 'pear.php.net',
        ),
        'Mail_Mime' => Array
        (
            'channel' => 'pear.php.net',
        ),
    ),            
),