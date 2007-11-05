'config' => array
(
    'name'        => 'config',
    'description' => 'Default Configuration Schema', /* This is a topic */
    'locktimeout' => 0, /* Don't lock this, we display the edit view always */
    'fields'      => array
    (
        'description_before_listing' => array
        (
            'description' => 'description_before_listing',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nemein.reservations',
            'config_key' => 'description_before_listing',
            'widget' => 'html',
            'widget_text_height' => '6em',
            'widget_text_width' => '20em',
            'helptext'  => 'description_before_listing_helptext',
        ),
        'schema_resource' => array
        (
            'description' => 'schema_resource',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nemein.reservations',
            'config_key' => 'schema_resource',
            'start_fieldgroup' => array
            (
                'title' => 'data schema settings',
            ),
        ),
        'schema_reservation' => array
        (
            'description' => 'schema_reservation',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nemein.reservations',
            'config_key' => 'schema_reservation',
        ),
        'javascript_hover' => array
        (
            'description' => 'enable javascript hover',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nemein.reservations',
            'config_key' => 'javascript_hover',
            'widget' => 'config_radiobox',
            'widget_radiobox_choices' => array
            (
                '' => 'default setting',
                'true' => 'yes',
                'false' => 'no',
            ),
        ),
        'enable_multiple_calendars' => array
        (
            'description' => 'enable multiple calendars for comparison',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nemein.reservations',
            'config_key' => 'enable_multiple_calendars',
            'widget' => 'config_radiobox',
            'widget_radiobox_choices' => array
            (
                '' => 'default setting',
                'true' => 'yes',
                'false' => 'no',
            ),
        ),
        'add_creator_to_reservation' => array
        (
            'description' => 'add creator to reservation',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nemein.reservations',
            'config_key' => 'add_creator_to_reservation',
            'widget' => 'config_radiobox',
            'widget_radiobox_choices' => array
            (
                '' => 'default setting',
                'true' => 'yes',
                'false' => 'no',
            ),
            'end_fieldgroup' => '',
        ),
        'new_reservation_email' => array
        (
            'description' => 'new_reservation_email',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nemein.reservations',
            'config_key' => 'new_reservation_email',
            'start_fieldgroup' => array
            (
                'title' => 'New reservation email notification',
            ),
        ),
        'new_reservation_email_reply' => array
        (
            'description' => 'new_reservation_email_reply',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nemein.reservations',
            'config_key' => 'new_reservation_email_reply',
        ),
        'new_reservation_email_bcc' => array
        (
            'description' => 'new_reservation_email_bcc',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nemein.reservations',
            'config_key' => 'new_reservation_email_bcc',
        ),
        'new_reservation_email_title' => array
        (
            'description' => 'new_reservation_email_title',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nemein.reservations',
            'config_key' => 'new_reservation_email_title',
        ),
        'new_reservation_email_body' => array
        (
            'description' => 'new_reservation_email_body',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nemein.reservations',
            'config_key' => 'new_reservation_email_body',
            'widget' => 'html',
            'helptext'  => 'new_reservation_email_body',
            'end_fieldgroup' => '',
        ),   
    ),
),