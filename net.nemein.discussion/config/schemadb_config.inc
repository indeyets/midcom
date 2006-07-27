'config' => array
(
    'name'        => 'config',
    'description' => 'Default Configuration Schema',
    'locktimeout' => 0,
    'fields'      => array
    (
        /* Schema settings */
        'schemadb' => array
        (
            'description' => 'schema database',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nemein.discussion',
            'config_key' => 'schemadb',
            'start_fieldgroup' => array
            ( 
                'title' => 'advanced schema and data settings'
            ),
            'end_fieldgroup' => '',
        ),
    ),
),