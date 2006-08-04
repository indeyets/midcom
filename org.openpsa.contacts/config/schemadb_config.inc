'config' => array
(
    'name'        => 'config',
    'description' => 'Default Configuration Schema', /* This is a topic */
    'locktimeout' => 0, /* Don't lock this, we display the edit view always */
    'fields'      => array
    (
        'enable_dbe' => array
        (
            'description' => 'enable digital business ecosystem',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'org.openpsa.contacts',
            'config_key' => 'enable_dbe',
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
        
        /* Schema settings */
        'organization_search_fields' => array
        (
            'description' => 'organization search fields',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'org.openpsa.contacts',
            'config_key' => 'organization_search_fields',
            'start_fieldgroup' => array
            ( 
                'title' => 'advanced schema and data settings',
            ),
        ),
        'person_search_fields' => array
        (
            'description' => 'person search fields',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'org.openpsa.contacts',
            'config_key' => 'person_search_fields',
        ),
        'schemadb_group' => array
        (
            'description' => 'organization schema database',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'org.openpsa.contacts',
            'config_key' => 'schemadb_group',
        ),
        'schemadb_person' => array
        (
            'description' => 'person schema database',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'org.openpsa.contacts',
            'config_key' => 'schemadb_person',
        ),
        'schemadb_acl' => array
        (
            'description' => 'permission schema database',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'org.openpsa.contacts',
            'config_key' => 'schemadb_acl',
        ),
        'schemadb_notifications' => array
        (
            'description' => 'notification schema database',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'org.openpsa.contacts',
            'config_key' => 'schemadb_notifications',
            'end_fieldgroup' => '',
        ),
    ),
),