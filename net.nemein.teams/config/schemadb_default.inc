'team' => array (
    'name'        => 'team',
    'description' => 'team',
    'fields'      => array (
    
        'team_name' => Array
        (
            'title' => 'team name',
            'storage' => 'name',
            'type' => 'text',
            'widget' => 'text',
        ),
        'team_logo' => Array
        (
            'title' => 'team logo',
            'storage' => null,
            'type' => 'image',
            'type_config' => Array
            (
                'filter_chain' => 'resize(800,600)',
                'auto_thumbnail' => Array(100,100),
                'keep_original' => true,
            ),
            'widget' => 'image',
            'hidden' => false,
        ),
        'team_description' => Array
        (
            'title' => 'team introduction text',
            'storage' => 'parameter',
            'type' => 'text',
            'widget' => 'textarea',
        ),
        'team_location' => Array
        (
            'title' => 'team location',
            'storage' => 'parameter',
            'type' => 'text',
            'widget' => 'text',
        ),
        'recruiting' => Array
        (
            'title' => 'Is recruiting',
            'storage' => array
            (
                'location' => 'configuration',
                'domain'   => 'net.nemein.teams:preferences',
                'name'     => 'is_recruiting',
            ),
            'type' => 'boolean',
            'widget' => 'checkbox',
            'default' => true,
        ),
    )
), // team

