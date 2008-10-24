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
                'class'       => 'midcom_db_topic',
                'component'   => 'net.nemein.feedcollector',
                'titlefield'  => 'extra',
                'idfield'     => 'guid',
                'searchfields' => array
                (
                    /* title is not actually used, extra is
                    'title',
                    */
                    'extra',
                    'name',
                ),
                'constraints' => array
                (
                    array
                    (
                        'field' => 'extra',
                        'op' => '<>',
                        'value' => '',
                    ),
                    array
                    (
                        'field' => 'component',
                        'op' => '=',
                        'value' => 'net.nehmer.blog',
                    ),
                ),
                'result_headers' => array
                (
                    array
                    (
                        'name' => 'extra',
                        'title' => $_MIDCOM->i18n->get_string('title', 'midcom'),
                    ),
                    array
                    (
                        'name' => 'name',
                        'title' => $_MIDCOM->i18n->get_string('name', 'midcom'),
                    ),
                    /* This is already constraint and as such useless
                    array
                    (
                        'name' => 'component',
                        'title' => $_MIDCOM->i18n->get_string('component', 'midcom'),
                    ),
                    */
                ),
                'generate_path_for' => 'extra',
                'orders' => array
                (
                    /* title is not actually used, extra is
                    array
                    (
                        'title' => 'ASC',
                    ),
                    */
                    array
                    (
                        'extra' => 'ASC',
                    ),
                    array
                    (
                        'name' => 'ASC',
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
