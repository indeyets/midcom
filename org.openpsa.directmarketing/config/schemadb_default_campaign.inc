'default' => array (

    'name'        => 'default',
    'description' => 'campaign',
    'fields'      => array (
        // Metadata
        'title' => array (
            'description' => 'title',
            'datatype'    => 'text',
            'location'    => 'title',
            'start_fieldgroup' => array (
                'title' => 'campaign',
                'css_group' => 'area',
            ),        
            'required'    => true,
        ),
        'type' => array (
            'description' => 'type',
            'datatype'    => 'integer',
            'location'    => 'orgOpenpsaObtype',
            'widget'      => 'select',
            'widget_select_choices' => array (
                ORG_OPENPSA_OBTYPE_CAMPAIGN       => 'normal campaign',
                ORG_OPENPSA_OBTYPE_CAMPAIGN_SMART => 'smart campaign',
            ),
        ),
        'description' => array (
            'description' => 'description',
            'datatype'    => 'text',
            'location'    => 'description',
            'widget_text_inputstyle' => 'longtext',
            'widget_text_height' => '8em',
        ),
        'watch_discussion' => array (
            'description' => 'discussion forum to watch',
            'datatype'    => 'text',
            'location'    => 'config',
            'config_domain' => 'org.openpsa.directmarketing',
            'config_key'    => 'watch_discussion',
            'hidden'      => true,
        ),
        'testers' => array (
            'description' => 'testers',
            'datatype'    => 'array',
            'location'    => 'testers',
            'widget'      => 'contactchooser',
            'end_fieldgroup' => '',            
        ),

        'archived' => array (
            'description' => 'archived on',
            'datatype'    => 'unixdate',
            'widget_date_enable_outside_ais' => true,
            'date_withtime' => true,
            /*
            'readonly' => true, 
            'hidden' => true,
            */
            'location'    => 'archived',
            'start_fieldgroup' => array (
                'title' => 'archival',
                'css_group' => 'area meta',
            ),       
            'end_fieldgroup' => '',                  
        ),                   
        'orgOpenpsaAccesstype' => array (
            'description' => 'access type',
            'location'    => 'config',
            'config_domain' => 'org.openpsa.core',
            'config_key' => 'orgOpenpsaAccesstype',
            'datatype'     => 'integer',
            'widget'       => 'select',
            'widget_select_choices' => $GLOBALS['org_openpsa_core_acl_options'],
            'start_fieldgroup' => array (
                'title' => 'access control',
                'css_group' => 'area acl',
            ),
            'index_merge_with_content' => false,            
        ),
        'orgOpenpsaOwnerWg' => array (
            'description'  => 'workgroup',
            'location'     => 'orgOpenpsaOwnerWg',
            'datatype'     => 'text',
            'widget'       => 'select',
            'widget_select_choices' => org_openpsa_helpers_workgroups(),   
            'default' => $GLOBALS['org_openpsa_core_workgroup_filter'], 
            'end_fieldgroup' => '',
            'index_merge_with_content' => false,            
        ),
    )
),