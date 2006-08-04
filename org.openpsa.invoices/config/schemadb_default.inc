'default' => array
(
    'description' => 'invoice',
    'fields'      => array
    (
        'invoiceNumber' => array
        (
            'description' => 'invoice number',
            'location'  => 'invoiceNumber',
            'datatype'  => 'text',
            'start_fieldgroup' => array (
                'title' => 'basic information',
                'css_group' => 'area meta',
            ),
        ),
        'owner' => array
        (
            'description' => 'owner',
            'location'  => 'owner',
            'datatype'  => 'integer',
            'widget'    => 'contactchooser',
            'default'   => $_MIDGARD['user'],
        ),
        'customerContact' => array
        (
            'description' => 'customer contact',
            'location'  => 'customerContact',
            'datatype'  => 'integer',
            'widget'    => 'contactchooser',
        ),
        'customer' => array (
            'description' => 'customer',
            'datatype'    => 'integer',
            'location'    => 'customer',
        ),        
        'description' => array
        (
            'description' => 'description',
            'location'  => 'description',
            'datatype'  => 'markdown',
            'end_fieldgroup' => '',
        ),        
        'sum' => array
        (
            'description' => 'sum',
            'location'  => 'sum',
            'datatype'  => 'number',
            'widget'    => 'text',            
            'default'   => 0,
            'start_fieldgroup' => array (
                'title' => 'invoicing information',
                'css_group' => 'area meta',
            ),            
        ),
        'vat' => array
        (
            'description' => 'vat',
            'location'  => 'vat',
            'datatype'  => 'integer',
            'widget'    => 'text',
            'default'   => 22,
        ),
        'due' => array (
            'description' => 'due',
            'datatype'    => 'unixdate',
            'location'    => 'due',
            'widget_date_enable_outside_ais' => true,            
        ),        
        'sent' => array (
            'description' => 'sent',
            'datatype'    => 'unixdate',
            'location'    => 'sent',
            'readonly'    => true,
            'hidden'    => true,
        ),            
        'paid' => array (
            'description' => 'paid',
            'datatype'    => 'unixdate',
            'location'    => 'paid',
            'readonly'    => true,
            'hidden'    => true,            
            'end_fieldgroup' => '',            
        ),
    ),
),