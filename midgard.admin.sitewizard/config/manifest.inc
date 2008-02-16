'name' => 'midgard.admin.sitewizard',
'purecode' => false,
'version' => '2.1.0beta1',
'state' => 'beta',
'privileges' => array(),
'class_definitions' => array(),
'package.xml' => array
(
    'summary' => 'Site creation wizard',
    'description' => 'Tool for creating companies, sites and styles',
    'maintainers' => array
    (
        'bergie' => array
        (
            'name' => 'Henri Bergius',
            'email' => 'henri.bergius@iki.fi',
            'role' => 'lead',
        ),
        'w_i' => array
        (
            'name' => 'Jerry Jalava',
            'email' => 'jerry.jalava@incognito.fi',
            'role' => 'lead',
        ),
        'solt' => array
        (
            'name' => 'Marcin Sołtysiak',
            'email' => 'olga@olga.pl',
            'role' => 'developer',
        ),
    ),
    'dependencies' => array
    (
        'midgard.admin.sitegroup' => array(),
        'template_Midgard' => array(),
        'midcom' => array(),
    ),
),
