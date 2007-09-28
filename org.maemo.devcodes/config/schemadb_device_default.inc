'device' => array (
    'name'        => 'device',
    'description' => 'device program',
    'fields'      => array
    (
        'codename' => array (
            'title'       => 'codename',
            'type'        => 'text',
            'widget'      => 'text',
            'storage'     => 'codename', 
            'required'    => true,
        ),
        'title' => array (
            'title'       => 'title',
            'type'        => 'text',
            'widget'      => 'text',
            'storage'     => 'title',
            'required'    => true,
        ),
        'notes' => array (
            'title'       => 'notes',
            'type'        => 'text',
            'widget'      => 'textarea',
            'storage'     => 'notes',
        ),
        'start' => array (
            'title'       => 'start accepting applications',
            'type'        => 'date',
            'widget'      => 'jsdate',
            'storage'     => 'start',
        ),
        'end' => array (
            'title'       => 'stop accepting applications',
            'type'        => 'date',
            'widget'      => 'jsdate',
            'storage'     => 'end',
        ),
    )
), // device
