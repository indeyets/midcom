'name' => 'fi.hut.htmlimport',
'purecode' => true,
'version' => '1.0.0',
'state' => 'stable',
'privileges' => array(),
'class_definitions' => array(),
'package.xml' => array
(
    'license' => 'GPL',
    'summary' => 'Helper to import HTML pages to MidCOM hierarchy',
    'description' => 'Supports multiple rulesets for matching regular or xpath -expressions into DM2 field values',
    'maintainers' => array
    (
        'rambo' => array
        (
            'name' => 'Eero af Heurlin',
            'email' => 'eero.afheurlin@nemein.com',
            'role' => 'lead',
        ),
    ),
    'dependencies' => array
    (
        'midcom' => array(),
        'net.nehmer.static' => array(),
        'midcom.helper.datamanager2' => array(),
        'HTMLPurifier' => array
        (
            'channel' => 'htmlpurifier.org',
        ),
    ),
),
