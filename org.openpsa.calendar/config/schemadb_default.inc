'default' => array (

    'name'        => 'default',
    'description' => 'event',
    'fields'      => array (
        //Hidden field for orgOpenpsaObtype
        'object_type'  => array(
            'default'  => ORG_OPENPSA_OBTYPE_EVENT,
            'hidden'   => true,
            'location' => 'orgOpenpsaObtype',
            'datatype' => 'integer',
        ),
        'title' => array (
            'description' => 'title',
            'datatype'    => 'text',
            'location'    => 'title',
            'start_fieldgroup' => array (
                'title' => 'event information',
                'css_group' => 'area',
            ),
            'required'    => true,
        ),
        'location' => array (
            'description' => 'location',
            'datatype'    => 'text',
            'location'    => 'location',
        ),
        'start' => array (
            'description' => 'start time',
            'datatype'    => 'unixdate',
            'location'    => 'start',
            'date_withtime' => true,
            'widget_date_enable_outside_ais' => true,
        ),        
        'end' => array (
            'description' => 'end time',
            'datatype'    => 'unixdate',
            'location'    => 'end',
            'date_withtime' => true,
            'widget_date_enable_outside_ais' => true,
        ),        
        'description' => array (
            'description' => 'description',
            'datatype'    => 'markdown',
            'location'    => 'description',
            'widget_text_inputstyle' => 'longtext',
            'end_fieldgroup' => '',
        ),
        'participants' => array (
            'description' => 'participants',
            'datatype'    => 'array',
            'location'    => 'participants',
            'widget'      => 'contactchooser',
            'default'     => org_openpsa_helpers_resources(),
            'start_fieldgroup' => array (
                'title' => 'participants',
                'css_group' => 'area',
            ),
        ),
        'orgOpenpsaAccesstype' => array (
            'description' => 'access type',
            'location'    => 'orgOpenpsaAccesstype',
            'datatype'     => 'integer',
            'widget'       => 'select',
            'widget_select_choices' => array(
                ORG_OPENPSA_ACCESSTYPE_PUBLIC => 'public',            
                ORG_OPENPSA_ACCESSTYPE_PRIVATE => 'private',
            ),
        ),       
        'busy' => array (
            'description' => 'dont allow overlapping',
            'datatype'    => 'integer',
            'widget'      => 'checkbox',
            'location'    => 'busy',
            'default'     => 1,
            'end_fieldgroup' => '',
        ),
    ),
),
