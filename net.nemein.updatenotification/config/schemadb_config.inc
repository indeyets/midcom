'config' => array (
	'name'        => 'config',
	'description' => 'Default Configuration Schema',
	'locktimeout' => 0, /* Don't lock this, we always display the edit view */
	'fields'      => array (
		'display_levels' => array (
			'description' => 'display_levels',
			'datatype' => 'text',
			'location' => 'config',
			'config_domain' => 'net.nemein.updatenotification',
			'config_key' => 'display_levels',
			'widget' => 'select',
			'widget_select_choices' => Array (
				'' => 'default setting',
				'1' => '1',
				'2' => '2',
				'3' => '3',
				'4' => '4',
				'5' => '5',
				'6' => '6',
				'7' => '7',
				'8' => '8',
				'9' => '9',
				'999' => 'unlimited',
			),
		),
		'display_root_topic' => array 
        (
            'description'   => 'root topic to use',
            'datatype'      => 'text',
            'location'      => 'config',
            'config_domain' => 'net.nemein.updatenotification',
            "config_key"    => 'display_root_topic',
            'widget'        => 'select',
            'widget_select_choices' => net_nemein_updatenotification_viewer::list_root_nodes(),
        ),
        'display_text_before_form' => array 
        (
            'description'   => 'text before form',
            'datatype'      => 'text',
            'location'      => 'config',
            'config_domain' => 'net.nemein.updatenotification',
            "config_key"    => 'display_text_before_form',
            'widget'        => 'tinymce',
        ),
        'preferred_notification_methods' => array (
			'description' => 'preferred_notification_methods',
			'datatype'      => 'multiselect',
			'location' => 'config',
			'config_domain' => 'net.nemein.updatenotification',
			'config_key' => 'preferred_notification_methods',
			'widget' => 'multiselect',
			'widget_select_size' => 10,
			'multiselect_selection_list' => Array (
				'emial' => 'email',
				'sms' => 'sms',
			),
		),
        

	)
),
