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
            'widget' => 'chooser',
            'widget_config' => array
            (
                'id_field' => 'id',
                'result_headers' => array
                (
                    array(
                        'name' => 'name',
                        'title' => 'Name',
                    ),
                    array(
                        'name' => 'email',
                        'title' => 'Email',
                    ),
                ),
            ),
        ),
        'centralized_toolbar' => array
        (
            'title'       => 'enable centralized toolbar',
            'type'        => 'privilege',
            'type_config' => array
            (
                'privilege_name' => 'midcom:centralized_toolbar',
                'assignee'       => 'SELF',
                'classname'      => 'midcom_services_toolbars',
            ),
            'widget'      => 'privilege',
            'storage'     => null,
        ),
        'ajax_toolbar' => array
        (
            'title'       => 'enable ajax in toolbar',
            'type'        => 'privilege',
            'type_config' => array
            (
                'privilege_name' => 'midcom:ajax',
                'assignee'       => 'SELF',
                'classname'      => 'midcom_services_toolbars',
            ),
            'widget'      => 'privilege',
            'storage'     => null,
        ),
        'ajax_uimessages' => array
        (
            'title'       => 'enable ajax in uimessages',
            'type'        => 'privilege',
            'type_config' => array
            (
                'privilege_name' => 'midcom:ajax',
                'assignee'       => 'SELF',
                'classname'      => 'midcom_services_uimessages',
            ),
            'widget'      => 'privilege',
            'storage'     => null,
        ),
        'asgard_access' => array
        (
            'title'       => 'enable asgard',
            'type'        => 'privilege',
            'type_config' => array
            (
                'privilege_name' => 'midgard.admin.asgard:access',
                'assignee'       => 'SELF',
                'classname'      => 'midgard_admin_asgard_plugin',
            ),
            'widget'      => 'privilege',
            'storage'     => null,
        ),
        'break_lock' => array
        (
            'title'       => 'enable breaking of the editing lock',
            'type'        => 'privilege',
            'type_config' => array
            (
                'privilege_name' => 'midcom.helper.datamanager2:break_lock',
                'assignee'       => 'SELF',
                'classname'      => 'midcom_helper_datamanager2_controller',
            ),
            'widget'      => 'privilege',
            'storage'     => null,
        ),
    ),
),