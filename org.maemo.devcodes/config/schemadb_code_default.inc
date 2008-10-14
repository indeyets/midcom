'code' => array (
    'name'        => 'code',
    'description' => 'developer code',
    'fields'      => array
    (
        'device' => array
        (
            'title'       => 'device program',
            'type'        => 'select',
            'type_config' => array
            (
                'options' => array
                (
                    0 => 'none',
                ),
                'require_corresponding_option' => false,
                'allow_other' => true,
            ),
            'storage'     => 'device', 
            'widget'      => 'universalchooser',
            'widget_config' => array
            (
                'class' => 'org_maemo_devcodes_device_dba',
                'component' => 'org.maemo.devcodes',
                'idfield' => 'id',
                'titlefield' => 'title',
                'searchfields' => array
                (
                    'codename',
                    'title',
                ),
                'auto_wildcards' => 'both',
                'constraints' => array
                (
                    array
                    (
                        'field' => 'sitegroup',
                        'op'    => '=',
                        'value' => $_MIDGARD['sitegroup'],
                    ),
                ),
                'orders' => array
                (
                    array('title' => 'ASC'),
                    array('codename' => 'ASC'),
                ),
            ),
            'required' => true,
        ),
        'code' => array (
            'title'       => 'code',
            'type'        => 'text',
            'widget'      => 'text',
            'storage'     => 'code', 
            'required'    => true,
        ),
        'area' => array (
            'title'       => 'area',
            'type'        => 'text',
            'widget'      => 'text',
            'storage'     => 'area', 
        ),
        'recipient' => array
        (
            'title'       => 'recipient',
            'type'        => 'select',
            'type_config' => array
            (
                'options' => array
                (
                    0 => 'none',
                ),
                'require_corresponding_option' => false,
                'allow_other' => true,
            ),
            'storage'     => 'recipient', 
            'widget'      => 'universalchooser',
            'widget_config' => array
            (
                'class' => 'org_openpsa_contacts_person_dba',
                'component' => 'org.openpsa.contacts',
                'idfield' => 'id',
                'titlefield' => 'name',
                'searchfields' => array
                (
                    'email',
                    'username',
                    'firstname',
                    'lastname',
                ),
                'auto_wildcards' => 'both',
                'constraints' => array
                (
                    array
                    (
                        'field' => 'sitegroup',
                        'op'    => '=',
                        'value' => $_MIDGARD['sitegroup'],
                    ),
                ),
                'orders' => array
                (
                    array('lastname' => 'ASC'),
                    array('firstname' => 'ASC'),
                ),
                'static_options' => array
                (
                    0 => 'none',
                ),
            ),
        ),        
    ),
), // code
