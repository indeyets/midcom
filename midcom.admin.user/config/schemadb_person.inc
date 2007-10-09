// This is for a midcom_db_person object
'default' => array 
(
    'description' => 'person',
    'fields'      => array 
    (
        'username' => array 
        (
            // COMPONENT-REQUIRED
            'title'       => 'username',
            'storage'     => 'username',
            'type'        => 'text',
            'widget'      => 'text',
            'index_method' => 'noindex',
        ),
        'firstname' => array 
        (
            'title'       => 'firstname',
            'type'        => 'text',
            'widget'      => 'text',
            'storage'     => 'firstname',
            'index_method' => 'noindex',
        ),
        'lastname' => array 
        (
            'title'       => 'lastname',
            'type'        => 'text',
            'widget'      => 'text',
            'storage'     => 'lastname',
            'index_method' => 'noindex',
        ),
        'email' => array 
        (
            'title'       => 'email',
            'type'        => 'text',
            'widget'      => 'text',
            'storage'     => 'email',
            'validation'  => 'email',
        ),
        'workphone' => array 
        (
            'title'       => 'work phone',
            'type'        => 'text',
            'widget'      => 'text',
            'storage'     => 'workphone'
        ),
        'workphone' => array 
        (
            'title'       => 'work phone',
            'type'        => 'text',
            'widget'      => 'text',
            'storage'     => 'workphone'
        ),
        'groups' => array
        (
            'title' => 'groups',
            'storage' => null,
            'type' => 'mnrelation',
            'type_config' => array
            (
                'mapping_class_name' => 'midcom_db_member',
                'master_fieldname' => 'uid',
                'member_fieldname' => 'gid',
                'master_is_id' => true,
                'option_callback' => 'midcom_helper_datamanager2_callback_select_grouplister',
                'option_callback_arg' => array
                (
                    'value_field' => 'official',
                    'key_field' => 'id'
                ),
            ),
            // TODO: Switch to chooser
            'widget' => 'radiocheckselect',
        ),
        /*'groups' => array
        (
            'title' => 'groups',
            'storage' => null,
            'type' => 'mnrelation',
            'type_config' => array
            (
                'mapping_class_name' => 'midcom_db_member',
                'master_fieldname' => 'uid',
                'member_fieldname' => 'gid',
                'master_is_id' => true,
                'option_callback' => 'midcom_helper_datamanager2_callback_select_grouplister',
                'option_callback_arg' => array
                (
                    'value_field' => 'official',
                    'key_field' => 'id'
                ),
            ),
            'widget' => 'chooser',
            'widget_config' => array
            (
                'clever_class' => 'group',
                'id_field' => 'id',
            ),
        ),*/
    ),
),