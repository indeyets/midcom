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
                    'title' => 'mailto',
                    'storage' => 'null',
                    'type' => 'text',
                    'widget' => 'text',
                ),
                'anonymous' => Array
                (
                    'title' => 'Allow anonymous',
                    'storage' => 'null',
                    'type' => 'text',
                    'widget' => 'text',
                ),
            ),
        ),
    ),

    
    // Root node definition		
    'root' => array
    (
        'name' => '__HOSTNAME__',
        'title' => '__HOSTTITLE__',
        'component' => 'net.nehmer.static',
        'acl' => array
        (
            'authors' => array
            (
                'midgard:owner' => MIDCOM_PRIVILEGE_ALLOW,
            ),
        ),
        'parameters' => array
        (
            'net.nehmer.blog' => array
            (
            ),
        ),
        // Child node definitions
        'nodes' => array
        (
            'fi' => array
            (
                'name' => 'fi',
                'title' => 'TKK',
                'component' => 'net.nehmer.static',
                'acl' => array
                (
                ),
                'parameters' => array
                (
                ),
                'nodes' => array
                (
                    'ajankohtaista' => array
                    (
                        'name' => 'ajankohtaista',
                        'title' => 'Ajankohtaista',
                        'component' => 'net.nehmer.blog',
                        'acl' => array
                        (
                            'authors' => array
                            (
                                'midgard:delete' => MIDCOM_PRIVILEGE_ALLOW,
                            ),
                        ),
                        'parameters' => array
                        (
                            'net.nehmer.comments' => array
                            (
                                'allow_anonymous' => '__SCHEMA_anonymous',
                            ),
                        ),
                    ),
                    'opinnot' => array
                    (
                        'name' => 'opinnot',
                        'title' => 'Opinnot',
                        'component' => 'net.nehmer.static',
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
                                'acl' => array
                                (
                
                                ),
                                'parameters' => array
                                (
                
                                ),
                            ),
                        ),
                    ),
                    'tutkimus' => array
                    (
                        'name' => 'tutkimus',
                        'title' => 'Tutkimus',
                        'component' => 'net.nehmer.static',
                        'acl' => array
                        (
                        ),
                        'parameters' => array
                        (
            
                        ),
        
                        // Child node definitions
                        'nodes' => array
                        (
                            'tutkimusala' => array
                            (
                                        'name' => 'tutkimusala',
                                'title' => 'Tutkimusala',
                                'component' => 'net.nehmer.static',
                                'acl' => array
                                (
                
                                ),
                                'parameters' => array
                                (
                
                                ),
                            ),
                            'projekti' => array
                            (
                                        'name' => 'projekti',
                                'title' => 'Projekti',
                                'component' => 'net.nehmer.static',
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
                        'acl' => array
                        (
                        ),
                        'parameters' => array
                        (
            
                        ),
                    ),
                    'yhteystiedot' => array
                    (
                        'name' => 'yhteystiedot',
                        'title' => 'Yhteystiedot',
                        'component' => 'net.nehmer.static',
                        'acl' => array
                        (
                        ),
                        'parameters' => array
                        (
                
                        ),
            
                        // Child node definitions
                        'nodes' => array
                        (
                            'henkilokunta' => array
                            (
                                'name' => 'henkilokunta',
                                'title' => 'Henkilokunta',
                                'component' => 'net.nemein.personnel',
                                'acl' => array
                                (
        
                                ),
                                'parameters' => array
                                (
                                    'net.nemein.personnel' => array
                                    (
                                        'group' => '__GROUPGUID_authors',
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
                'title' => 'TKK',
                'component' => 'net.nehmer.static',
                'acl' => array
                (
                ),
                'parameters' => array
                (
                ),         
                // en nodes   
            ),
            'sv' => array
            (
                'name' => 'sv',
                'title' => 'TKK',
                'component' => 'net.nehmer.static',
                'acl' => array
                (
                ),
                'parameters' => array
                (
                ),   
                // sv nodes         
            ),
        ),
    ),
),

