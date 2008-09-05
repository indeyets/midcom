/* The schema database to use */
'schemadb' => 'file:/midgard/admin/wizards/config/schemadb_default.inc',

/* The schema in the database to use to edit articles 
 * note the name here and in schemadb_default.inc
 * This is used when having created the complete crud component - not just the basic one. 
 * */
'schema' => 'default',

'plugin_groups' => array
(
    'sitewizard' => array
    (
        'title' => 'Site Wizard',
        'description' => 'Site Wizard',
        'plugins' => array 
        (
            'create_sitegroup' => array
            (
                'class' => 'default_create_sitegroup',
                'src' => 'file:/midgard/admin/wizards/plugins/default_create_sitegroup.php',
                'name' => 'Sitegroup creation',    
                'config' => array
                (
                    'sitewizard_path' => '__INSTALL_PREFIX__/share/midgard/setup/php/midgard_admin_sitewizard.php',
                ),        
            ),
            'create_host' => array
            (
                'class' => 'default_create_host',
                'src' => 'file:/midgard/admin/wizards/plugins/default_create_host.php',
                'name' => 'Host Creation',
                'config' => array
                (
                    'sitewizard_path' => '__INSTALL_PREFIX__/share/midgard/setup/php/midgard_admin_sitewizard.php',
                    'copy_host_port' => 8080,
                    'copy_host_name' => 'www.tky.fi',
                ),            
            ),
            'select_style' => array
            (
                'class' => 'default_select_style',
                'src' => 'file:/midgard/admin/wizards/plugins/default_select_style.php',
                'name' => 'Style Selection',
                'config' => array
                (
                    'sitewizard_path' => '__INSTALL_PREFIX__/share/midgard/setup/php/midgard_admin_sitewizard.php',
                    /*'show_style_templates' => array
                    (
                        'template_SimpleGray',
                        'template_Kubrick',
                    ),*/
                ),            
            ),
            'select_structure' => array
            (
                'class' => 'default_select_structure',
                'src' => 'file:/midgard/admin/wizards/plugins/default_select_structure.php',
                'name' => 'Structure Selection',
                'config' => array
                (
                    'sitewizard_path' => '__INSTALL_PREFIX__/share/midgard/setup/php/midgard_admin_sitewizard.php',
                    'structure_config_path' => 'file:/midgard/admin/wizards/config/templates/',
                ),            
            ),
            /*'configure_structure' => array
            (
                'class' => 'default_configure_structure',
                'src' => 'file:/midgard/admin/wizards/plugins/default_configure_structure.php',
                'name' => 'Structure Configuration',
                'config' => array
                (
                    'sitewizard_path' => '__INSTALL_PREFIX__/share/midgard/setup/php/midgard_admin_sitewizard.php',
                ),            
            ),*/
            'create_website' => Array
            (
                'class' => 'default_create_website',
                'src' => 'file:/midgard/admin/wizards/plugins/default_create_website.php',
                'name' => 'Website Creation',
                'config' => Array
                (
                    'sitewizard_path' => '__INSTALL_PREFIX__/share/midgard/setup/php/midgard_admin_sitewizard.php',
                    'verbose' => false,
                    'structure_config_path' => 'file:/midgard/admin/wizards/config/templates/',
                    'vhost_save_path' => '/etc/midgard/apache/vhosts/sitewizard/',
                    'notification_email' => 'henri.bergius@nemein.com',
                    'host_ip' => '82.130.62.227',
                    'mysql_db_user_pass' => 'midgard midgard midgard',
                    'document_root' => '/var/lib/midgard/vhosts/www.tky.fi/80/',
                    'midgard_root' => '/usr/lib/httpd/modules/midgard-root.php',
                    'staging2live' => false,
                ),
            ),
        ),    
    ),
),