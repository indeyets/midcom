'default' => array (

    'name'        => 'default',
    'description' => 'Directory',
    'fields'      => array (
        // Metadata
        'extra' => array (
            'description' => 'Title',
            'datatype'    => 'text',
            'location'    => 'extra',
            'start_fieldgroup' => array (
                'title' => 'Directory',
                'css_group' => 'area',
            ),
            'end_fieldgroup' => '',          
            'required'    => true,
        ),  
        'orgOpenpsaAccesstype' => array (
            'description' => 'Access type',
            'location'    => 'config',
            'config_domain' => 'org.openpsa.core',
            'config_key' => 'orgOpenpsaAccesstype',
            'datatype'     => 'integer',
            'widget'       => 'select',
            'widget_select_choices' => $GLOBALS['org_openpsa_core_acl_options'],
            'start_fieldgroup' => array (
                'title' => 'Access control',
                'css_group' => 'area acl',
            ),
            'index_merge_with_content' => false,            
        ),
        'orgOpenpsaOwnerWg' => array (
            'description' => 'Workgroup',
            'location'    => 'config',
            'config_domain' => 'org.openpsa.core',
            'config_key' => 'orgOpenpsaOwnerWg',
            'datatype'     => 'text',
            'widget'       => 'select',
            'widget_select_choices' => org_openpsa_helpers_workgroups(),   
            'default' => $GLOBALS['org_openpsa_core_workgroup_filter'], 
            'end_fieldgroup' => '',
            'index_merge_with_content' => false,            
        ),
    )
),