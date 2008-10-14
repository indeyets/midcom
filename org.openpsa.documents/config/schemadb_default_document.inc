'default' => array (

    'name'        => 'default',
    'description' => 'Document',
    'fields'      => array (
        // Metadata
        //Hidden field for orgOpenpsaObtype
        'object_type'  => array(
            'description' => 'object type',
            'default'  => ORG_OPENPSA_OBTYPE_DOCUMENT,
            'hidden'   => true,
            //'readonly'   => true,
            'location' => 'orgOpenpsaObtype',
            'datatype' => 'integer',
        ),
        'title' => array (
            'description' => 'Title',
            'datatype'    => 'text',
            'location'    => 'title',
            'start_fieldgroup' => array (
                'title' => 'Document',
                'css_group' => 'area',
            ),
            'required'    => true,
        ),
        'document' => array (
            'description' => 'Document',
            'datatype'    => 'blob',
            'datatype_blob_autoindex' => true,
            'widget_blob_simple' => true,
            'end_fieldgroup' => '',
        ),
        'keywords' => array (
            'description' => 'Keywords',
            'datatype'    => 'text',
            'location'    => 'keywords',
            'start_fieldgroup' => array (
                'title' => 'Metadata',
                'css_group' => 'area meta',
            ),
        ),
        'author' => array (
            'description' => 'Author',
            'datatype'    => 'integer',
            'location'    => 'author',
            'widget'      => 'contactchooser',
            'index_merge_with_content' => false,
            'default'     => $_MIDCOM->auth->user->id,
        ),
        'abstract' => array (
            'description' => 'Abstract',
            'datatype'    => 'text',
            'location'    => 'abstract',
            'widget_text_inputstyle' => 'longtext',
        ),
        'status_select' => array (
            'description' => 'Document status',
            'location'    => 'docStatus',
            'datatype'     => 'integer',
            'widget'       => 'select',
            'widget_select_choices' => array (
                ORG_OPENPSA_DOCUMENT_STATUS_DRAFT  => 'Draft',
                ORG_OPENPSA_DOCUMENT_STATUS_REVIEW => 'Review',
                ORG_OPENPSA_DOCUMENT_STATUS_FINAL  => 'Final',
            ),
            'end_fieldgroup' => '',
            'index_merge_with_content' => false,
        ),
        'orgOpenpsaAccesstype' => array (
            'description' => 'Access type',
            'location'    => 'orgOpenpsaAccesstype',
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
            'location'    => 'orgOpenpsaOwnerWg',
            'datatype'     => 'text',
            'widget'       => 'select',
            'widget_select_choices' => org_openpsa_helpers_list::workgroups(),
            'default' => $GLOBALS['org_openpsa_core_workgroup_filter'],
            'end_fieldgroup' => '',
            'index_merge_with_content' => false,
        ),
    )
),

'newdocument' => array (

    'name'        => 'newdocument',
    'description' => 'New document',
    'fields'      => array (
        // Metadata
        //Hidden field for orgOpenpsaObtype
        'object_type'  => array(
            'default'  => ORG_OPENPSA_OBTYPE_DOCUMENT,
            'hidden'   => true,
            'location' => 'orgOpenpsaObtype',
            'datatype' => 'integer',
        ),
        //Hidden field for status
        'object_type'  => array(
            'default'  => ORG_OPENPSA_DOCUMENT_STATUS_DRAFT,
            'hidden'   => true,
            'location' => 'docStatus',
            'datatype' => 'integer',
        ),
        'title' => array (
            'description' => 'Title',
            'datatype'    => 'text',
            'location'    => 'title',
            'start_fieldgroup' => array (
                'title' => 'New document',
                'css_group' => 'area',
            ),
            'required'    => true,
        ),
        'document' => array (
            'description' => 'Document',
            'datatype'    => 'blob',
            'datatype_blob_autoindex' => true,
            'widget_blob_simple' => true,
            'end_fieldgroup' => '',
        ),
        'orgOpenpsaAccesstype' => array (
            'description' => 'Access type',
            'location'    => 'orgOpenpsaAccesstype',
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
            'location'    => 'orgOpenpsaOwnerWg',
            'datatype'     => 'text',
            'widget'       => 'select',
            'widget_select_choices' => org_openpsa_helpers_list::workgroups(),
            'default' => $GLOBALS['org_openpsa_core_workgroup_filter'],
            'end_fieldgroup' => '',
            'index_merge_with_content' => false,
        ),
    )
)