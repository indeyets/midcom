'config' => array
(
    'description' => 'configuration',
    'fields' => array
    (
        'schemadb' => array
        (
            'title' => 'schemadb',
            'storage' => array
            (
                'location' => 'parameter',
                'domain' => 'cc.kaktus.pearserver',
                'name' => 'schemadb',
            ),
            'type' => 'text',
            'widget' => 'text',
        ),
        'root_group' => array
        (
            'title' => 'root group',
            'storage' => array
            (
                'location' => 'parameter',
                'domain' => 'cc.kaktus.pearserver',
                'name' => 'schemadb',
            ),
            'type' => 'select',
            'type_config' => array
            (
                 'require_corresponding_option' => false,
                 'options' => array(),
                'allow_other' => true,
            ),
            'widget' => 'universalchooser',
            'widget_config' => array
            (
                'class' => 'org_openpsa_products_product_group_dba',
                'component' => 'org.openpsa.products',
                'titlefield' => 'title',
                'idfield' => 'guid',
                'constraints' => array
                (
                ),
                'searchfields' => array
                (
                    'title',
                    'code',
                ),
                'orders' => array
                (
                    array
                    (
                        'title', 'ASC',
                    ),
                    array
                    (
                        'code', 'ASC',
                    ),
                ),
            ),
        ),
    ),
),