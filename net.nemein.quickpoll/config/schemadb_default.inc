'default' => array
(
    'description' => 'poll',
    'fields'      => array
    (
        'name' => Array
        (
            // COMPONENT-REQUIRED
            'title'   => 'url name',
            'storage' => 'name',
            'type'    => 'text',
            'widget'  => 'text',
            'write_privilege' => array
            (
                'privilege' => 'midcom:urlname',
            ),
        ),    
        'title' => Array
        (
            // COMPONENT-REQUIRED
            'title' => 'title',
            'storage' => 'title',
            'required' => true,
            'type' => 'text',
            'widget' => 'text',
        ),
        'abstract' => Array
        (
            // COMPONENT-REQUIRED
            'title' => 'abstract',
            'storage' => 'abstract',
            'type' => 'text',
            'widget' => 'textarea',
        ),
        'options' => Array
        (
            'title' => 'options',
            'storage' => null,
            'type' => 'composite',
            'type_config' => Array
            (
                'child_class' => 'net_nemein_quickpoll_option_dba',
                'child_constraints' => Array(),
                'child_schemadb' => 'file:/net/nemein/quickpoll/config/schemadb_options.inc',
                'style_element_name' => 'option',
                'window_mode' => false,
                'child_foreign_key_fieldname' => 'article',
                'parent_key_fieldname' => 'id',
                'enable_creation' => false,
            ),
            'widget' => 'composite',
        ),

    )
),

