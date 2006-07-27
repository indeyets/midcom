
'component' => Array
(
    'description' => 'Edit component settings',
    'fields' => Array
    (
        'urlname' => Array
        (
            'title' => 'url name',
            'description' => 'The ',
            'helptext' => '',

            'storage' => 'name',
            'required' => true,
            'type' => 'text',
            'widget' => 'text',
        ),
        
    'title' => Array
        (
            'title' => 'Title',
            'description' => '',
            'helptext' => '',

            'storage' => 'extra',
            'type' => 'text',
            'widget' => 'text',
        ),
    
    'style' => Array
     (
            'title' => 'style',
            'description' => '',
            'helptext' => '',
            'storage' => Array
                (
                    'location' => 'parameter',
                    'domain' => 'midcom',
                ),
            'type' => 'select',
            'type_config' => Array
            (
                'options' => null,
                'option_callback' => 'midcom_admin_content2_callbacks_styleselector',
                'allow_multiple' => false,
                'allow_other' => false,
            ),
            'widget' => 'select',
    ),
    'component' => Array
     (
            'title' => 'component',
            'description' => 'Midcom module',
            'helptext' => '',
            'storage' => Array
                (
                    'location' => 'parameter',
                    'domain' => 'midcom'
                ),
            'type' => 'text',
            'widget' => 'text',
    ),
    'navorder' => Array
    (
            'title' => 'navorder',
            'description' => 'Navigation order',
            'helptext' => '',
            'storage' => Array
                (
                    'location' => 'parameter',
                    'domain' => 'midcom.helper.nav',
                ),
            'type' => 'select',
            'type_config' => Array
            (
                'options' => Array 
                (
                MIDCOM_NAVORDER_DEFAULT => "default sort order",
                MIDCOM_NAVORDER_TOPICSFIRST => "topics first",
                MIDCOM_NAVORDER_ARTICLESFIRST => "articles first",
                MIDCOM_NAVORDER_SCORE => "by score",
                ),
                'allow_multiple' => false,
                'allow_other' => false,
            ),
            'widget' => 'select',
        ),
    ), // fields
),