'default' => array
(
    'description' => 'businessarea',
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
        'description' => Array
        (
            'title' => 'description',
            'storage' => 'description',
            'type' => 'text',
            'type_config' => Array 
            (
                'output_mode' => 'markdown'
            ),
            'widget' => 'textarea',
        ),
        'groups' => Array
        (
            'title' => 'groups',
            'storage' => null,
            'type' => 'mnrelation',
            'type_config' => Array
            (
                'options' => org_openpsa_products_product_group_dba::list_groups(),
                'mapping_class_name' => 'org_openpsa_products_businessarea_member_dba',
                'master_fieldname' => 'businessarea',
                'master_is_id' => true,
                'member_fieldname' => 'grp',
            ),
            'widget' => 'select',
        ),
    )
),