'default' => array 
(
    'description' => 'status',
    'fields'      => array 
    (    
        'status' => array 
        (
            'title'    => 'status',
            'type'     => 'text',
            'storage'  => 'status', 
            'required' => true,
            'widget'   => 'text',
        ),
        'private' => array
        (
            'title'    => 'private',
            'storage'  => null,
            'type'     => 'privilegeset',
            'type_config' => array
            (
                'privileges' => array
                (
                    array
                    (
                        'midgard:read', 
                        'EVERYONE', 
                        MIDCOM_PRIVILEGE_DENY
                    ),
                ),
            ),
            'widget'   => 'privilegecheckbox',
        ),        
    )
),

