'name' => 'org.openpsa.mail',
'icon' => 'stock-icons/16x16/stock_mail-open.png',
'purecode' => true,
'privileges' => array(),
'class_definitions' => array(),
'version' => '2.1.0beta1',
'state' => 'beta',
'package.xml' => array
(
    'summary' => 'Email sending and decoding library',
    'description' => 'Library for handling outgoing and incoming emails, including HTML emails and attachments',
    'maintainers' => array
    (
        'rambo' => array
        (
            'name' => 'Eero af Heurlin',
            'email' => 'eero.afheurlin@iki.fi',
            'role' => 'lead',
        ),
    ),
    'dependencies' => array
    (
        'Mail' => array
        (
            'channel' => 'pear.php.net',
        ),
        'Mail_Mime' => array
        (
            'channel' => 'pear.php.net',
        ),
        'Net_SMTP' => array
        (
            'channel' => 'pear.php.net',
        ),
        'midcom' => array(),
    ),
),
