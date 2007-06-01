'default' => array
(
    'description'   => 'organization',
    'l10n_db' => 'org.openpsa.contacts',
    'fields'  => array
    (
        'official' => array
        (
            'title'    => 'official name',
            'storage'  => 'official',
            'type'     => 'text',
            'widget'   => 'text',
            'required' => true,
        ),
        'company_id' => array
        (
            'title'    => 'company id',
            'storage'  => 'name',
            'type'     => 'text',
            'widget'   => 'text',
        ),
        'homepage' => array (
            'title'    => 'homepage',
            'storage'  => 'homepage',
            'type'     => 'text',
            'widget'   => 'text',
        ),
        'address_visiting_street' => array
        (
            'title'    => 'street address',
            'storage'  => 'street',
            'type'     => 'text',
            'widget'   => 'text',
        ),
        'address_visiting_postcode' => array
        (
            'title'    => 'postcode',
            'storage'  => 'postcode',
            'type'     => 'text',
            'widget'   => 'text',
        ),
        'address_visiting_city' => array
        (
            'title'    => 'city',
            'storage'  => 'city',
            'type'     => 'text',
            'widget'   => 'text',
        ),
        'address_visiting_country' => array
        (
            'title'    => 'country',
            'storage'  => 'country',
            'type'     => 'text',
            'widget'   => 'text',
        ),
    )
),