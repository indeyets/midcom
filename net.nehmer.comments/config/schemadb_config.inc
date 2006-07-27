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
            'config_domain' => 'net.nehmer.blog',
            'config_key' => 'allow_anonymous',
            'widget' => 'config_radiobox',
            'widget_radiobox_choices' => Array
            (
                '' => 'default setting',
                '1' => 'yes',
                '0' => 'no',
            ),
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
