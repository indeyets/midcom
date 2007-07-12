'name' => 'net.nehmer.blog',
'version' => '1.4.0beta1',
'state' => 'stable',
'package.xml' => array
(
    'summary' => 'Weblog system',
    'description' => 'System for displaying and managing news posts',
    'maintainers' => array
    (
        'torben' => array
        (
            'name' => 'Torben Nehmer',
            'email' => 'torben@nehmer.net',
            'role' => 'lead',
            'active' => 'no',
        ),
        'bergie' => array
        (
            'name' => 'Henri Bergius',
            'email' => 'henri.bergius@iki.fi',
            'role' => 'lead',
            'active' => 'yes',
        ),
        'adrenalin' => array
        (
            'name' => 'Arttu Manninen',
            'email' => 'arttu.manninen@protie.fi',
            'role' => 'developer',
            'active' => 'yes',
        ),
    ),
    'dependencies' => array
    (
        'midcom.helper.datamanager2' => array(),
        'net.nehmer.comments'        => array(),
        'org.openpsa.mail'           => array(),
        'net.nemein.rss'             => array(),
        'XML_RPC'                    => array
        (
            'channel' => 'pear.php.net',
        ),
    ),
),

