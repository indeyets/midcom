'default' => array
(
    'description' => 'product',
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
        'price' => Array
        (
            'title' => 'price',
            'storage' => 'price',
            'type' => 'number',
            'widget'  => 'text',
        ),
        'description' => Array
        (
            'title' => 'description',
            'storage' => 'description',
            'type' => 'text',
            'widget' => 'textarea',
        ),
        'orgOpenpsaObtype' => Array
        (
            // COMPONENT-REQUIRED
            'title' => 'type',
            'storage' => 'orgOpenpsaObtype',
            'type' => 'select',
            //'required' => true,
            'type_config' => Array
            (
                'options' => Array
                (
                    ORG_OPENPSA_PRODUCTS_PRODUCT_TYPE_ACTIVE => 'active',
                    ORG_OPENPSA_PRODUCTS_PRODUCT_TYPE_INACTIVE => 'inactive',
                    ORG_OPENPSA_PRODUCTS_PRODUCT_TYPE_COMPONENT => 'component',
                ),
            ),
            'widget' => 'select',
        ),
        'start' => Array
        (
            // COMPONENT-REQUIRED
            'title' => 'in market from',
            'storage' => 'start',
            'type' => 'date',
            'type_config' => Array 
            ( 
                'storage_type' => 'UNIXTIME' 
            ),
            'widget' => 'jsdate',
            'hidden' => true,
        ),
        'end' => Array
        (
            // COMPONENT-REQUIRED
            'title' => 'in market until',
            'storage' => 'end',
            'type' => 'date',
            'type_config' => Array 
            ( 
                'storage_type' => 'UNIXTIME' 
            ),
            'widget' => 'jsdate',
            'hidden' => true,
        ),
        'components' => Array
        (
            'title' => 'components',
            'storage' => null,
            'type' => 'composite',
            'type_config' => Array
            (
                'child_class' => 'org_openpsa_products_product_member_dba',
                'child_schemadb' => 'file:/org/openpsa/products/config/schemadb_member_default.inc',
                'child_foreign_key_fieldname' => 'product',
                'style_element_name' => 'component',
            ),
            'widget' => 'composite',
        ),    
    )
),