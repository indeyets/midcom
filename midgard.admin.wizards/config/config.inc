/* The schema database to use */
'schemadb' => 'file:/midgard/admin/wizards/config/schemadb_default.inc',

/* The schema in the database to use to edit articles 
 * note the name here and in schemadb_default.inc
 * This is used when having created the complete crud component - not just the basic one. 
 * */
'schema' => 'default',

'plugin_groups' => array
(
    'tkk_sitewizard' => array
    (
        'title' => 'TKK Sitewizard',
        'description' => 'TKK Sitewizard',
        'plugins' => array 
        (
            'create_tkk_sitegroup' => array
            (
                'class' => 'create_tkk_sitegroup',
                'src' => 'file:/midgard/admin/wizards/plugins/create_tkk_sitegroup.php',
                'name' => 'TKK Sitegroup creation',    
                'config' => array
                (
                    'sitewizard_path' => '/usr/share/php/midcom/static/data/midgard_admin_sitewizard.php',
                ),        
            ),
            'create_tkk_host' => array
            (
                'class' => 'create_tkk_host',
                'src' => 'file:/midgard/admin/wizards/plugins/create_tkk_host.php',
                'name' => 'TKK Host Creation',
                'config' => array
                (
                    'sitewizard_path' => '/usr/share/php/midcom/static/data/midgard_admin_sitewizard.php',
                ),            
            ),
            'create_team_home' => Array
            (
                'class' => 'midgard_admin_sitewizard_plugin',
                'src' => 'file:/net/nemein/teams/plugins/midgard_admin_sitewizard_plugin.php',
                'name' => 'TKK Structure Creation',
                'config' => Array
                (
                /*
                    'sitewizard_path' => '/usr/share/php/midcom/static/data/midgard_admin_sitewizard.php',
                    'host_guid' => '983e66725acd11db845a197adaa843af43af',
                    'creation_root_topic_guid' => '6d3af3384be911dcb7b0b3bf4b275d0d5d0d',
                    'creation_root_topic_parent_guid' => '6d3af3384be911dcb7b0b3bf4b275d0d5d0d',
                    'creation_root_topic_component' => 'net.nehmer.static',
                    'creation_root_topic_parameters' => array(),
                    'creation_root_group_guid' => '94f058364f0f11dc93f803ebc4b67c0c7c0c',
                    'creation_root_group_parent_guid' => '',
                    'creation_root_group_name' => '',
                    'verbose' => true,
                    'structure_config_path' => '/usr/share/midgard/svn/midcom-trunk/net.nemein.teams/plugins/leet_team_home_structure.inc',
                    */
                ),
            ),

        ),
        
    ),
    
    'some_wizard' => array 
    (
        'title' => 'Some Wizard',
        'description' => 'Some Wizard',
        'plugins' => array
        (
        
        ),
    ),
),