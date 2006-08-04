'config' => array
(
    'name'        => 'config',
    'description' => 'Default Configuration Schema', /* This is a topic */
    'locktimeout' => 0, /* Don't lock this, we display the edit view always */
    'fields'      => array
    (
        /* invoice settings */
        'default_due_days' => array
        (
            'description'   => 'default due days',
            'datatype'      => 'text',
            'location'      => 'config',
            'config_domain' => 'org.openpsa.invoices',
            'config_key'    => 'default_due_days',
            'start_fieldgroup' => array
            (
                'title' => 'invoice',
            ),
        ),
        'vat_percentages' => array
        (
            'description'   => 'vat percentages',
            'datatype'      => 'text',
            'location'      => 'config',
            'config_domain' => 'org.openpsa.invoices',
            'config_key'    => 'vat_percentages',
        ),
        'invoice_sender' => array
        (
            'description'   => 'invoice sender',
            'datatype'      => 'text',
            'location'      => 'config',
            'widget'        => 'contactchooser',
            'config_domain' => 'org.openpsa.invoices',
            'config_key'    => 'invoice_sender',
        ),
        'default_hourly_price' => array
        (
            'description'   => 'default hourly price',
            'datatype'      => 'text',
            'location'      => 'config',
            'config_domain' => 'org.openpsa.invoices',
            'config_key'    => 'default_hourly_price',
            'end_fieldgroup' => '',
        ),        
        
        /* Schema settings */
        'schemadb' => array
        (
            'description' => 'schema database',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'org.openpsa.invoices',
            'config_key' => 'schemadb',
            'start_fieldgroup' => array
            ( 
                'title' => 'advanced schema and data settings',
            ),
            'end_fieldgroup' => '',
        ),

    ),
),