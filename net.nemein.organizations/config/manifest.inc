'name' => 'net.nemein.organizations',
'icon' => 'stock-icons/16x16/stock_home.png',
'purecode' => false,
'version' => '1.1.0beta1',
'state' => 'beta',
'privileges' => array(),
'class_definitions' => array(),
'package.xml' => array
(
    'summary' => 'Organization listing',
    'description' => 'Component to display groups',
    'maintainers' => array
    (
        'bergie' => array
        (
            'name' => 'Henri Bergius',
            'email' => 'henri.bergius@iki.fi',
            'role' => 'lead',
            'active' => 'yes',
        ),
        'netblade' => array
        (
            'name' => 'Oskari Kokko',
            'email' => 'oskari.kokko@iki.fi',
            'role' => 'lead',
            'active' => 'yes',
        ),
    ),
    'dependencies' => array
    (
        'midcom.helper.datamanager2' => array(),
        'org.routamc.positioning' => array(),
        'midcom' => array(),
    ),
),
