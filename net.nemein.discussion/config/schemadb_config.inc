'config' => array
(
    'name'        => 'config',
    'description' => 'Default Configuration Schema',
    'locktimeout' => 0,
    'fields'      => array
    (
        // **** Email Stuff ****

        'api_email_enable' => array
        (
            'description' => 'enable email import API',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nemein.discussion',
            'config_key' => 'api_email_enable',
            'widget' => 'config_radiobox',
            'widget_radiobox_choices' => Array
            (
                '0' => 'no',
                '1' => 'yes',
            ),
            'start_fieldgroup' => Array ( 'title' => 'email export/import settings' ),
        ),
        'email_out_enable' => array
        (
            'description' => 'enable sending of new posts as emails',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nemein.discussion',
            'config_key' => 'email_out_enable',
            'widget' => 'config_radiobox',
            'widget_radiobox_choices' => Array
            (
                '0' => 'no',
                '1' => 'yes',
            ),
        ),
        'email_out_to' => array
        (
            'description' => 'address to send posts to',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nemein.discussion',
            'config_key' => 'email_out_to',
        ),
        'email_out_from' => array
        (
            'description' => 'override sender address',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nemein.discussion',
            'config_key' => 'email_out_from',
        ),
        'email_out_subject_prefix' => array
        (
            'description' => 'prefix this string to outgoing emails',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nemein.discussion',
            'config_key' => 'email_out_subject_prefix',
            'end_fieldgroup' => '',
        ),

        /* Schema settings */
        'schemadb' => array
        (
            'description' => 'schema database',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nemein.discussion',
            'config_key' => 'schemadb',
            'start_fieldgroup' => array
            ( 
                'title' => 'advanced schema and data settings'
            ),
            'end_fieldgroup' => '',
        ),
    ),
),