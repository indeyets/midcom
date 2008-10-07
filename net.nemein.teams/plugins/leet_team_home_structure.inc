// Leet user account template
'home' => array
(
    // Basic metadata for the site template
    'name' => 'home',
    'title' => 'Home', 
    'description' => 'User home topic',
    'credits' => '',
    'license' => 'LGPL',
    'examples' => array
    (
    ),
    
    // Groups that should be created / used for this site template      
    'groups' => array
    (
    ),

    // Root node definition        
    'root' => array
    (
        'name' => '__HOSTNAME__',
        'title' => '__HOSTTITLE__',
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
           'forum' => array
            (
                'name' => 'forum',
                'title' => 'Forum',
                'component' => 'net.nemein.discussion',
                'style' => 'koe',
                'acl' => array
                (
                ),
                'parameters' => array
                (
                    'net.nemein.discussion' => array
                    (

                    ),
                    'midcom.helper.datamanager2' => array
                    (
                        'schema_name' => 'blog',
                    ),
                ),
            ),
           'photos' => array
            (
                'name' => 'photos',
                'title' => 'Photos',
                'component' => 'net.nehmer.static',
                'create_index' => true,
                'acl' => array
                (
                ),
                'parameters' => array
                (
                    'net.nehmer.static' => array
                    (

                    ),
                ),
            ),
        ),        
    ),
),
