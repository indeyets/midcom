'default' => array (

    'name'        => 'default',
    'description' => 'notifications',
    'l10n_db'     => 'org.openpsa.notifications',
    'fields'      => array (
        'org_openpsa_calendar_event_update' => array (
            'description' => 'org.openpsa.calendar:event_update',
            'datatype'    => 'text',
            'location'    => 'config',
            'config_domain' => 'org.openpsa.notifications',
            'config_key'    => 'org.openpsa.calendar:event_update',
            'widget'      => 'select',
            'widget_select_choices' => array(
                '' => 'inherit',
                'none' => 'none',                
                'email' => 'email',
            ),
            'start_fieldgroup' => array (
                'title' => 'org.openpsa.calendar',
                'css_group' => 'area',
            ),
        ),
        'org_openpsa_calendar_event_add' => array (
            'description' => 'org.openpsa.calendar:event_add',
            'datatype'    => 'text',
            'location'    => 'config',
            'config_domain' => 'org.openpsa.notifications',
            'config_key'    => 'org.openpsa.calendar:event_add',
            'widget'      => 'select',
            'widget_select_choices' => array(
                '' => 'inherit',
                'none' => 'none',                
                'email' => 'email',
            ),
        ),
        'org_openpsa_calendar_event_remove' => array (
            'description' => 'org.openpsa.calendar:event_remove',
            'datatype'    => 'text',
            'location'    => 'config',
            'config_domain' => 'org.openpsa.notifications',
            'config_key'    => 'org.openpsa.calendar:event_remove',
            'widget'      => 'select',
            'widget_select_choices' => array(
                '' => 'inherit',
                'none' => 'none',                
                'email' => 'email',
            ),
        ),
        'org_openpsa_calendar_event_cancel' => array (
            'description' => 'org.openpsa.calendar:event_cancel',
            'datatype'    => 'text',
            'location'    => 'config',
            'config_domain' => 'org.openpsa.notifications',
            'config_key'    => 'org.openpsa.calendar:event_cancel',
            'widget'      => 'select',
            'widget_select_choices' => array(
                '' => 'inherit',
                'none' => 'none',                
                'email' => 'email',
            ),
            'end_fieldgroup' => '',
        ),
    )
),


