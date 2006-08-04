'default' => array (
    'name'        => 'default',
    'description' => 'Horde/Imp account settings',
    'fields'      => array (
            'uri'  => array (
                'description'   => 'Horde/Imp server (fully qualified) URL',
                'datatype'      => 'text',
                'location'      => 'config',
                'config_domain' => 'org.openpsa.imp',
                'config_key'    => 'imp_uri',
                'start_fieldgroup'  => array (
                    'title'     => 'Account information',
                    'css_group' => 'area',
                ),
            ),
            'username'  => array (
                'description'   => 'Horde/Imp username',
                'datatype'      => 'text',
                'location'      => 'config',
                'config_domain' => 'org.openpsa.imp',
                'config_key'    => 'imp_username',
            ),
            'password'  => array (
                'description'   => 'Horde/Imp password',
                'datatype'      => 'text',
                'location'      => 'config',
                'config_domain' => 'org.openpsa.imp',
                'config_key'    => 'imp_password',
                'end_fieldgroup'    => '',
            ),
    ),
),
'globalserver' => array (
    'name'        => 'globalserver',
    'description' => 'Horde/Imp account settings',
    'fields'      => array (
            'username'  => array (
                'description'   => 'Horde/Imp username',
                'datatype'      => 'text',
                'location'      => 'config',
                'config_domain' => 'org.openpsa.imp',
                'config_key'    => 'imp_username',
                'start_fieldgroup'  => array (
                    'title'     => 'Account information',
                    'css_group' => 'area',
                ),
            ),
            'password'  => array (
                'description'   => 'Horde/Imp password',
                'datatype'      => 'text',
                'location'      => 'config',
                'config_domain' => 'org.openpsa.imp',
                'config_key'    => 'imp_password',
                'end_fieldgroup'    => '',
            ),
    ),
),
