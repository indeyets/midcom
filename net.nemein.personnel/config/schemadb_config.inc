'config' => array (
    'name'        => 'config',
    'description' => 'Default Configuration Schema', /* This is a topic */
    'locktimeout' => 0, /* Don't lock this, we display the edit view always */
    'fields'      => array (
  
        /**** Display settings ****/
        'group' => array (
            'description' => 'group',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nemein.personnel',
            'config_key' => 'group',
            'widget'   => 'select',
            /* TODO: This can be quite slow */
            'widget_select_choices' => midcom_helper_datamanager_selectlist_allgroups(),
            'start_fieldgroup' => Array ( 
                'title' => 'list settings',
            ),
            'end_fieldgroup' => '',
        ),
         
         /**** Schema settings ****/
        'schemadb' => array (
            'description' => 'schema database',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nemein.personnel',
            'config_key' => 'schemadb',
            'start_fieldgroup' => Array ( 
                'title' => 'advanced schema and data settings'
            ),
            'end_fieldgroup' => '',
        ),              
    )
),