'name' => 'pl.olga.windguru',
'purecode' => false,
'version' => '0.1.0beta1',
'state' => 'beta',
'privileges' => array(),
'class_definitions' => array
(
    'midcom_dba_classes.inc',
),
'package.xml' => array
(
    'summary' => 'Place Windguru (tm) forecast on your page',
    'description' => 'Quick interface for placing Windguru (tm) forecats on your page. Registered Windguru users can place up to 10 forecats',
    'maintainers' => array
    (
        'solt' => array
        (
            'name' => 'Marcin Sołtysiak',
            'email' => 'olga@olga.pl',
            'role' => 'lead',
        ),
    ),
    'dependencies' => array
    (
        'midcom.helper.datamanager2' => array(),
        'midcom.helper.dm2config' => array(),
        'midcom' => array(),
    ),
),
