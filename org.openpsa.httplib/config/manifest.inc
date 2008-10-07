'name' => 'org.openpsa.httplib',
'icon' => 'stock-icons/16x16/stock_internet.png',
'purecode' => true,
'version' => '8.09.0',
'state' => 'stable',
'privileges' => array(),
'class_definitions' => array(),
'package.xml' => array
(
    'summary' => 'HTTP client library',
    'description' => 'HTTP client library',
    'maintainers' => array
    (
        'bergie' => array
        (
            'name' => 'Henri Bergius',
            'email' => 'henri.bergius@iki.fi',
            'role' => 'lead',
        ),
        'rambo' => array
        (
            'name' => 'Eero af Heurlin',
            'email' => 'eero.afheurlin@nemein.com',
            'role' => 'lead',
        ),
    ),
    'dependencies' => array
    (
        'HTTP_request' => array
        (
            'channel' => 'pear.php.net',
        ),
        'Console_Getargs' => array
        (
            'channel' => 'pear.php.net',
        ),
        'midcom' => array(),
    ),
),
