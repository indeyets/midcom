'name' => 'net.nemein.personnel',
'purecode' => false,
'version' => '1.2.0beta2',
'state' => 'beta',
'privileges' => array(),
'class_definitions' => array(),
'package.xml' => array
(
    'summary' => 'Personnel listing',
    'description' => 'Component to display persons in group',
    'maintainers' => array
    (
        'bergie' => array
        (
            'name' => 'Henri Bergius',
            'email' => 'henri.bergius@iki.fi',
            'role' => 'lead',
            'active' => 'no',
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
            'email' => 'arttu.manninen@protie.fi',
            'role' => 'lead',
            'active' => 'yes',
        ),
        'tarjei' => array
        (
            'name' => 'Tarje Huse',
            'email' => 'tarjei@nu.no',
            'role' => 'lead',
            'active' => 'yes',
        ),
        'solt' => array
        (
            'name' => 'Marcin Sołtysiak',
            'email' => 'olga@olga.pl',
            'role' => 'developer',
            'active' => 'yes',
        ),
    ),
    'dependencies' => array
    (
        'midcom.helper.datamanager2' => array
        (
            'min' => '1.2.3',
        ),
        'midcom' => array
        (
            'min' => '2.8.9',
        ),
        'midcom.helper.dm2config' => array(),
    ),
),
