'config' => array
(
    'name'        => 'config',
    'description' => 'Default Configuration Schema', /* This is a topic */
    'locktimeout' => 0, /* Don't lock this, we display the edit view always */
    'fields'      => array
    (
        /* view settings */
        'start_view' => array
        (
            'description'   => 'which view to start in',
            'datatype'      => 'text',
            'default'       => 'week',
            'location'      => 'config',
            'config_domain' => 'org.openpsa.calendar',
            'config_key'    => 'start_view',
            'start_fieldgroup' => array
            (
                'title' => 'view settings',
            ),
        ),
        'calendar_popup_height' => array
        (
            'description'   => 'height of the event popup window (px)',
            'datatype'      => 'integer',
            'default'       => 450,
            'location'      => 'config',
            'config_domain' => 'org.openpsa.calendar',
            'config_key'    => 'calendar_popup_height',
        ),
        'calendar_popup_width' => array
        (
            'description'   => 'width of the event popup window (px)',
            'datatype'      => 'integer',
            'default'       => 300,
            'location'      => 'config',
            'config_domain' => 'org.openpsa.calendar',
            'config_key'    => 'calendar_popup_width',
            'end_fieldgroup' => '',
        ),
        /* time settings */
        'day_start_time' => array
        (
            'description'   => 'hour "working day" starts',
            'datatype'      => 'integer',
            'location'      => 'config',
            'default'       => 8,
            'config_domain' => 'org.openpsa.calendar',
            'config_key'    => 'day_start_time',
            'start_fieldgroup' => array
            (
                'title' => 'time settings',
            ),
        ),
        'day_end_time' => array
        (
            'description'   => 'hour "working day" ends',
            'datatype'      => 'integer',
            'location'      => 'config',
            'default'       => 18,
            'config_domain' => 'org.openpsa.calendar',
            'config_key'    => 'day_end_time',
        ),
        'day_slot_length' => array
        (
            'description'   => 'size of week day slot (minutes)',
            'datatype'      => 'integer',
            'location'      => 'config',
            'default'       => 60,
            'config_domain' => 'org.openpsa.calendar',
            'config_key'    => 'day_slot_length',
        ),
        'week_slot_length' => array
        (
            'description'   => 'size of week view slot (minutes)',
            'datatype'      => 'integer',
            'location'      => 'config',
            'default'       => 120,
            'config_domain' => 'org.openpsa.calendar',
            'config_key'    => 'week_slot_length',
            'end_fieldgroup' => '',
        ),
        
        /* Schema settings */
        'schemadb' => array
        (
            'description' => 'schema database',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'org.openpsa.calendar',
            'config_key' => 'schemadb',
            'start_fieldgroup' => array
            ( 
                'title' => 'advanced schema and data settings',
            ),
        ),
        'always_show_group'  => array
        (
            'description' => 'always display group',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'org.openpsa.calendar',
            'config_key' => 'always_show_group',
        ), 
        'always_show_self'  => array
        (
            'description' => 'always display self',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'org.openpsa.calendar',
            'config_key' => 'always_show_self',
            'widget'       => 'select',
            'widget_select_choices' => array(
                1 => 'yes',            
                0 => 'no',
            ),
        ),         
        'event_label'  => array
        (
            'description' => 'event label field',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'org.openpsa.calendar',
            'config_key' => 'event_label',
            'end_fieldgroup' => '',
        ),
    ),
),