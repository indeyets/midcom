'name' => 'net.nemein.openid',
'icon' => 'net.nemein.openid/openid.gif',
'purecode' => true,
'version' => '0.0.1',
'state' => 'devel',
'privileges' => array(),
'class_definitions' => array(),
/**
 * This is used by the PEAR packaging.
 */
'package.xml' => array
(
    'summary' => 'OpenID authentication and sign-up system',
    'description' => 'OpenID authentication and sign-up system',
    'maintainers' => array
    (
        'bergie' => array
        (
            'name' => 'Henri Bergius',
            'email' => 'henri.bergius@iki.fi',
            'role' => 'lead',
        ),
    ),
    'dependencies' => Array
    (
        'net.nehmer.account' => array(),
        'Auth_OpenID' => array
        (
            'channel' => 'pear.php.net',
        ),
    ),
),


