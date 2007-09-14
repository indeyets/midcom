'gforgeuser' => array
(
    'description' => 'gForge user object',
    'fields'      => array
    (
        'firstname' => array
        (
            'description' => 'firstname',
            'type'        => 'text',
            'storage'     => 'firstname', 
            'widget'      => 'text',
            'required'    => true,
        ),
        'lastname' => array
        (
            'description' => 'lastname',
            'type'        => 'text',
            'storage'     => 'lastname', 
            'widget'      => 'text',
            'required'    => true,
        ),
        'email' => array
        (
            'description' => 'email',
            'type'        => 'text',
            'storage'     => 'email', 
            'widget'      => 'text',
            'required'    => true,
        ),
        'jabber_address' => array
        (
            'title'    => 'jabber id',
            'type'     => 'text',
            'storage' => array
            (
                'location' => 'parameter',
                'domain'   => 'org.openpsa.jabber',
                'name'     => 'jid',
            ),
            'widget'   => 'text',
            'required' => false,
        ),
        /*
        'address' => array
        (
            'description' => 'street',
            'type'        => 'text',
            'storage'     => 'street', 
            'widget'      => 'text',
        ),
        // TODO: Figure out how to put the postcode here as well (sensibly!)
        'address2' => array
        (
            'description' => 'city',
            'type'        => 'text',
            'storage'     => 'city', 
            'widget'      => 'text',
        ),
        */
    ),
), // gforgeuser

