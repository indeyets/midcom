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
        /*
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
        */
            'create_tkk_host' => array
            (
                'class' => 'create_tkk_host',
                'src' => 'file:/midgard/admin/wizards/plugins/create_tkk_host.php',
                'name' => 'TKK Host Creation',
                'config' => array
                (
                    'sitewizard_path' => '/usr/share/php/midcom/static/data/midgard_admin_sitewizard.php',
                    'default_sitegroup_id' => 1,
                ),            
            ),
            'select_tkk_style' => array
            (
                'class' => 'select_tkk_style',
                'src' => 'file:/midgard/admin/wizards/plugins/select_tkk_style.php',
                'name' => 'TKK Style Selection',
                'config' => array
                (
                    'sitewizard_path' => '/usr/share/php/midcom/static/data/midgard_admin_sitewizard.php',
                ),            
            ),
            'select_tkk_structure' => array
            (
                'class' => 'select_tkk_structure',
                'src' => 'file:/midgard/admin/wizards/plugins/select_tkk_structure.php',
                'name' => 'TKK Structure Selection',
                'config' => array
                (
                    'sitewizard_path' => '/usr/share/php/midcom/static/data/midgard_admin_sitewizard.php',
                    'structure_config_path' => '/usr/share/midgard/svn/midcom-trunk/midgard.admin.wizards/config/templates/',
                ),            
            ),
            'create_tkk_website' => Array
            (
                'class' => 'create_tkk_website',
                'src' => 'file:/midgard/admin/wizards/plugins/create_tkk_website.php',
                'name' => 'TKK Website Creation',
                'config' => Array
                (
                    'sitewizard_path' => '/usr/share/php/midcom/static/data/midgard_admin_sitewizard.php',
                    
                    'creation_root_topic_guid' => '5c7c4a76761711dc96616575a3e41a5c1a5c',
                    /*
                    'creation_root_topic_parent_guid' => '6d3af3384be911dcb7b0b3bf4b275d0d5d0d',
                    'creation_root_topic_component' => 'net.nehmer.static',
                    'creation_root_topic_parameters' => array(),
                    */
                    'creation_root_group_guid' => 'e32da6065ac411dba4b95bbbb548039a039a',
                    //'creation_root_group_parent_guid' => '',
                    //'creation_root_group_name' => '',
                    
                    'verbose' => true,
                    'structure_config_path' => '/usr/share/midgard/svn/midcom-trunk/midgard.admin.wizards/config/templates/',
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