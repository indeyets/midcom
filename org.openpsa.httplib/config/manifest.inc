'name' => 'org.openpsa.httplib',
'purecode' => true,
'version' => '1.1.0beta1',
'state' => 'beta',
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
