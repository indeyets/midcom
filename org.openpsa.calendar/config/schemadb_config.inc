'config' => array
(
    'name' => 'config',
    'title' => 'Default Configuration Schema', /* This is a topic */
    'locktimeout' => 0, /* Don't lock this, we display the edit view always */
    'fields' => array
    (
        /* view settings */
        'start_view' => array
        (
            'title' => 'which view to start in',
            'type' => 'text',
            'default' => 'week',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'org.openpsa.calendar',
                'name' => 'start_view'
            ),
            'start_fieldset' => array
            (
                'title' => 'view settings',
            ),
        ),
        'calendar_popup_height' => array
        (
            'title' => 'height of the event popup window (px)',
            'type' => 'integer',
            'default' => 450,
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'org.openpsa.calendar',
                'name' => 'calendar_popup_height'
            ),
        ),
        'calendar_popup_width' => array
        (
            'title' => 'width of the event popup window (px)',
            'type' => 'integer',
            'default' => 300,
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'org.openpsa.calendar',
                'name' => 'calendar_popup_width'
            ),
            'end_fieldset' => '',
        ),
        /* time settings */
        'day_start_time' => array
        (
            'title' => 'hour "working day" starts',
            'type' => 'integer',
            'storage' => 'config',
            'default' => 8,
            'config_domain' => 'org.openpsa.calendar',
            'config_key' => 'day_start_time',
            'start_fieldset' => array
            (
                'title' => 'time settings',
            ),
        ),
        'day_end_time' => array
        (
            'title' => 'hour "working day" ends',
            'type' => 'integer',
            'storage' => 'config',
            'default' => 18,
            'config_domain' => 'org.openpsa.calendar',
            'config_key' => 'day_end_time',
        ),
        'day_slot_length' => array
        (
            'title' => 'size of week day slot (minutes)',
            'type' => 'integer',
            'storage' => 'config',
            'default' => 60,
            'config_domain' => 'org.openpsa.calendar',
            'config_key' => 'day_slot_length',
        ),
        'week_slot_length' => array
        (
            'title' => 'size of week view slot (minutes)',
            'type' => 'integer',
            'storage' => 'config',
            'default' => 120,
            'config_domain' => 'org.openpsa.calendar',
            'config_key' => 'week_slot_length',
            'end_fieldset' => '',
        ),
        
        /* Schema settings */
        'schemadb' => array
        (
            'title' => 'schema database',
            'type' => 'text',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'org.openpsa.calendar',
                'name' => 'schemadb'
            ),
            'start_fieldset' => array
            ( 
                'title' => 'advanced schema and data settings',
            ),
        ),
        'always_show_group' => array
        (
            'title' => 'always display group',
            'type' => 'text',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'org.openpsa.calendar',
                'name' => 'always_show_group'
            ),
        ), 
        'always_show_self' => array
        (
            'title' => 'always display self',
            'type' => 'text',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'org.openpsa.calendar',
                'name' => 'always_show_self'
            ),
            'widget' => 'select',
            'widget_select_choices' => array
            (
                1 => 'yes',            
                0 => 'no',
            ),
        ),         
        'event_label' => array
        (
            'title' => 'event label field',
            'type' => 'text',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'org.openpsa.calendar',
                'name' => 'event_label'
            ),
            'end_fieldset' => '',
        ),
    ),
),