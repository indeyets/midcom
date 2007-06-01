'default' => array
(
    'name'        => 'default',
    'description' => 'database entry',
    'fields'      => array
    (
        'id' => array
        (
            'description' => 'database id',
            'datatype'    => 'text',
            'location'    => 'id',
            'hidden'      => true,
            'readonly'     => true,
        ),
        'name' => array
        (
            'description' => 'url name',
            'datatype'    => 'text',
            'location'    => 'name',
            'net_nemein_simpledb_list' => false,
        ),
        'title' => array
        (
            'description' => 'entry name',
            'datatype'    => 'text',
            'location'    => 'title',
        ),
        'extra1' => array
        (
            'description' => 'searchable 1',
            'datatype'    => 'text',
            'location'    => 'extra1',
        ),
        'extra2' => array
        (
            'description' => 'searchable 2',
            'datatype'    => 'text',
            'location'    => 'extra2',
        ),
        'extra3' => array
        (
            'description' => 'searchable 3',
            'datatype'    => 'text',
            'location'    => 'extra3',
        ),
        'content' => array
        (
            'description' => 'description',
            'datatype'    => 'text',
            'location'    => 'content',
            'widget_text_inputstyle' => 'longtext',
            'net_nemein_simpledb_search' => false,
        ),
    ),
),
