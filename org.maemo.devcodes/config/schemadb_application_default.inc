'application' => array (
    'name'        => 'application',
    'description' => 'application for developer code',
    'fields'      => array
    (
        'summary' => array (
            'title'       => 'summary',
            'type'        => 'text',
            'widget'      => 'text',
            'storage'     => 'summary', 
            'required'    => true,
        ),
        'content' => array (
            'title'       => 'longer explanation',
            'type'        => 'text',
            'widget'      => 'textarea',
            'storage'     => 'content',
        ),
        'applicant' => array
        (
            'title'       => 'applicant',
            'type'        => 'select',
            'type_config' => array
            (
                'options' => array
                (
                    0 => 'none',
                ),
                'require_corresponding_option' => false,
                'allow_other' => true,
            ),
            'storage'     => 'applicant', 
            'widget'      => 'universalchooser',
            'widget_config' => array
            (
                'class' => 'org_openpsa_contacts_person_dba',
                'component' => 'org.openpsa.contacts',
                'idfield' => 'id',
                'titlefield' => 'name',
                'searchfields' => array
                (
                    'email',
                    'username',
                    'firstname',
                    'lastname',
                ),
                'auto_wildcards' => 'both',
                'constraints' => array
                (
                    array
                    (
                        'field' => 'sitegroup',
                        'op'    => '=',
                        'value' => $_MIDGARD['sitegroup'],
                    ),
                ),
                'orders' => array
                (
                    array('lastname' => 'ASC'),
                    array('firstname' => 'ASC'),
                ),
                'static_options' => array
                (
                    0 => 'none',
                ),
            ),
        ),
        'device' => array
        (
            'title'       => 'device program',
            'type'        => 'select',
            'type_config' => array
            (
                //'options' => org_maemo_devcodes_device_dba::list_open('id'),
                'options' => org_maemo_devcodes_device_dba::list_all('id'),
            ),
            'widget'      => 'select',
        ),        
        'state' => array
        (
            'title'       => 'state',
            'type'        => 'select',
            'type_config' => array
            (
                'options' => array
                (
                    ORG_MAEMO_DEVCODES_APPLICATION_PENDING => 'pending',
                    ORG_MAEMO_DEVCODES_APPLICATION_ACCEPTED => 'accepted',
                    ORG_MAEMO_DEVCODES_APPLICATION_REJECTED => 'rejected',
                ),
                'allow_other' => false,
            ),
            'storage'     => 'state', 
            'widget'      => 'select',
            'readonly'    => true,
        ),
    ),
), // application
'application-user' => array (
    'name'        => 'application',
    'description' => 'application for developer code',
    'fields'      => array
    (
        'summary' => array (
            'title'       => 'summary',
            'type'        => 'text',
            'widget'      => 'text',
            'storage'     => 'summary', 
            'required'    => true,
        ),
        'content' => array (
            'title'       => 'longer explanation',
            'type'        => 'text',
            'widget'      => 'textarea',
            'storage'     => 'content',
        ),
    ),
), // application-user
