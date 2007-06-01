'privileges' => array
(
    'description' => 'privileges',
    'l10n_db'     => 'midgard.admin.acl',
    'fields'      => array
    (
        'add_assignee' => Array
        (
            // COMPONENT-REQUIRED
            'title' => 'add assignee',
            'storage' => Array(
                'location' => 'configuration',
                'domain'   => 'midgard.admin.acl',
                'name'     => 'add_assignee',
            ),
            'type' => 'select',
            'type_config' => Array
            (
                'options' => Array(),
            ),
            'widget' => 'select',
        ),         
        // This is dynamically filled later
        
    )
),