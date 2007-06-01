'email' => array
(
    'name'        => 'email',
    'description' => 'email backend form',
    'operations'  => array
    (
        'cancel' => 'back to cart',
        'save'   => 'confirm order',
    ),
    'fields'      => array
    (
        'firstname' => Array
        (
            'title'     => 'first name',
            'type'      => 'text',
            'widget'    => 'text',
            'required'  => true,
        ),
        'lastname' => Array
        (
            'title'     => 'last name',
            'type'      => 'text',
            'widget'    => 'text',
            'required'  => true,
        ),
        'email' => Array
        (
            'title'     => 'email',
            'type'      => 'text',
            'widget'    => 'text',
            'required'  => true,
            'validation' => 'email',
        ),
        'street' => Array
        (
            'title'     => 'street address',
            'type'      => 'text',
            'widget'    => 'text',
            'required'  => true,
        ),
        'postcode' => Array
        (
            'title'     => 'postal code',
            'type'      => 'text',
            'widget'    => 'text',
            'required'  => true,
        ),
        'city' => Array
        (
            'title'     => 'city',
            'type'      => 'text',
            'widget'    => 'text',
            'required'  => true,
        ),
        'country' => Array
        (
            'title'     => 'country',
            'type'      => 'text',
            'widget'    => 'text',
            'required'  => true,
        ),
        'notes' => Array
        (
            'title'     => 'notes for order processing',
            'type'      => 'text',
            'widget'    => 'textarea',
            'required'  => false,
        ),
    ),
), // email

