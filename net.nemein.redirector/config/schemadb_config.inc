'config' => array
(
    'name'        => 'config',
    'description' => 'Default Configuration Schema', /* This is a topic */
    'locktimeout' => 0, /* Don't lock this, we display the edit view always */
    'fields'      => array
    (
        /* redirection settings */
        'redirection_type'  => array
        (
            'description' => 'redirection type',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nemein.redirector',
            'config_key'    => 'redirection_type',
            'widget'       => 'select',
            'widget_select_choices' => array(
                'subnode' => 'first subfolder',            
                'url'     => 'url entered below',
            ),
            'start_fieldgroup' => array
            (
                'title' => 'redirection settings',
            ),            
        ),           
        'redirection_url' => array
        (
            'description'   => 'redirection url',
            'datatype'      => 'text',
            'location'      => 'config',
            'config_domain' => 'net.nemein.redirector',
            'config_key'    => 'redirection_url',
        ),
        'redirection_code' => array
        (
            'description'   => 'redirection http code',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nemein.redirector',
            'config_key'    => 'redirection_code',
            'widget'       => 'select',
            'widget_select_choices' => array(
                301 => '301 Moved Permanently',            
                302 => '302 Found',
                410 => '410 Gone',
            ),
            'default'        => 302,
            'end_fieldgroup' => '',            
        ),
    ),
),