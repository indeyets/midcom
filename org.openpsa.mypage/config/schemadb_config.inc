'config' => array
(
    'name'        => 'config',
    'description' => 'Default Configuration Schema', /* This is a topic */
    'locktimeout' => 0, /* Don't lock this, we display the edit view always */
    'fields'      => array
    (
        'show_buddylist' => array
        (
            'description' => 'show buddy list',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'org.openpsa.mypage',
            'config_key' => 'show_buddylist',
            'widget' => 'config_radiobox',
            'widget_radiobox_choices' => Array
            (
                '' => 'default setting',
                '1' => 'yes',
                '0' => 'no',
            ),
            'start_fieldgroup' => array
            ( 
                'title' => 'usage settings',
            ),
            'end_fieldgroup' => '',
        ),
    ),
),