'default' => array
(
    'description' => 'product group',
    'fields'      => array
    (
        'code' => Array
        (
            // COMPONENT-REQUIRED
            'title'   => 'code',
            'storage' => 'code',
            'type'    => 'text',
            'widget'  => 'text',
            'required' => true,
        ),
        'title' => Array
        (
            // COMPONENT-REQUIRED
            'title' => 'title',
            'storage' => 'title',
            'required' => true,
            'type' => 'text',
            'widget'  => 'text',
        ),
        'up' => Array
        (
            'title' => 'parent group',
            'storage' => 'up',
            'type' => 'select',
            'type_config' => Array
            (
                'options' => org_openpsa_products_product_group_dba::list_groups(),
            ),
            'widget' => 'select',
        ),
        'description' => Array
        (
            'title' => 'description',
            'storage' => 'description',
            'type' => 'text',
            'type_config' => Array ( 
                'output_mode' => 'markdown' 
            ),
            'widget' => 'textarea',
        ),
    )
),