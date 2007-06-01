'default' => array 
(
    'description' => 'campaign',
    'fields'      => array 
    (
        // Metadata
        'title' => array 
        (
            // COMPONENT-REQUIRED
            'title'    => 'title',
            'type'     => 'text',
            'widget'   => 'text',
            'storage'  => 'title',
            'required' => true,
        ),
        'type' => array 
        (
            'title'       => 'type',
            'storage'     => 'orgOpenpsaObtype',
            'type'        => 'select',
            'type_config' => array
            (
                'options' => array 
                (
                    ORG_OPENPSA_OBTYPE_CAMPAIGN       => 'normal campaign',
                    ORG_OPENPSA_OBTYPE_CAMPAIGN_SMART => 'smart campaign',
                ),
            ),
            'widget'      => 'select',
        ),
        'description' => array
        (
            'title' => 'description',
            'storage' => 'description',
            'type' => 'text',
            'type_config' => array 
            ( 
                'output_mode' => 'markdown' 
            ),
            'widget' => 'textarea',
        ),
        'testers' => array 
        (
            'title'       => 'testers',
            'storage'     => 'testers2',
            'type'        => 'select',
            'type_config' => array
            (
                'allow_multiple' => true,
                'require_corresponding_option' => false,
                'options' => array(),
                'multiple_storagemode' => 'array',
            ),
            'widget' => 'universalchooser',
            'widget_config' => array
            (
                'class'       => 'org_openpsa_contacts_person',
                'component'   => 'org.openpsa.contacts',
                'titlefield'  => 'name',
                'idfield'     => 'id',
                'constraints' => array(),
                'searchfields'  => array
                (
                    'firstname',
                    'lastname',
                    'username',
                ),
                'orders'        => array
                (
                    array('lastname'    => 'ASC'), 
                    array('firstname'    => 'ASC'), 

                ),
            ),
        ),
        'orgOpenpsaAccesstype' => array 
        (
            'title'       => 'access type',
            'storage'     => 'orgOpenpsaAccesstype',
            'type'        => 'select',
            'type_config' => array
            (
                'options' => $GLOBALS['org_openpsa_core_acl_options'],
            ),
            'widget'      => 'select',
        ),
        'orgOpenpsaOwnerWg' => array 
        (
            'title'       => 'workgroup',
            'storage'     => 'orgOpenpsaOwnerWg',
            'type'        => 'select',
            'type_config' => array
            (
                'options' => org_openpsa_helpers_workgroups(),
            ),
            'widget'      => 'select',
        ),
    )
),