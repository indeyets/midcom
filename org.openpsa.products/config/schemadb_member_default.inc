'default' => array
(
    'description' => 'component',
    'fields'      => array
    (
        'component' => Array
        (
            // COMPONENT-REQUIRED
            'title' => 'component',
            'storage' => 'component',
            'type' => 'select',
            'required' => true,
            // TODO: Switch to universalchooser when it is ready
            'type_config' => Array
            (
                'options' => org_openpsa_products_product_dba::list_products(true),
            ),
            'widget' => 'select',
        ),    
        'pieces' => Array
        (
            // COMPONENT-REQUIRED
            'title' => 'pieces',
            'storage' => 'pieces',
            'required' => true,
            'type' => 'number',
            'widget'  => 'text',
        ),
        'description' => Array
        (
            'title' => 'description',
            'storage' => 'description',
            'type' => 'text',
            'widget' => 'text',
        ),
    )
),