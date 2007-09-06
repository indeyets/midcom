'config' => array
(
    'name'        => 'config',
    'description' => 'Default Configuration Schema', /* This is a topic */
    'locktimeout' => 0, /* Don't lock this, we display the edit view always */
    'fields'      => array
    (
  
        /**** Display settings ****/
        'index_count' => array
        (
            'description' => 'index count',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nemein.calendar',
            'config_key' => 'index_count',
            'start_fieldgroup' => array
            (
                'title' => 'display settings',
            ),
        ),
        
        'categories' => array
        (
            'description' => 'categories',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nemein.calendar',
            'config_key' => 'categories',
            'widget' => 'text',
        ),

        
        'type_filter_upcoming' => array
        (
            'description' => 'type filter for upcoming',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nemein.calendar',
            'config_key' => 'type_filter_upcoming',
        ),

        'enable_filters' => array
        (
            'description' => 'enable GET filters',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nemein.calendar',
            'config_key' => 'enable_filters',
            'widget' => 'config_radiobox',
            'widget_radiobox_choices' => array
            (
                '' => 'default setting',
                '1' => 'yes',
                '0' => 'no',
            ),
        ),
  
        'list_from_master' => array
        (
            'description' => 'list events from master event',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nemein.calendar',
            'config_key' => 'list_from_master',
            'widget' => 'config_radiobox',
            'widget_radiobox_choices' => array
            (
                '' => 'default setting',
                '1' => 'yes',
                '0' => 'no',
            ),
        ),

        'show_events_locally' => array
        (
            'description' => 'show events locally',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nemein.calendar',
            'config_key' => 'show_events_locally',
            'widget' => 'config_radiobox',
            'widget_radiobox_choices' => array
            (
                '' => 'default setting',
                '1' => 'yes',
                '0' => 'no',
            ),
        ),

        'archive_year_order' => array
        (
            'description' => 'archive year order',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nemein.calendar',
            'config_key' => 'archive_year_order',
            'widget' => 'config_radiobox',
            'widget_radiobox_choices' => array
            (
                '' => 'default setting',
                'DESC' => 'DESC',
                'ASC' => 'ASC',
            ),
            'end_fieldgroup' => '',
        ),
        
        'javascript_hover' => array
        (
            'start_fieldgroup' => array
            (
                'title' => 'calendar widget',
            ),
            'description' => 'use javascript to emulate hovering effect',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nemein.calendar',
            'config_key' => 'javascript_hover',
            'widget'   => 'config_radiobox',
            'widget_radiobox_choices' => array
            (
                '' => 'default setting',
                '1' => 'yes',
                '0' => 'no',
            ),
            'end_fieldgroup' => '',
        ),
        
         /**** Schema settings ****/
        'schemadb' => array
        (
            'description' => 'schema database',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nemein.calendar',
            'config_key' => 'schemadb',
            'start_fieldgroup' => array
            (
                'title' => 'advanced schema and data settings'
            ),
        ),
        
        'root_event' => array
        (
            'description' => 'root event guid',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nemein.calendar',
            'config_key' => 'root_event',
        ),
        
        'master_event' => array
        (
            'description' => 'master event guid',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nemein.calendar',
            'config_key' => 'master_event',
            'end_fieldgroup' => '',
        ),
    ),
),