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
                    'copy_host_port' => 8001,
                    'copy_host_name' => 'wwwcms.hut.fi',
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
                    'show_style_templates' => array
                    (
                        'template_SimpleGray',
                    ),
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
            'configure_tkk_structure' => array
            (
                'class' => 'configure_tkk_structure',
                'src' => 'file:/midgard/admin/wizards/plugins/configure_tkk_structure.php',
                'name' => 'TKK Structure Configuration',
                'config' => array
                (
                    'sitewizard_path' => '/usr/share/php/midcom/static/data/midgard_admin_sitewizard.php',
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
                    'creation_root_group_guid' => 'e32da6065ac411dba4b95bbbb548039a039a',
                    
                    'verbose' => false,
                    'structure_config_path' => '/usr/share/midgard/svn/midcom-trunk/midgard.admin.wizards/config/templates/',
                    'vhost_save_path' => '/usr/share/midgard/svn/midcom-trunk/midgard.admin.wizards/',
                    'notification_email' => 'juhana@nemein.com',
                    'host_ip' => '84.34.133.216',
                    'mysql_db_user_pass' => 'midgard midgard midgard',
                    'document_root' => '/var/lib/midgard/vhosts/devel-xen-devel.nemein.net/80/',
                    'midgard_root' => '/usr/lib/apache2/modules/midgard-root.php',
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