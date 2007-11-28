'name' => 'midcom.helper.datamanager2',
'version' => '1.0.4beta21',
'state' => 'beta',
'purecode' => true,
'package.xml' => array
(
    'summary' => 'Data abstraction and form rendering library',
    'description' => 'midcom.helper.datamanager2 is a MidCOM purecode component providing database abstraction and form processing features to MidCOM components.',
    'state' => 'beta',
    'maintainers' => array
    (
        'rambo' => array
        (
            'name' => 'Eero af Heurlin',
            'email' => 'eero.afheurlin@iki.fi',
            'role' => 'lead',
        ),
        'bergie' => array
        (
            'name' => 'Henri Bergius',
            'email' => 'henri.bergius@iki.fi',
            'role' => 'lead',
        ),
        'torben' => array
        (
            'name' => 'Torben Nehmer',
            'email' => 'torben@nehmer.net',
            'role' => 'lead',
            'active' => 'no',
        ),
        'adrenalin' => array
        (
            'name' => 'Arttu Manninen',
            'email' => 'arttu@kaktus.cc',
            'role' => 'developer',
        ),
        'w_i' => array
        (
            'name' => 'Jerry Jalava',
            'email' => 'jerry.jalava@gmail.com',
            'role' => 'lead',
        ),
    ),
    'dependencies' => array
    (
        'net.nehmer.markdown' => array (),
        'net.nemein.tag' => array (),
        'HTML_QuickForm' => array
        (
            'channel' => 'pear.php.net',
        ),
        'HTMLPurifier' => array
        (
            'channel' => 'htmlpurifier.org',
        ),
        'Text_CAPTCHA' => array
        (
            'channel' => 'pear.php.net',
        ),
        'JavaScript_Prototype' => array
        (
            'channel' => 'pearified.com',
        ),
        'midcom' => array (),
        'midcom.helper.imagepopup' => array (),
    ),
),
