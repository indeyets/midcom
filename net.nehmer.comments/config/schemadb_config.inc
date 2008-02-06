'config' => array 
(
    'name'        => 'config',
    'description' => 'Default Configuration Schema', /* This is a topic */
    'locktimeout' => 0, /* Don't lock this, we display the edit view always */
    'fields'      => array 
    (
        'allow_anonymous' => array
        (
            'description' => 'allow_anonymous',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nehmer.comments',
            'config_key' => 'allow_anonymous',
            'widget' => 'config_radiobox',
            'widget_radiobox_choices' => array
            (
                '' => 'default setting',
                '1' => 'yes',
                '0' => 'no',
            ),
        ),

        'ratings_enable' => array
        (
            'description' => 'ratings_enable',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nehmer.comments',
            'config_key' => 'ratings_enable',
            'widget' => 'config_radiobox',
            'widget_radiobox_choices' => array
            (
                '' => 'default setting',
                '1' => 'yes',
                '0' => 'no',
            ),
            'start_fieldgroup' => array
            (
                'title' => 'ratings',
            )
        ),

        'ratings_cache_to_object' => array
        (
            'description' => 'ratings_cache_to_object' ,
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nehmer.comments',
            'config_key' => 'ratings_cache_to_object',
            'widget' => 'config_radiobox',
            'widget_radiobox_choices' => array
            (
                '' => 'default setting',
                '1' => 'yes',
                '0' => 'no',
            ),
        ),

        'ratings_cache_to_object_property' => array
        (
            'description' => 'ratings_cache_to_object_property',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nehmer.comments',
            'config_key' => 'ratings_cache_to_object_property',
        ),

        'ratings_cache_total' => array
        (
            'description' => 'ratings_cache_total',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nehmer.comments',
            'config_key' => 'ratings_cache_total',
            'widget' => 'config_radiobox',
            'widget_radiobox_choices' => array
            (
                '' => 'default setting',
                '1' => 'yes',
                '0' => 'no',
            ),
            'end_fieldgroup' => '',
        ),
        
        'comment_count_cache_to_object' => array
        (
            'description' => 'comment_count_cache_to_object' ,
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nehmer.comments',
            'config_key' => 'comment_count_cache_to_object',
            'widget' => 'config_radiobox',
            'widget_radiobox_choices' => array
            (
                '' => 'default setting',
                '1' => 'yes',
                '0' => 'no',
            ),
        ),
    
        'comment_count_cache_to_object_property' => array
        (
            'description' => 'comment_count_cache_to_object_property',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nehmer.comments',
            'config_key' => 'comment_count_cache_to_object_property',
        ),
        
        'schemadb' => array
        (
            'description' => 'schemadb',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nehmer.comments',
            'config_key' => 'schemadb',
        ),
    )
),
