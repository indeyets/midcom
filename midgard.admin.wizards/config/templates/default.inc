// Default template
'default' => array
(
    // Basic metadata for the site template
    'name' => 'default',
    'title' => 'Default', 
    'description' => 'Default structure',
    'credits' => 'Juhana Hirvilahti, the Midgard Project',
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
        ),
    ),

    // Root node definition        
    'root' => array
    (
        'name' => '__HOSTNAME__',
        'title' => '__HOSTTITLE__',
        'component' => 'net.nehmer.static',
        'create_index' => true,
        'use_inherited_style' => true,
        'style_inherit' => true,
        'acl' => array
        (
            // none
        ),
        // Parameters
        'parameters' => array
        (
            // none
        ),
        // Child node definitions
        'nodes' => array
        (
            // none
        ),
    ),
),