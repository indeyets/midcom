'default' => array (

    'name'        => 'default',
    'description' => 'person',
    'fields'      => array (
        // Metadata
        //Hidden field for orgOpenpsaObtype
        'object_type'  => array(
            'decription' => 'object type',
            'default'  => ORG_OPENPSA_OBTYPE_PERSON,
            'hidden'   => true,
            //'readonly'   => true,
            'location' => 'orgOpenpsaObtype',
            'datatype' => 'integer',
        ),
        'firstname' => array (
            'description' => 'firstname',
            'datatype'    => 'text',
            'location'    => 'firstname',
            'start_fieldgroup' => array (
                'title' => 'basic information',
                'css_group' => 'area meta',
            ),
            'required'    => true,
            'index_method' => 'title',
        ),
        'lastname' => array (
            'description' => 'lastname',
            'datatype'    => 'text',
            'location'    => 'lastname',
        ),
        'photo' => array (
            'description' => 'photo',
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
            'index_merge_with_content' => false,
        ),
        'email' => array (
            'description' => 'email',
            'datatype'    => 'text',
            'location'    => 'email',
        ),
        'handphone' => array (
            'description' => 'mobile phone',
            'datatype'    => 'text',
            'location'    => 'handphone',
        ),
        'workphone' => array (
            'description' => 'work phone',
            'datatype'    => 'text',
            'location'    => 'workphone',
        ),
        'fax' => array (
            'description' => 'fax',
            'datatype'    => 'text',
            'location'    => 'fax',
        ),
        'homephone' => array (
            'description' => 'homephone',
            'datatype'    => 'text',
            'location'    => 'homephone',
        ),
        'skype' => array (
            'description' => 'skype name',
            'datatype'    => 'text',
            'location'    => 'config',
            'config_domain' => 'org.openpsa.skype',
            'config_key'    => 'name',
        ),
        'jid' => array (
            'description' => 'jabber id',
            'datatype'    => 'text',
            'location'    => 'config',
            'config_domain' => 'org.openpsa.jabber',
            'config_key'    => 'jid',
        ),
        /*'address_home_street' => array (
            'description' => 'Street address',
            'datatype'    => 'text',
            'location'    => 'street',
            'start_fieldgroup' => array (
                'title' => 'Home address',
                'css_group' => 'area contact postal',
            ),
        ),
        'address_home_postcode' => array (
            'description' => 'Postcode',
            'datatype'    => 'text',
            'location'    => 'postcode',
        ),
        'address_home_city' => array (
            'description' => 'City',
            'datatype'    => 'text',
            'location'    => 'city',
        ),
        'address_home_country' => array (
            'description' => 'Country',
            'datatype'    => 'text',
            'location'    => 'country',
            'end_fieldgroup' => 2,
        ),*/
        'notes' => array (
            'description' => 'notes',
            'datatype'    => 'markdown',
            'widget_text_inputstyle' => 'longtext',
            'location'    => 'extra',
            'end_fieldgroup' => '',
        ),
        'weekly_workhours' => array (
            'description' => 'weekly workhours',
            'datatype'    => 'number',
            'location'    => 'config',
            'config_domain' => 'org.openpsa.reports.projects',
            'config_key'    => 'weekly_workhours',
            'start_fieldgroup' => array (
                'title' => 'project management',
                'css_group' => 'area',
            ),
        ),
        'invoiceable_target' => array (
            'description' => 'invoicing target percentage',
            'datatype'    => 'number',
            'location'    => 'config',
            'config_domain' => 'org.openpsa.reports.projects',
            'config_key'    => 'invoiceable_target',
            'end_fieldgroup' => '',
        ),
    )
),

'newperson' => array (

    'name'        => 'newperson',
    'description' => 'new person',
    'fields'      => array (
        // Metadata
        //Hidden field for orgOpenpsaObtype
        'object_type'  => array(
            'default'  => ORG_OPENPSA_OBTYPE_PERSON,
            'hidden'   => true,
            'location' => 'orgOpenpsaObtype',
            'datatype' => 'integer',
        ),
        'firstname' => array (
            'description' => 'firstname',
            'datatype'    => 'text',
            'location'    => 'firstname',
            'start_fieldgroup' => array (
                'title' => 'new person',
                'css_group' => 'area meta',
            ),
            'required'    => true,
        ),
        'lastname' => array (
            'description' => 'lastname',
            'datatype'    => 'text',
            'location'    => 'lastname',
            'required'    => true,
        ),
        'email' => array (
            'description' => 'email',
            'datatype'    => 'text',
            'location'    => 'email',
        ),
        'handphone' => array (
            'description' => 'mobile phone',
            'datatype'    => 'text',
            'location'    => 'handphone',
        ),
        'workphone' => array (
            'description' => 'work phone',
            'datatype'    => 'text',
            'location'    => 'workphone',
        ),
        'photo' => array (
            'description' => 'photo',
            'datatype'    => 'image',
            'widget_image_simple' => true,
            'end_fieldgroup' => '',
        ),

    )
)