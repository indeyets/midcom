// TKK lab template
'tkk_lab' => array
(
    // Basic metadata for the site template
    'name' => 'tkk_lab',
    'title' => 'TKK lab', 
    'description' => 'Default structure for laboratories',
    'credits' => 'Henri Bergius, the Midgard Project',
    'license' => 'LGPL',
    'examples' => array
    (
    ),

    // Groups that should be created / used for this site template      
    'groups' => array
    (
        'administrators' => array
        (
            'name' => '__HOSTTITLE__ administrators',
            'subgroups' => array
            (
                'authors' => array
                (
                    'name' => 'authors',
                ),
                'personnel' => array
                (
                    'name' => 'personnel'
                ),
            ),
        ),
    ),
    
    // DM2 schema
    'schemadb' => array
    (
        'settings' => array
        (
            'description' => 'settings',
            'fields'      => array
            (
                'mailto' => Array
                (
                    'title' => 'Palautelomakkeen vastaanottaja (email-osoite)',
                    'storage' => 'null',
                    'type' => 'text',
                    'widget' => 'text',
                ),
                /*
                'anonymous' => Array
                (
                    'title' => 'Allow anonymous',
                    'storage' => 'null',
                    'type' => 'boolean',
                    'widget' => 'checkbox',
                ),
                */
            ),
        ),
    ),
    
    // Root node definition		
    'root' => array
    (
        'name' => '__HOSTNAME__',
        'title' => '__HOSTTITLE__',
        'component' => 'net.nehmer.static',
        'create_index' => true,
        'use_inherited_style' => false,
        'style_inherit' => true,
        'acl' => array
        (
            /*
            'authors' => array
            (
                'midgard:owner' => MIDCOM_PRIVILEGE_ALLOW,
            ),
            */
        ),
        'parameters' => array
        (

        ),
        // Child node definitions
        'nodes' => array
        (
            'fi' => array
            (
                'name' => 'fi',
                'title' => 'Etusivu',
                'component' => 'net.nehmer.static',
                'create_index' => true,
                'acl' => array
                (
                ),
                'parameters' => array
                (
                    'net.nehmer.static' => array
                    (
                        'schemadb' => '/sitegroup-config/net.nehmer.static/schemadb_frontpage_lab',
                    ),
                ),
                'nodes' => array
                (
                    'ajankohtaista' => array
                    (
                        'name' => 'ajankohtaista',
                        'title' => 'Ajankohtaista',
                        'component' => 'net.nehmer.blog',
                        'symlink_source_id' => 'news_fi',
                        'style' => '/template_laboratorio/sitemap',
                        'acl' => array
                        (

                        ),
                        'parameters' => array
                        (
 
                        ),
                    ),
                    'opinnot' => array
                    (
                        'name' => 'opinnot',
                        'title' => 'Opinnot',
                        'component' => 'net.nehmer.static',
                        'create_index' => true,
                        'acl' => array
                        (
                        ),
                        'parameters' => array
                        (
                        ),
                        // Child node definitions
                        'nodes' => array
                        (
                            'moduulit' => array
                            (
                                'name' => 'moduulit',
                                'title' => 'Moduulit',
                                'component' => 'net.nehmer.static',
                                'create_index' => true,
                                'acl' => array
                                (
                
                                ),
                                'parameters' => array
                                (
                
                                ),
                            ),
                            'kurssit' => array
                            (
                                'name' => 'kurssit',
                                'title' => 'Kurssit',
                                'component' => 'net.nehmer.static',
                                'create_index' => true,
                                'acl' => array
                                (
                
                                ),
                                'parameters' => array
                                (
                
                                ),
                            ),
                        ),
                    ),
                    'tutkimusryhmat' => array
                    (
                        'name' => 'tutkimusryhmat',
                        'title' => 'Tutkimusryhmät',
                        'component' => 'net.nehmer.static',
                        'create_index' => true,
                        'acl' => array
                        (
                        ),
                        'parameters' => array
                        (
            
                        ),
        
                        // Child node definitions
                        'nodes' => array
                        (
                            'tutkimusryhma1' => array
                            (
                                'name' => 'tutkimusryhma1',
                                'title' => 'Tutkimusryhmä1',
                                'component' => 'net.nehmer.static',
                                'create_index' => true,
                                'acl' => array
                                (
                
                                ),
                                'parameters' => array
                                (
                
                                ),
                            ),
                            'tutkimusryhma2' => array
                            (
                                'name' => 'tutkimusryhma2',
                                'title' => 'Tutkimusryhmä2',
                                'component' => 'net.nehmer.static',
                                'create_index' => true,
                                'acl' => array
                                (
                
                                ),
                                'parameters' => array
                                (
                
                                ),
                            ),
                        ),
                    ),
                    'julkaisut' => array
                    (
                        'name' => 'julkaisut',
                        'title' => 'Julkaisut',
                        'component' => 'net.nehmer.static',
                        'create_index' => true,
                        'acl' => array
                        (
                        ),
                        'parameters' => array
                        (
            
                        ),
                    ),
                    'sivukartta' => array
                    (
                        'name' => 'sivukartta',
                        'title' => 'Sivukartta',
                        'component' => 'de.linkm.sitemap',
                        'nav_noentry' => true,
                        'style' => '/template_laboratorio/sitemap',
                        'acl' => array
                        (
                        ),
                        'parameters' => array
                        (
            
                        ),
                    ),
                    'hakemisto' => array
                    (
                        'name' => 'hakemisto',
                        'title' => 'Hakemisto',
                        'component' => 'net.nemein.alphabeticalindex',
                        'nav_noentry' => true,
                        'acl' => array
                        (
                        ),
                        'parameters' => array
                        (
            
                        ),
                    ),
                    'palaute' => array
                    (
                        'name' => 'palaute',
                        'title' => 'Palaute',
                        'component' => 'no.odindata.quickform',
                        'nav_noentry' => true,
                        'acl' => array
                        (
                        ),
                        'parameters' => array
                        (
                            'no.odindata.quickform' => array
                            (
                                'mail_address_to' => '__SCHEMA_mailto',
                            ),
                        ),
                    ),
                    'laitos' => array
                    (
                        'name' => 'laitos',
                        'title' => 'Laitos',
                        'component' => 'net.nehmer.static',
                        'create_index' => true,
                        'acl' => array
                        (
                        ),
                        'parameters' => array
                        (
            
                        ),
        
                        // Child node definitions
                        'nodes' => array
                        (
                            'yhteystiedot_ja_sijainti' => array
                            (
                                'name' => 'yhteystiedot_ja_sijainti',
                                'title' => 'Yhteystiedot ja sijainti',
                                'component' => 'net.nehmer.static',
                                'create_index' => true,
                                'acl' => array
                                (
                                ),
                                'parameters' => array
                                (
                
                                ),
                            ),                           
                            'henkilokunnan_yhteystiedot' => array
                            (
                                'name' => 'henkilokunnan_yhteystiedot',
                                'title' => 'Henkilokunnan yhteystiedot',
                                'component' => 'net.nemein.personnel',
                                'acl' => array
                                (
        
                                ),
                                'parameters' => array
                                (
                                    'net.nemein.personnel' => array
                                    (
                                        'group' => '__GROUPGUID_personnel',
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
            'en' => array
            (
                'name' => 'en',
                'title' => 'Home',
                'component' => 'net.nehmer.static',
                'create_index' => true,
                'acl' => array
                (
                ),
                'parameters' => array
                (
                    'net.nehmer.static' => array
                    (
                        'schemadb' => '/sitegroup-config/net.nehmer.static/schemadb_frontpage_lab',
                    ),
                ),         
                // en nodes  
                'nodes' => array
                (
                    'current_affairs' => array
                    (
                        'name' => 'current_affairs',
                        'title' => 'Current affairs',
                        'component' => 'net.nehmer.blog',
                        'symlink_target_id' => 'news_fi',
                        'style' => '/template_laboratorio/sitemap',
                        'acl' => array
                        (

                        ),
                        'parameters' => array
                        (

                        ),
                    ),
                    'studies' => array
                    (
                        'name' => 'studies',
                        'title' => 'Studies',
                        'component' => 'net.nehmer.static',
                        'create_index' => true,
                        'acl' => array
                        (
                        ),
                        'parameters' => array
                        (
                
                        ),
                        // Child node definitions
                        'nodes' => array
                        (
                            'modules' => array
                            (
                                'name' => 'modules',
                                'title' => 'Modules',
                                'component' => 'net.nehmer.static',
                                'create_index' => true,
                                'acl' => array
                                (
                
                                ),
                                'parameters' => array
                                (
                
                                ),
                            ),
                            'courses' => array
                            (
                                'name' => 'courses',
                                'title' => 'Courses',
                                'component' => 'net.nehmer.static',
                                'create_index' => true,
                                'acl' => array
                                (
                
                                ),
                                'parameters' => array
                                (
                
                                ),
                            ),
                        ),
                    ),
                    'research' => array
                    (
                        'name' => 'research',
                        'title' => 'Research',
                        'component' => 'net.nehmer.static',
                        'create_index' => true,
                        'acl' => array
                        (
                        ),
                        'parameters' => array
                        (
            
                        ),
        
                        // Child node definitions
                        'nodes' => array
                        (
                            'research_group1' => array
                            (
                                'name' => 'research_group1',
                                'title' => 'Research group 1',
                                'component' => 'net.nehmer.static',
                                'create_index' => true,
                                'acl' => array
                                (
                
                                ),
                                'parameters' => array
                                (
                
                                ),
                            ),
                            'research_group2' => array
                            (
                                'name' => 'research_group2',
                                'title' => 'Research group 2',
                                'component' => 'net.nehmer.static',
                                'create_index' => true,
                                'acl' => array
                                (
                
                                ),
                                'parameters' => array
                                (
                
                                ),
                            ),
                        ),
                    ),
                    'publications' => array
                    (
                        'name' => 'publications',
                        'title' => 'Publications',
                        'component' => 'net.nehmer.static',
                        'create_index' => true,
                        'acl' => array
                        (
                        ),
                        'parameters' => array
                        (
            
                        ),
                    ),
                    'sitemap' => array
                    (
                        'name' => 'sitemap',
                        'title' => 'Sitemap',
                        'component' => 'de.linkm.sitemap',
                        'nav_noentry' => true,
                        'style' => '/template_laboratorio/sitemap',
                        'acl' => array
                        (
                        ),
                        'parameters' => array
                        (
            
                        ),
                    ),
                    'index' => array
                    (
                        'name' => 'index',
                        'title' => 'Index',
                        'component' => 'net.nemein.alphabeticalindex',
                        'nav_noentry' => true,
                        'acl' => array
                        (
                        ),
                        'parameters' => array
                        (
            
                        ),
                    ),
                    'feedback' => array
                    (
                        'name' => 'feedback',
                        'title' => 'Feedback',
                        'component' => 'no.odindata.quickform',
                        'nav_noentry' => true,
                        'acl' => array
                        (
                        ),
                        'parameters' => array
                        (
                            'no.odindata.quickform' => array
                            (
                                'mail_address_to' => '__SCHEMA_mailto',
                            ),
                        ),
                    ),
                    'departments' => array
                    (
                        'name' => 'departments',
                        'title' => 'Departments',
                        'component' => 'net.nehmer.static',
                        'create_index' => true,
                        'acl' => array
                        (
                        ),
                        'parameters' => array
                        (
                
                        ),
            
                        // Child node definitions
                        'nodes' => array
                        (
                            'contact_information_and_location' => array
                            (
                                'name' => 'contact_information_and_location',
                                'title' => 'Contact Information and location',
                                'component' => 'net.nehmer.static',
                                'create_index' => true,
                                'acl' => array
                                (
                                ),
                                'parameters' => array
                                (
                        
                                ),
                            ),
                            'personnel' => array
                            (
                                'name' => 'personnel',
                                'title' => 'Personnel',
                                'component' => 'net.nemein.personnel',
                                'acl' => array
                                (
        
                                ),
                                'parameters' => array
                                (
                                    'net.nemein.personnel' => array
                                    (
                                        'group' => '__GROUPGUID_personnel',
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
            'sv' => array
            (
                'name' => 'sv',
                'title' => 'Startsida',
                'component' => 'net.nehmer.static',
                'create_index' => true,
                'acl' => array
                (
                ),
                'parameters' => array
                (
                    'net.nehmer.static' => array
                    (
                        'schemadb' => '/sitegroup-config/net.nehmer.static/schemadb_frontpage_lab',
                    ),
                ),   
                // sv nodes 
                'nodes' => array
                (
                    'aktuellt' => array
                    (
                        'name' => 'aktuellt',
                        'title' => 'Aktuellt',
                        'component' => 'net.nehmer.blog',
                        'symlink_target_id' => 'news_fi',
                        'style' => '/template_laboratorio/news',
                        'acl' => array
                        (

                        ),
                        'parameters' => array
                        (

                        ),
                    ),
                    'studier' => array
                    (
                        'name' => 'studier',
                        'title' => 'Studier',
                        'component' => 'net.nehmer.static',
                        'create_index' => true,
                        'acl' => array
                        (
                        ),
                        'parameters' => array
                        (
                
                        ),
                        // Child node definitions
                        'nodes' => array
                        (
                            'moduler' => array
                            (
                                'name' => 'moduler',
                                'title' => 'Moduler',
                                'component' => 'net.nehmer.static',
                                'create_index' => true,
                                'acl' => array
                                (
                
                                ),
                                'parameters' => array
                                (
                
                                ),
                            ),
                            'kurser' => array
                            (
                                'name' => 'kurser',
                                'title' => 'Kurser',
                                'component' => 'net.nehmer.static',
                                'create_index' => true,
                                'acl' => array
                                (
                
                                ),
                                'parameters' => array
                                (
                
                                ),
                            ),
                        ),
                    ),
                    'forskning' => array
                    (
                        'name' => 'forskning',
                        'title' => 'Forskning',
                        'component' => 'net.nehmer.static',
                        'create_index' => true,
                        'acl' => array
                        (
                        ),
                        'parameters' => array
                        (
            
                        ),
        
                        // Child node definitions
                        'nodes' => array
                        (
                            'forskningsomrade' => array
                            (
                                'name' => 'forskningsomrade',
                                'title' => 'Forskningsområde',
                                'component' => 'net.nehmer.static',
                                'create_index' => true,
                                'acl' => array
                                (
                
                                ),
                                'parameters' => array
                                (
                
                                ),
                            ),
                            'projekt' => array
                            (
                                'name' => 'projekt',
                                'title' => 'Projekt',
                                'component' => 'net.nehmer.static',
                                'create_index' => true,
                                'acl' => array
                                (
                
                                ),
                                'parameters' => array
                                (
                
                                ),
                            ),
                        ),
                    ),
                    'publikationer' => array
                    (
                        'name' => 'publikationer',
                        'title' => 'Publikationer',
                        'component' => 'net.nehmer.static',
                        'create_index' => true,
                        'acl' => array
                        (
                        ),
                        'parameters' => array
                        (
            
                        ),
                    ),
                    'sidkart' => array
                    (
                        'name' => 'sidkart',
                        'title' => 'Sidkart',
                        'component' => 'de.linkm.sitemap',
                        'nav_noentry' => true,
                        'style' => '/template_laboratorio/sitemap',
                        'acl' => array
                        (
                        ),
                        'parameters' => array
                        (
            
                        ),
                    ),
                    'index' => array
                    (
                        'name' => 'index',
                        'title' => 'Index',
                        'component' => 'net.nemein.alphabeticalindex',
                        'nav_noentry' => true,
                        'acl' => array
                        (
                        ),
                        'parameters' => array
                        (
            
                        ),
                    ),
                    'feedback' => array
                    (
                        'name' => 'feedback',
                        'title' => 'Feedback',
                        'component' => 'no.odindata.quickform',
                        'nav_noentry' => true,
                        'acl' => array
                        (
                        ),
                        'parameters' => array
                        (
                            'no.odindata.quickform' => array
                            (
                                'mail_address_to' => '__SCHEMA_mailto',
                            ),
                        ),
                    ),
                    'institutionen' => array
                    (
                        'name' => 'institutionen',
                        'title' => 'Institutionen',
                        'component' => 'net.nehmer.static',
                        'create_index' => true,
                        'acl' => array
                        (
                        ),
                        'parameters' => array
                        (
                
                        ),
            
                        // Child node definitions
                        'nodes' => array
                        (
                            'kontaktuppgifter' => array
                            (
                                'name' => 'kontaktuppgifter',
                                'title' => 'Kontaktuppgifter',
                                'component' => 'net.nehmer.static',
                                'create_index' => true,
                                'acl' => array
                                (
                                ),
                                'parameters' => array
                                (
                        
                                ),
                            ),
                            'personal' => array
                            (
                                'name' => 'personal',
                                'title' => 'Personal',
                                'component' => 'net.nemein.personnel',
                                'acl' => array
                                (
        
                                ),
                                'parameters' => array
                                (
                                    'net.nemein.personnel' => array
                                    (
                                        'group' => '__GROUPGUID_personnel',
                                    ),
                                ),
                            ),
                        ),
                    ),                
                ),        
            ),
        ),
    ),
),

