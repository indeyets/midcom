// TKK departmant template
'tkk_department' => array
(
    // Basic metadata for the site template
    'name' => 'tkk_department',
    'title' => 'TKK department', 
    'description' => 'Default structure for departments',
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
                        'schemadb' => '/sitegroup-config/net.nehmer.static/schemadb_frontpage',
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
                        'acl' => array
                        (
                            /*
                            'authors' => array
                            (
                                'midgard:delete' => MIDCOM_PRIVILEGE_ALLOW,
                            ),
                            */
                        ),
                        'parameters' => array
                        (

                        ),
                    ),
                   'osasto' => array
                    (
                        'name' => 'osasto',
                        'title' => 'Osasto',
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
                            'yhteystiedot_sijainti' => array
                            (
                                'name' => 'yhteystiedot_sijainti',
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
                            'osaston_hallinto' => array
                            (
                                'name' => 'osaston_hallinto',
                                'title' => 'Osaston hallinto',
                                'component' => 'net.nehmer.static',
                                'create_index' => true,
                                'acl' => array
                                (
        
                                ),
                                'parameters' => array
                                (
        
                                ),
                            ),
                            'laboratoriot_muutyksikot' => array
                            (
                                'name' => 'labosatoriot_muutyksikot',
                                'title' => 'Laboratoriot ja muut yksiköt',
                                'component' => 'net.nehmer.static',
                                'create_index' => true,
                                'acl' => array
                                (
        
                                ),
                                'parameters' => array
                                (
        
                                ),
                            ),
                            'tilastoja' => array
                            (
                                'name' => 'tilastoja',
                                'title' => 'Tilastoja',
                                'component' => 'net.nehmer.static',
                                'create_index' => true,
                                'acl' => array
                                 (
        
                                ),
                                'parameters' => array
                                (
        
                                ),
                            ),
                            'medialle' => array
                            (
                                'name' => 'medialle',
                                'title' => 'Medialle',
                                'component' => 'net.nehmer.static',
                                'create_index' => true,
                                'acl' => array
                                (
                                 ),
                                 'parameters' => array
                                (
        
                                ),
                            ),
                            'alumnit' => array
                            (
                                'name' => 'alumnit',
                                'title' => 'Alumnit',
                                'component' => 'net.nehmer.static',
                                'create_index' => true,
                                'acl' => array
                                (
        
                                ),
                                'parameters' => array
                                (
        
                                ),
                             ),
                            'yritysyhteistyo' => array
                            (
                                'name' => 'yritysyhteistyo',
                                'title' => 'Yritysyhteistyö',
                                'component' => 'net.nehmer.static',
                                'create_index' => true,
                                'acl' => array
                                (
        
                                ),
                                'parameters' => array
                                (
        
                                ),
                            ),
                            'henkilokunnalle' => array
                            (
                                'name' => 'henkilokunnalle',
                                'title' => 'Henkilokunnalle',
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
                            'tutkintoohjelma_opas' => array
                            (
                                'name' => 'tutkintoohjelma_opas',
                                'title' => 'Tutkinto-ohjelma ja opinto-opas',
                                'component' => 'net.nehmer.static',
                                'create_index' => true,
                                'acl' => array
                                (
            
                                ),
                                'parameters' => array
                                (
            
                                ),
                            ),
                            'international_masters_programme' => array
                            (
                                'name' => 'international_masters_programme',
                                'title' => 'International Master\'s Programme',
                                'component' => 'net.nehmer.static',
                                'create_index' => true,
                                'acl' => array
                                (
            
                                ),
                                'parameters' => array
                                (
            
                                ),
                            ),
                            'jatko_opinnot' => array
                            (
                                'name' => 'jatko_opinnot',
                                'title' => 'Jatko-opinnot',
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
                            'luku_ja_tenttijarjestykset' => array
                            (
                                'name' => 'luku_ja_tenttijarjestykset',
                                'title' => 'Luku- ja tenttijärjestykset',
                                'component' => 'net.nehmer.static',
                                'create_index' => true,
                                'acl' => array
                                (
            
                                ),
                                'parameters' => array
                                (
            
                                ),
                            ),
                            'harjoittelu' => array
                            (
                                'name' => 'harjoittelu',
                                'title' => 'Harjoittelu',
                                'component' => 'net.nehmer.static',
                                'create_index' => true,
                                'acl' => array
                                (
            
                                ),
                                'parameters' => array
                                (
            
                                ),
                            ),
                            'opintojen_ohjaus_ja_neuvonta' => array
                            (
                                'name' => 'opintojen_ohjaus_ja_neuvonta',
                                'title' => 'Opintojen ohajus ja neuvonta',
                                'component' => 'net.nehmer.static',
                                'create_index' => true,
                                'acl' => array
                                (
            
                                ),
                                'parameters' => array
                                (
            
                                ),
                            ),
                            'lomakkeita' => array
                            (
                                'name' => 'lomakkeita',
                                'title' => 'Lomakkeita',
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
                    'tutkimus' => array
                    (
                        'name' => 'tutkimus',
                        'title' => 'Tutkimus',
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
                            'tutkimustoiminta_laboratorioittain' => array
                            (
                                'name' => 'tutkimustoiminta_laboratorioittain',
                                'title' => 'Tutkimustoiminta laboratorioittain',
                                'component' => 'net.nehmer.static',
                                'create_index' => true,
                                'acl' => array
                                (
            
                                ),
                                'parameters' => array
                                (
            
                                ),
                            ),
                            'huippuyksikot_ryhmat_ja_palkitut' => array
                            (
                                'name' => 'huippuyksikot_ryhmat_ja_palkitut',
                                'title' => 'Huippuyksiköt, -ryhmät ja palkitut',
                                'component' => 'net.nehmer.static',
                                'create_index' => true,
                                'acl' => array
                                (
            
                                ),
                                'parameters' => array
                                (
            
                                ),
                            ),
                            'professorit' => array
                            (
                                'name' => 'professorit',
                                'title' => 'Professorit',
                                'component' => 'net.nehmer.static',
                                'create_index' => true,
                                'acl' => array
                                (
            
                                ),
                                'parameters' => array
                                (
            
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
                            'tutkimusyhteistyo' => array
                            (
                                'name' => 'tutkimusyhteistyo',
                                'title' => 'Tutkimusyhteistyö',
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
                    'opiskelijaksi' => array
                    (
                        'name' => 'opiskelijaksi',
                        'title' => 'Opiskelijaksi',
                        'component' => 'net.nehmer.static',
                        'create_index' => true,
                        'acl' => array
                        (
                        ),
                        'parameters' => array
                        (
            
                        ),
            
                        'nodes' => array
                        (
                            'alaa_esitteleva_teksti' => array
                            (
                                'name' => 'alaa_esitteleva_teksti',
                                'title' => 'Alaa esittelevä teksti',
                                'component' => 'net.nehmer.static',
                                'create_index' => true,
                                'acl' => array
                                (
            
                                ),
                                'parameters' => array
                                (
            
                                ),
                            ),
                            'opiskelu_nn_osastolla' => array
                            (
                                'name' => 'opiskelu_nn_osastolla',
                                'title' => 'Opiskelu NN osastolla',
                                'component' => 'net.nehmer.static',
                                'create_index' => true,
                                'acl' => array
                                (
            
                                ),
                                'parameters' => array
                                (
            
                                ),
                            ),
                            'valmistuneiden_tarinoita' => array
                            (
                                'name' => 'valmistuneiden_tarinoita',
                                'title' => 'Valmistuneiden tarinoita',
                                'component' => 'net.nehmer.static',
                                'create_index' => true,
                                'acl' => array
                                (
            
                                ),
                                'parameters' => array
                                (
            
                                ),
                            ),
                            'joo_opiskelijaksi' => array
                            (
                                'name' => 'joo_opiskelijaksi',
                                'title' => 'JOO-opiskelijaksi',
                                'component' => 'net.nehmer.static',
                                'create_index' => true,
                                'acl' => array
                                (
            
                                ),
                                'parameters' => array
                                (
            
                                ),
                            ),
                            'international_masters_programme' => array
                            (
                                'name' => 'international_masters_programme',
                                'title' => 'International Master\'s programme',
                                'component' => 'net.nehmer.static',
                                'create_index' => true,
                                'acl' => array
                                (
            
                                ),
                                'parameters' => array
                                (
            
                                ),
                            ),
                            'jatko_opiskelijaksi' => array
                            (
                                'name' => 'jatko_opiskelijaksi',
                                'title' => 'Jatko-opiskelijaksi',
                                'component' => 'net.nehmer.static',
                                'create_index' => true,
                                'acl' => array
                                (
            
                                ),
                                'parameters' => array
                                (
            
                                ),
                            ),
                            'taydennyskoulutus' => array
                            (
                                'name' => 'taydennyskoulutus',
                                'title' => 'Täydennyskoulutus',
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
                    'kirjasto' => array
                    (
                        'name' => 'kirjasto',
                        'title' => 'Kirjasto',
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
                            'palvelut' => array
                            (
                                'name' => 'palvelut',
                                'title' => 'Palvelut',
                                'component' => 'net.nemein.personnel',
                                'acl' => array
                                (
            
                                ),
                                'parameters' => array
                                (
            
                                ),
                            ),
                            'kokoelmat' => array
                            (
                                'name' => 'kokoelmat',
                                'title' => 'Kokoelmat',
                                'component' => 'net.nehmer.static',
                                'create_index' => true,
                                'acl' => array
                                (
            
                                ),
                                'parameters' => array
                                (
            
                                ),
                            ),
                            'tiedonhaku' => array
                            (
                                'name' => 'tiedonhaku',
                                'title' => 'Tiedonhaku',
                                'component' => 'net.nehmer.static',
                                'create_index' => true,
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
                        'schemadb' => '/sitegroup-config/net.nehmer.static/schemadb_frontpage',
                    ),
                ),         
                // en nodes  
                'nodes' => array
                (
                    'news' => array
                    (
                        'name' => 'news',
                        'title' => 'News',
                        'component' => 'net.nehmer.blog',
                        'symlink_target_id' => 'news_fi',
                        'acl' => array
                        (

                        ),
                        'parameters' => array
                        (

                        ),
                    ),
                   'department' => array
                    (
                        'name' => 'department',
                        'title' => 'Department',
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
                                'title' => 'Contact Information and Location',
                                'component' => 'net.nehmer.static',
                                'create_index' => true,
                                'acl' => array
                                (
        
                                ),
                                'parameters' => array
                                (
        
                                ),
                            ),
                            'administration' => array
                            (
                                'name' => 'administration',
                                'title' => 'Administration',
                                'component' => 'net.nehmer.static',
                                'create_index' => true,
                                'acl' => array
                                (
        
                                ),
                                'parameters' => array
                                (
        
                                ),
                            ),
                            'laboratories_and_other_units' => array
                            (
                                'name' => 'laboratories_and_other_units',
                                'title' => 'Laboratories and Other Units',
                                'component' => 'net.nehmer.static',
                                'create_index' => true,
                                'acl' => array
                                (
        
                                ),
                                'parameters' => array
                                (
        
                                ),
                            ),
                            'statistics' => array
                            (
                                'name' => 'statistics',
                                'title' => 'Statistics',
                                'component' => 'net.nehmer.static',
                                'create_index' => true,
                                'acl' => array
                                 (
        
                                ),
                                'parameters' => array
                                (
        
                                ),
                            ),
                            'for_media' => array
                            (
                                'name' => 'for_media',
                                'title' => 'For Media',
                                'component' => 'net.nehmer.static',
                                'create_index' => true,
                                'acl' => array
                                (
                                 ),
                                 'parameters' => array
                                (
        
                                ),
                            ),
                            'alumni' => array
                            (
                                'name' => 'alumni',
                                'title' => 'Alumni',
                                'component' => 'net.nehmer.static',
                                'create_index' => true,
                                'acl' => array
                                (
        
                                ),
                                'parameters' => array
                                (
        
                                ),
                             ),
                            'corporate_cooperation' => array
                            (
                                'name' => 'corporate_cooperation',
                                'title' => 'Corporate Cooperation',
                                'component' => 'net.nehmer.static',
                                'create_index' => true,
                                'acl' => array
                                (
        
                                ),
                                'parameters' => array
                                (
        
                                ),
                            ),
                            'for_staff' => array
                            (
                                'name' => 'for_staff',
                                'title' => 'For Staff',
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
                            'study_programme' => array
                            (
                                'name' => 'study_programme',
                                'title' => 'Study Programme',
                                'component' => 'net.nehmer.static',
                                'create_index' => true,
                                'acl' => array
                                (
            
                                ),
                                'parameters' => array
                                (
            
                                ),
                            ),
                            'international_masters_programme' => array
                            (
                                'name' => 'international_masters_programme',
                                'title' => 'International Master\'s Programme',
                                'component' => 'net.nehmer.static',
                                'create_index' => true,
                                'acl' => array
                                (
            
                                ),
                                'parameters' => array
                                (
            
                                ),
                            ),
                            'graduate_studies' => array
                            (
                                'name' => 'graduate_studies',
                                'title' => 'Graduate Studies',
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
                            'schedule_of_classes_and_order_of_exams' => array
                            (
                                'name' => 'schedule_of_classes_and_order_of_exams',
                                'title' => 'Schedule of Classes and Order of Exams',
                                'component' => 'net.nehmer.static',
                                'create_index' => true,
                                'acl' => array
                                (
            
                                ),
                                'parameters' => array
                                (
            
                                ),
                            ),
                            'internship' => array
                            (
                                'name' => 'internship',
                                'title' => 'Internship',
                                'component' => 'net.nehmer.static',
                                'create_index' => true,
                                'acl' => array
                                (
            
                                ),
                                'parameters' => array
                                (
            
                                ),
                            ),
                            'study_councelling' => array
                            (
                                'name' => 'study_councelling',
                                'title' => 'Study Councelling',
                                'component' => 'net.nehmer.static',
                                'create_index' => true,
                                'acl' => array
                                (
            
                                ),
                                'parameters' => array
                                (
            
                                ),
                            ),
                            'forms' => array
                            (
                                'name' => 'forms',
                                'title' => 'Forms',
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
                            'research_in_laboratories' => array
                            (
                                'name' => 'research_in_laboratories',
                                'title' => 'Research in Laboratories',
                                'component' => 'net.nehmer.static',
                                'create_index' => true,
                                'acl' => array
                                (
            
                                ),
                                'parameters' => array
                                (
            
                                ),
                            ),
                            'centers_of_exellence_groups_prices' => array
                            (
                                'name' => 'centers_of_exellence_groups_prices',
                                'title' => 'Centers of Exellence, Groups, Prizes',
                                'component' => 'net.nehmer.static',
                                'create_index' => true,
                                'acl' => array
                                (
            
                                ),
                                'parameters' => array
                                (
            
                                ),
                            ),
                            'professors' => array
                            (
                                'name' => 'professors',
                                'title' => 'Professors',
                                'component' => 'net.nehmer.static',
                                'create_index' => true,
                                'acl' => array
                                (
            
                                ),
                                'parameters' => array
                                (
            
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
                            'research_cooperation' => array
                            (
                                'name' => 'research_cooperation',
                                'title' => 'Research Cooperation',
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
                    'prospective_students' => array
                    (
                        'name' => 'prospective_students',
                        'title' => 'Prospective Students',
                        'component' => 'net.nehmer.static',
                        'create_index' => true,
                        'acl' => array
                        (
                        ),
                        'parameters' => array
                        (
            
                        ),
            
                        'nodes' => array
                        (
                            'department_introduction' => array
                            (
                                'name' => 'department_introduction',
                                'title' => 'Department Introduction',
                                'component' => 'net.nehmer.static',
                                'create_index' => true,
                                'acl' => array
                                (
            
                                ),
                                'parameters' => array
                                (
            
                                ),
                            ),
                            'studying_at_the_department' => array
                            (
                                'name' => 'studying_at_the_department',
                                'title' => 'Studying at the Department',
                                'component' => 'net.nehmer.static',
                                'create_index' => true,
                                'acl' => array
                                (
            
                                ),
                                'parameters' => array
                                (
            
                                ),
                            ),
                            'stories_from_tkk_graduates' => array
                            (
                                'name' => 'stories_from_tkk_graduates',
                                'title' => 'Stories from TKK Graduates',
                                'component' => 'net.nehmer.static',
                                'create_index' => true,
                                'acl' => array
                                (
            
                                ),
                                'parameters' => array
                                (
            
                                ),
                            ),
                            'how_to_become_a_joo_student' => array
                            (
                                'name' => 'how_to_become_a_joo_student',
                                'title' => 'How to Become a JOO-Student',
                                'component' => 'net.nehmer.static',
                                'create_index' => true,
                                'acl' => array
                                (
            
                                ),
                                'parameters' => array
                                (
            
                                ),
                            ),
                            'international_masters_programme' => array
                            (
                                'name' => 'international_masters_programme',
                                'title' => 'International Master\'s programme',
                                'component' => 'net.nehmer.static',
                                'create_index' => true,
                                'acl' => array
                                (
            
                                ),
                                'parameters' => array
                                (
            
                                ),
                            ),
                            'prospective_graduate_students' => array
                            (
                                'name' => 'prospective_graduate_students',
                                'title' => 'Prospective Graduate Students',
                                'component' => 'net.nehmer.static',
                                'create_index' => true,
                                'acl' => array
                                (
            
                                ),
                                'parameters' => array
                                (
            
                                ),
                            ),
                            'continuing_education' => array
                            (
                                'name' => 'continuing_education',
                                'title' => 'Continuing Education',
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
                    'library' => array
                    (
                        'name' => 'library',
                        'title' => 'Library',
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
                            'services' => array
                            (
                                'name' => 'services',
                                'title' => 'Services',
                                'component' => 'net.nemein.personnel',
                                'acl' => array
                                (
            
                                ),
                                'parameters' => array
                                (
            
                                ),
                            ),
                            'collections' => array
                            (
                                'name' => 'collections',
                                'title' => 'Collections',
                                'component' => 'net.nehmer.static',
                                'create_index' => true,
                                'acl' => array
                                (
            
                                ),
                                'parameters' => array
                                (
            
                                ),
                            ),
                            'information_searches' => array
                            (
                                'name' => 'information_searches',
                                'title' => 'Information Searches',
                                'component' => 'net.nehmer.static',
                                'create_index' => true,
                                'acl' => array
                                (
            
                                ),
                                'parameters' => array
                                (
            
                                ),
                            ),
                            'contact_information' => array
                            (
                                'name' => 'contact_information',
                                'title' => 'Contact Information',
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
                        'schemadb' => '/sitegroup-config/net.nehmer.static/schemadb_frontpage',
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
                        'acl' => array
                        (

                        ),
                        'parameters' => array
                        (

                        ),
                    ),
                   'avdelningen' => array
                    (
                        'name' => 'avdelningen',
                        'title' => 'Avdelningen',
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
                            'kontaktuppgifter_och_lage' => array
                            (
                                'name' => 'kontaktuppgifter_och_lage',
                                'title' => 'Kontaktuppgifter och läge',
                                'component' => 'net.nehmer.static',
                                'create_index' => true,
                                'acl' => array
                                (
        
                                ),
                                'parameters' => array
                                (
        
                                ),
                            ),
                            'avgelningens_forvaltning' => array
                            (
                                'name' => 'avgelningens_forvaltning',
                                'title' => 'Avgelningens förvaltning',
                                'component' => 'net.nehmer.static',
                                'create_index' => true,
                                'acl' => array
                                (
        
                                ),
                                'parameters' => array
                                (
        
                                ),
                            ),
                            'laboratorier_och_ovriga_enheter' => array
                            (
                                'name' => 'laboratorier_och_ovriga_enheter',
                                'title' => 'Laboratorier och övriga enheter',
                                'component' => 'net.nehmer.static',
                                'create_index' => true,
                                'acl' => array
                                (
        
                                ),
                                'parameters' => array
                                (
        
                                ),
                            ),
                            'statistik' => array
                            (
                                'name' => 'statistik',
                                'title' => 'Statistik',
                                'component' => 'net.nehmer.static',
                                'create_index' => true,
                                'acl' => array
                                 (
        
                                ),
                                'parameters' => array
                                (
        
                                ),
                            ),
                            'for_media' => array
                            (
                                'name' => 'for_media',
                                'title' => 'För media',
                                'component' => 'net.nehmer.static',
                                'create_index' => true,
                                'acl' => array
                                (
                                 ),
                                 'parameters' => array
                                (
        
                                ),
                            ),
                            'alumner' => array
                            (
                                'name' => 'alumner',
                                'title' => 'Alumner',
                                'component' => 'net.nehmer.static',
                                'create_index' => true,
                                'acl' => array
                                (
        
                                ),
                                'parameters' => array
                                (
        
                                ),
                             ),
                            'narlingslivssemverka' => array
                            (
                                'name' => 'narlingslivssemverka',
                                'title' => 'Närlingslivssemverka',
                                'component' => 'net.nehmer.static',
                                'create_index' => true,
                                'acl' => array
                                (
        
                                ),
                                'parameters' => array
                                (
        
                                ),
                            ),
                            'for_personalen' => array
                            (
                                'name' => 'for_personalen',
                                'title' => 'För personalen',
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
                            'examensprogram_och_studieguide' => array
                            (
                                'name' => 'examensprogram_och_studieguide',
                                'title' => 'Examensprogram och studieguide',
                                'component' => 'net.nehmer.static',
                                'create_index' => true,
                                'acl' => array
                                (
            
                                ),
                                'parameters' => array
                                (
            
                                ),
                            ),
                            'international_masters_programme' => array
                            (
                                'name' => 'international_masters_programme',
                                'title' => 'International Master\'s Programme',
                                'component' => 'net.nehmer.static',
                                'create_index' => true,
                                'acl' => array
                                (
            
                                ),
                                'parameters' => array
                                (
            
                                ),
                            ),
                            'forskarstudier' => array
                            (
                                'name' => 'forskarstudier',
                                'title' => 'Forskarstudier',
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
                            'las_och_tentamensordningar' => array
                            (
                                'name' => 'las_och_tentamensordningar',
                                'title' => 'Läs och tentamensordningar',
                                'component' => 'net.nehmer.static',
                                'create_index' => true,
                                'acl' => array
                                (
            
                                ),
                                'parameters' => array
                                (
            
                                ),
                            ),
                            'praktik' => array
                            (
                                'name' => 'praktik',
                                'title' => 'Praktik',
                                'component' => 'net.nehmer.static',
                                'create_index' => true,
                                'acl' => array
                                (
            
                                ),
                                'parameters' => array
                                (
            
                                ),
                            ),
                            'studiehandledning_och_radgivning' => array
                            (
                                'name' => 'studiehandledning_och_radgivning',
                                'title' => 'Studiehandledning och rådgivning',
                                'component' => 'net.nehmer.static',
                                'create_index' => true,
                                'acl' => array
                                (
            
                                ),
                                'parameters' => array
                                (
            
                                ),
                            ),
                            'blanketter' => array
                            (
                                'name' => 'blanketter',
                                'title' => 'Blanketter',
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
                            'forskning_enligt_laboratorier' => array
                            (
                                'name' => 'forskning_enligt_laboratorier',
                                'title' => 'Forskning enligt laboratorier',
                                'component' => 'net.nehmer.static',
                                'create_index' => true,
                                'acl' => array
                                (
            
                                ),
                                'parameters' => array
                                (
            
                                ),
                            ),
                            'spetsenheter_och_grupper_prisbelonade_forskare' => array
                            (
                                'name' => 'spetsenheter_och_grupper_prisbelonade_forskare',
                                'title' => 'Spetsenheter och -grupper, prisbelonade forskare',
                                'component' => 'net.nehmer.static',
                                'create_index' => true,
                                'acl' => array
                                (
            
                                ),
                                'parameters' => array
                                (
            
                                ),
                            ),
                            'professorer' => array
                            (
                                'name' => 'professorer',
                                'title' => 'Professorer',
                                'component' => 'net.nehmer.static',
                                'create_index' => true,
                                'acl' => array
                                (
            
                                ),
                                'parameters' => array
                                (
            
                                ),
                            ),
                            'publicationer' => array
                            (
                                'name' => 'publicationer',
                                'title' => 'Publicationer',
                                'component' => 'net.nehmer.static',
                                'create_index' => true,
                                'acl' => array
                                (
            
                                ),
                                'parameters' => array
                                (
            
                                ),
                            ),
                            'forskningssamverka' => array
                            (
                                'name' => 'forskningssamverka',
                                'title' => 'Forskningssamverka',
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
                    'bli_studerande' => array
                    (
                        'name' => 'bli_studerande',
                        'title' => 'Bli studerande',
                        'component' => 'net.nehmer.static',
                        'create_index' => true,
                        'acl' => array
                        (
                        ),
                        'parameters' => array
                        (
            
                        ),
            
                        'nodes' => array
                        (
                            'presentation_av_omradet' => array
                            (
                                'name' => 'presentation_av_omradet',
                                'title' => 'Presentation av området',
                                'component' => 'net.nehmer.static',
                                'create_index' => true,
                                'acl' => array
                                (
            
                                ),
                                'parameters' => array
                                (
            
                                ),
                            ),
                            'att_studera_pa_avdelningen' => array
                            (
                                'name' => 'att_studera_pa_avdelningen',
                                'title' => 'Att studera på avdelningen',
                                'component' => 'net.nehmer.static',
                                'create_index' => true,
                                'acl' => array
                                (
            
                                ),
                                'parameters' => array
                                (
            
                                ),
                            ),
                            'utexaminerades_berattelser' => array
                            (
                                'name' => 'utexaminerades_berattelser',
                                'title' => 'Utexaminerades Berättelser',
                                'component' => 'net.nehmer.static',
                                'create_index' => true,
                                'acl' => array
                                (
            
                                ),
                                'parameters' => array
                                (
            
                                ),
                            ),
                            'att_bli_joo_studerande' => array
                            (
                                'name' => 'att_bli_joo_studerande',
                                'title' => 'Att bli JOO-studerande',
                                'component' => 'net.nehmer.static',
                                'create_index' => true,
                                'acl' => array
                                (
            
                                ),
                                'parameters' => array
                                (
            
                                ),
                            ),
                            'international_masters_programme' => array
                            (
                                'name' => 'international_masters_programme',
                                'title' => 'International Master\'s programme',
                                'component' => 'net.nehmer.static',
                                'create_index' => true,
                                'acl' => array
                                (
            
                                ),
                                'parameters' => array
                                (
            
                                ),
                            ),
                            'att_bli_forskarstuderande' => array
                            (
                                'name' => 'att_bli_forskarstuderande',
                                'title' => 'Att bli forskarstuderande',
                                'component' => 'net.nehmer.static',
                                'create_index' => true,
                                'acl' => array
                                (
            
                                ),
                                'parameters' => array
                                (
            
                                ),
                            ),
                            'fortbildning' => array
                            (
                                'name' => 'fortbildning',
                                'title' => 'Fortbildning',
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
                    'biblioteket' => array
                    (
                        'name' => 'biblioteket',
                        'title' => 'Biblioteket',
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
                            'service' => array
                            (
                                'name' => 'service',
                                'title' => 'Service',
                                'component' => 'net.nemein.personnel',
                                'acl' => array
                                (
            
                                ),
                                'parameters' => array
                                (
            
                                ),
                            ),
                            'samlingar' => array
                            (
                                'name' => 'samlingar',
                                'title' => 'Samlingar',
                                'component' => 'net.nehmer.static',
                                'create_index' => true,
                                'acl' => array
                                (
            
                                ),
                                'parameters' => array
                                (
            
                                ),
                            ),
                            'informationssokning' => array
                            (
                                'name' => 'informationssokning',
                                'title' => 'Informationssökning',
                                'component' => 'net.nehmer.static',
                                'create_index' => true,
                                'acl' => array
                                (
            
                                ),
                                'parameters' => array
                                (
            
                                ),
                            ),
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
                        ),
                    ),
                ),       
            ),
        ),
    ),
),

