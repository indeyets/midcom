// This is for a midcom_db_person object
'default' => array 
(
    'description' => 'group',
    'l10n_db' => 'midcom.admin.user',
    'fields'      => array
    (
        'name' => array 
        (
            // COMPONENT-REQUIRED
            'title'       => 'name',
            'storage'     => 'name',
            'type'        => 'text',
            'widget'      => 'text',
            'index_method' => 'noindex',
        ),
        'official' => array
        (
            // COMPONENT-RECOMMENDED
            'title'       => 'official',
            'storage'     => 'official',
            'type'        => 'text',
            'widget'      => 'text',
            'required'    => true,
        ),
        'owner' => array
        (
            'title' => 'owner group',
            'storage' => 'owner',
            'type' => 'select',
            'type_config' => array
            (
                 'require_corresponding_option' => false,
                 'allow_multiple' => false,
                 'options' => array(),
            ),
            'widget' => 'chooser',
            'widget_config' => array
            (
                'clever_class' => 'group',
                'id_field' => 'id',
            ),
        ),
        'email' => array
        (
            'title'       => 'email',
            'type'        => 'text',
            'widget'      => 'text',
            'storage'     => 'email',
            'validation'  => 'email',
        ),
        'postcode' => array
        (
            'title'       => 'postcode',
            'type'        => 'text',
            'widget'      => 'text',
            'storage'     => 'postcode',
        ),
        'city' => array
        (
            'title'       => 'city',
            'type'        => 'text',
            'widget'      => 'text',
            'storage'     => 'city',
        ),
        'persons' => array
        (
            'title' => 'members',
            'storage' => null,
            'type' => 'mnrelation',
            'type_config' => array
            (
                'mapping_class_name' => 'midcom_db_member',
                'master_fieldname' => 'gid',
                'member_fieldname' => 'uid',
                'master_is_id' => true,
                'option_callback' => 'midcom_helper_datamanager2_callback_select_personlister',
                'option_callback_arg' => array
                (
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