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
        'acl' => array
        (
            'authors' => array
            (
                'midgard:owner' => MIDCOM_PRIVILEGE_ALLOW,
            ),
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
                                'allow_anonymous' => true,
                            ),
                        ),
                    ),
                   'osasto' => array
                    (
                        'name' => 'osasto',
                        'title' => 'Osasto',
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
                            'yhteystiedot_sijainti' => array
                            (
                                'name' => 'yhteystiedot_sijainti',
                                'title' => 'Yhteystiedot ja sijainti',
                                'component' => 'net.nehmer.static',
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
                                'title' => 'Laboratoriot ja muut yksikot',
                                'component' => 'net.nehmer.static',
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
                                'title' => 'Yritysyhteistyo',
                                'component' => 'net.nehmer.static',
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
                            'luku_ja_tenttijarjestykset' => array
                            (
                                'name' => 'luku_ja_tenttijarjestykset',
                                'title' => 'Luku- ja tenttijarjestykset',
                                'component' => 'net.nehmer.static',
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
                            'tutkimustoiminta_laboratorioittain' => array
                            (
                                'name' => 'tutkimustoiminta_laboratorioittain',
                                'title' => 'Tutkimustoiminta laboratorioittaint',
                                'component' => 'net.nehmer.static',
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
                                'title' => 'Huippuyksikot, -ryhmat ja palkitut',
                                'component' => 'net.nehmer.static',
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
                                'acl' => array
                                (
            
                                ),
                                'parameters' => array
                                (
            
                                ),
                            ),
                            'toimitusyhteistyo' => array
                            (
                                'name' => 'toimitusyhteistyo',
                                'title' => 'Toimitusyhteistyo',
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
                    'opiskelijaksi' => array
                    (
                        'name' => 'opiskelijaksi',
                        'title' => 'Opiskelijaksi',
                        'component' => 'net.nehmer.static',
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
                                'title' => 'Alaa esitteleva teksti',
                                'component' => 'net.nehmer.static',
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
                                'title' => 'Taydennyskoulutus',
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
                    'kirjasto' => array
                    (
                        'name' => 'kirjasto',
                        'title' => 'Kirjasto',
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

