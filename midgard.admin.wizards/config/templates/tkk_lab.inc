// Blog template
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
        'authors',
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
            'ajankohtaista' => array
            (
                'name' => 'ajankohtaista',
                'title' => 'Ajankohtaista',
                'component' => 'net.nehmer.static',
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

		        ),
		    ),
		),
	    ),
        ),
    ),
),

