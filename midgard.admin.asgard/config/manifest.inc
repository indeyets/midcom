'name' => 'midgard.admin.asgard',
'purecode' => true,
'version' => '2.0.0beta1',
'state' => 'devel',
'privileges' => array(),
'class_definitions' => array(),
/**
 * This is used by the PEAR packaging.
 */
'package.xml' => array
(
    'summary' => 'Next generation admin interface',
    'description' => 'Next generation admin interface',
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
            'email' => 'eero.afheurlin@iki.fi',
            'role' => 'lead',
        ),
        'adrenalin' => array
        (
            'name' => 'Arttu Manninen',
            'email' => 'arttu@kaktus.cc',
            'role' => 'lead',
        ),
        'solt' => array
        (
        	'name' => 'Marcin Sołtysiak',
        	'email' => 'olga@olga.pl',
        	'role' => 'developer',
        ),
    ),
    /* Define what modules this module depends on.  */
    'dependencies' => Array
    (
        'midcom.helper.datamanager2' => array(),
    ),
),


