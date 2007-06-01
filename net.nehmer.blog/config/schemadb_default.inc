'default' => array
(
    'description' => 'article',
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
    	'related' => array
        (
            'title' => 'Related stories',
            'storage' => 'extra3',
            'type' => 'select',
            'type_config' => array
            (
                 'require_corresponding_option' => false,
                 'options' => array(),
            ),
            'widget' => 'universalchooser',
            'widget_config' => array
            (
                'class'       => 'midcom_db_article',
                'component'   => 'net.nehmer.static',
                'titlefield'  => 'title',
                'idfield'     => 'guid',
                'constraints' => array
                (
                ),
                'searchfields'  => array
                (
                    'title',
                ),
                'orders'        => array
                (
                    array('title'  => 'ASC'), 
                ),
            ),            
        ),
    )
),

