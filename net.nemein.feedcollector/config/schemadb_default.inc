'default' => array
(
    'description' => 'article',
    'fields'      => array
    (   
        'title' => Array
        (
            // COMPONENT-REQUIRED
            'title' => 'title',
            'storage' => 'title',
            'required' => true,
            'type' => 'text',
            'widget' => 'text',
        ),
        'feedtopic' => array
        (
            'title' => 'news folders',
            'storage' => 'feedtopic',
            'type' => 'select',
            'type_config' => array
            (
                'allow_multiple' => false,
                'allow_other' => false,
                'require_corresponding_option' => false,
                 'options' => array(),
            ),
            'widget' => 'chooser',
            'widget_config' => array
            (
                'clever_class' => 'topic',
                'result_headers' => array
                (
                    array
                    (
                        'name' => 'extra',
                        'title' => 'title'
                    ),
                    array
                    (
                        'name' => 'component',
                        'title' => 'component'
                    ),
                ),
                'id_field' => 'id',
                'constraints' => array
                (
                    array
                    (
                        'field' => 'component',
                        'op' => '=',
                        'value' => 'net.nehmer.blog',
                    ),
                ),
            ),
        ),
        'categories' => Array
        (
            'title' => $_MIDCOM->i18n->get_string('categories', 'net.nehmer.blog'),
            'storage' => 'categories',
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
    ),
),
