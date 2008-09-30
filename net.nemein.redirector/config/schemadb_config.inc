'config' => array
(
    'description' => 'Default Configuration Schema', /* This is a topic */
    'locktimeout' => 0, /* Don't lock this, we display the edit view always */
    'fields' => array
    (
        /* redirection settings */
        'redirection_type' => array
        (
            'title' => 'redirection type',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'net.nemein.redirector',
                'name' => 'redirection_type',
            ),
            'type' => 'select',
            'type_config' => array
            (
                'options' => array
                (
                    'subnode' => 'first subfolder',
                    'node' => 'selected node',
                    'url' => 'url entered below',
                ),
            ),
            'widget' => 'select',
            'start_fieldset' => array
            (
                'title' => 'redirection settings',
            ),
        ),
        'redirection_url' => array
        (
            'title' => 'redirection url',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'net.nemein.redirector',
                'name' => 'redirection_url',
            ),
            'type' => 'text',
            'widget' => 'text',
        ),
        'redirection_node' => array
        (
            'title' => 'redirection node',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'net.nemein.redirector',
                'name' => 'redirection_node',
            ),
            'type' => 'select',
            'type_config' => array
            (
                'options' => array(),
                'allow_other' => true,
                'require_corresponding_option' => false,
            ),
            'widget' => 'chooser',
            'widget_config' => array
            (
                'class'       => 'midcom_db_topic',
                'component'   => 'net.nemein.redirector',
                'titlefield'  => 'extra',
                'idfield'     => 'id',
                'searchfields' => array
                (
                    array
                    (
                        'title' => 'ASC',
                    ),
                    array
                    (
                        'extra' => 'ASC',
                    ),
                    array
                    (
                        'component' => 'ASC',
                    ),
                    array
                    (
                        'name' => 'ASC',
                    ),
                ),
                'constraints' => array
                (
                    array
                    (
                        'field' => 'extra',
                        'op' => '<>',
                        'value' => '',
                    ),
                ),
                'result_headers' => array
                (
                    array
                    (
                        'name' => 'extra',
                        'title' => 'extra',
                    ),
                    array
                    (
                        'name' => 'name',
                        'title' => 'name',
                    ),
                    array
                    (
                        'name' => 'component',
                        'title' => 'component',
                    ),
                ),
                'orders' => array
                (
                    array
                    (
                        'title' => 'ASC',
                    ),
                    array
                    (
                        'extra' => 'ASC',
                    ),
                    array
                    (
                        'name' => 'ASC',
                    ),
                ),
            ),
        ),
        'redirection_code' => array
        (
            'title' => 'redirection http code',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'net.nemein.redirector',
                'name' => 'redirection_code',
            ),
            'type' => 'select',
            'type_config' => array
            (
                'options' => array
                (
                    301 => '301 Moved Permanently',
                    302 => '302 Found',
                    410 => '410 Gone',
                ),
            ),
            'widget' => 'select',
            'default' => 302,
        ),
        'admin_redirection' => array
        (
            'title' => 'redirect administrators',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'net.nemein.redirector',
                'name' => 'admin_redirection',
            ),
            'type' => 'select',
            'type_config' => array
            (
                'options' => array
                (
                    '' => 'default setting',
                    1 => 'yes',
                    0 => 'no',
                ),
            ),
            'widget' => 'select',
            'end_fieldset' => 1,
        ),
    ),
),