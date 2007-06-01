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
            'hidden'  => (!$_MIDCOM->auth->admin),
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
        'content' => Array
        (
            // COMPONENT-REQUIRED
            'title' => 'content',
            'storage' => 'content',
            'required' => true,
            'type' => 'text',
            'type_config' => Array 
            ( 
                'output_mode' => 'html' 
            ),
            'widget' => 'tinymce',
        ),
        'categories' => Array
        (
            'title' => 'categories',
            'storage' => 'extra1',
            'type' => 'select',
            'widget' => 'select',
            'type_config' => Array 
            ( 
                'options'        => Array(),
                'allow_other'    => true,
                'allow_multiple' => true,
                'multiple_storagemode' => 'imploded_wrapped',
            ),    
            'widget_config' => Array 
            ( 
                'height' => 3,
            ),        
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
            ),
            'widget' => 'composite',
        ),

    )
),

