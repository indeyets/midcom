'default' => array (
    //When changing this remember to update relevant parts of newgroup as well
    'name'        => 'default',
    'description' => 'group',
    'fields'      => array (
        // Metadata
        'official' => array (
            'description' => 'official name',
            'datatype'    => 'text',
            'location'    => 'official',
            'start_fieldgroup' => array (
                'title' => 'basic information',
                'css_group' => 'area meta',
            ),
            'required'    => true,
            'index_method' => 'title',            
        ),
        'object_type'  => array(
            'description'  => 'organization type',
            'location'     => 'orgOpenpsaObtype',
            'datatype'     => 'integer',
            'widget'       => 'select',
            'widget_select_choices' => array (
                ORG_OPENPSA_OBTYPE_ORGANIZATION => 'organization',
                ORG_OPENPSA_OBTYPE_DAUGHTER     => 'daughter organization',
                ORG_OPENPSA_OBTYPE_DEPARTMENT   => 'department',
            ),
            'index_merge_with_content' => false,            
        ),
        'members' => array (
            'description' => 'contacts',
            'datatype'    => 'array',
            'location'    => 'members',
            'widget'      => 'contactchooser',
            'hidden'      => true,
        ),
        'customer_id' => array (
            'description' => 'Customer ID',
            'datatype'    => 'text',
            'location'    => 'customerId',
            'index_merge_with_content' => false,            
        ),
        'company_id' => array (
            'description' => 'company id',
            'datatype'    => 'text',
            'location'    => 'name',
            'index_merge_with_content' => false,            
        ),
        'categories' => array (
            'description' => 'categories',
            'datatype'    => 'multiselect',
            'multiselect_selection_list' => array (
                '' => '',
                'org_openpsa_category_partner'  => 'partner',
                'org_openpsa_category_client'   => 'client',
                'org_openpsa_category_vendor'   => 'vendor',
            ),
            'index_merge_with_content' => false,              
        ),
        'keywords' => array (
            'description' => 'keywords',
            'datatype'    => 'text',
            'location'    => 'keywords',
        ),        
        'logo' => array (
            'description' => 'logo',
            'datatype'    => 'image',
            'widget_image_simple' => true,
            'end_fieldgroup' => '',
        ),
        // Contact information
        'homepage' => array (
            'description' => 'homepage',
            'datatype'    => 'text',
            'location'    => 'homepage',
            'start_fieldgroup' => array (
                'title' => 'contact information',
                'css_group' => 'area contact',
            ),            
        ),
        'email' => array (
            'description' => 'email',
            'datatype'    => 'text',
            'location'    => 'email',
        ),
        'phone' => array (
            'description' => 'phone',
            'datatype'    => 'text',
            'location'    => 'phone',
        ),
        'fax' => array (
            'description' => 'fax',
            'datatype'    => 'text',
            'location'    => 'fax',
            'end_fieldgroup' => '',
        ),
        // Contact/Visiting address
        'address_visiting_street' => array (
            'description' => 'street address',
            'datatype'    => 'text',
            'location'    => 'street',
            'start_fieldgroup' => array (
                'title' => 'visiting address',
                'css_group' => 'area',
            ),
        ),
        'address_visiting_postcode' => array (
            'description' => 'postcode',
            'datatype'    => 'text',
            'location'    => 'postcode',
        ),
        'address_visiting_city' => array (
            'description' => 'city',
            'datatype'    => 'text',
            'location'    => 'city',
        ),
        'address_visiting_country' => array (
            'description' => 'country',
            'datatype'    => 'text',
            'location'    => 'country',
            'end_fieldgroup' => '',
        ),
        // Contact/Postal address
        'address_postal_street' => array (
            'description' => 'street address',
            'datatype'    => 'text',
            'location'    => 'postalStreet',
            'start_fieldgroup' => array (
                'title' => 'postal address',
                'css_group' => 'area contact postal',
            ),
        ),
        'address_postal_postcode' => array (
            'description' => 'postcode',
            'datatype'    => 'text',
            'location'    => 'postalPostcode',
        ),
        'address_postal_city' => array (
            'description' => 'city',
            'datatype'    => 'text',
            'location'    => 'postalCity',
        ),
        'address_postal_country' => array (
            'description' => 'country',
            'datatype'    => 'text',
            'location'    => 'postalCountry',
            'end_fieldgroup' => '',
        ),
        // Contact/Invoicing address
        'address_invoice_street' => array (
            'description' => 'street address',
            'datatype'    => 'text',
            'location'    => 'invoiceStreet',
            'start_fieldgroup' => array (
                'title' => 'invoicing address',
                'css_group' => 'area contact postal',
            ),
        ),
        'address_invoice_postcode' => array (
            'description' => 'postcode',
            'datatype'    => 'text',
            'location'    => 'invoicePostcode',
        ),
        'address_invoice_city' => array (
            'description' => 'city',
            'datatype'    => 'text',
            'location'    => 'invoiceCity',
        ),
        'address_invoice_country' => array (
            'description' => 'country',
            'datatype'    => 'text',
            'location'    => 'invoiceCountry',
            'end_fieldgroup' => '',
        ),
        'notes' => array (
            'description' => 'notes',
            'datatype'    => 'markdown',
            'widget_text_inputstyle' => 'longtext',
            'location'    => 'extra',
            'start_fieldgroup' => array (
                'title' => 'notes',
                'css_group' => 'area notes',
            ),
            'end_fieldgroup' => '',
        ),


    )
),

'newgroup' => array (

    'name'        => 'newgroup',
    'description' => 'new group',
    'fields'      => array (
        // Metadata
        'official' => array (
            'description' => 'official name',
            'datatype'    => 'text',
            'location'    => 'official',
            'start_fieldgroup' => array (
                'title' => 'new organization',
                'css_group' => 'area meta',
            ),
            'required'    => true,
        ),
        'object_type'  => array(
            'description'  => 'organization type',
            'location'     => 'orgOpenpsaObtype',
            'datatype'     => 'integer',
            'widget'       => 'select',
            'widget_select_choices' => array (
                ORG_OPENPSA_OBTYPE_ORGANIZATION => 'organization',
                ORG_OPENPSA_OBTYPE_DAUGHTER     => 'daughter organization',
                ORG_OPENPSA_OBTYPE_DEPARTMENT   => 'department',
            ),
        ),
        'company_id' => array (
            'description' => 'company id',
            'datatype'    => 'text',
            'location'    => 'name'
        ),
        'categories' => array (
            'description' => 'categories',
            'datatype'    => 'multiselect',
            'multiselect_selection_list' => array (
                '' => '',
                'org_openpsa_category_partner'  => 'partner',
                'org_openpsa_category_client'   => 'client',
                'org_openpsa_category_vendor'   => 'vendor',
            ),
        ),
        'homepage' => array (
            'description' => 'homepage',
            'datatype'    => 'text',
            'location'    => 'homepage',
            'end_fieldgroup' => '',    
        ),
    )
)

