'external' => array (
    'name'        => 'external item',
    'description' => 'external item',
    'fields'      => array (
    
    	/* Start of component required fields */
        
        'title' => array (
            'title' => 'title',
            'type'    => 'text',
            'storage'    => 'title', 
            'required' => true,
            'widget' => 'text',
        ),

        'url' => array (
            'title' => 'url',
            'type'    => 'text',
            'storage'    => 'url', 
            'required' => true,
            'widget' => 'text',
        ),
        
        /* End of component required fields */
        
    )
), // external

'internal' => array (
    'name'        => 'internal items',
    'description' => 'internal items',
    'fields'      => array (

    	/* Start of component required fields */
        
    	'article' => array
        (
            'title' => 'article',
            'storage' => null,
            'type' => 'select',
            'type_config' => array
            (
                 'require_corresponding_option' => false,
                 'allow_multiple' => true,
                 'options' => array(),
                 'multiple_storagemode' => 'serialized',
            ),
            'widget' => 'chooser',
            'widget_config' => array
            (
                'clever_class' => 'article',
                'id_field' => 'guid',
            ),
            'required' => false,
        ),

    	'topic' => array
        (
            'title' => 'topic',
            'storage' => null,
            'type' => 'select',
            'type_config' => array
            (
                 'require_corresponding_option' => false,
                 'allow_multiple' => true,
                 'options' => array(),
                 'multiple_storagemode' => 'serialized',
            ),
            'widget' => 'chooser',
            'widget_config' => array
            (
                'clever_class' => 'topic',
                'id_field' => 'guid'
            ),
            'required' => false,
        ),
        
        /* End of component required fields */
        
    )
), // internal

'edit' => array (
    'name'        => 'edit item',
    'description' => 'edit item',
    'fields'      => array (
    
    	/* Start of component required fields */
        
        'title' => array (
            'title' => 'title',
            'type'    => 'text',
            'storage'    => 'title', 
            'required' => true,
            'widget' => 'text',
        ),
        
        /* End of component required fields */
        
    )
), // edit

