'default' => array (

    'name'        => 'default',
    'description' => 'event',
    'fields'      => array (
        //Hidden field for orgOpenpsaObtype
        'object_type'  => array(
            'default'  => ORG_OPENPSA_OBTYPE_EVENT,
            'hidden'   => true,
            'storage' => 'orgOpenpsaObtype',
            'type' => 'number',
			'widget' => 'text',
        ),
        'title' => array (
            'title' => 'title',
            'description' => 'title',
            'type'    => 'text',
			'widget'	=> 'text',
            'storage'    => 'title',
            'required'    => true,
        ),
        'location' => array (
            'title' => 'location',
            'description' => 'location',
            'type'    => 'text',
			'widget'	=> 'text',
            'storage'    => 'location',
        ),
        'start' => array (
            'title' => 'start time',
            'description' => 'start time',
            'type'    => 'date',
            'storage'    => 'start',
            'type_config' => array
            ( 
                'storage_type' => 'UNIXTIME'
            ),
            'widget' => 'jsdate',
			'required' => true,
        ),
        'end' => array (
            'title' => 'end time',
            'description' => 'end time',
            'type'    => 'date',
            'storage'    => 'end',
            'type_config' => array
            ( 
                'storage_type' => 'UNIXTIME'
            ),
            'widget' => 'jsdate',
			'required' => true,
        ),
        'description' => array (
	        'title' => 'description',
	        'storage' => 'description',
	        'type' => 'text',
	        'type_config' => array 
	        ( 
	            'output_mode' => 'markdown' 
	        ),
	        'widget' => 'textarea',
        ),
        'tags' => Array
        (
            'title' => 'tags',
            'storage' => 'parameter',
            'storage' => null,
            'type' => 'tags',
            'widget' => 'text',
        ),
        'participants' => array (
            'title' => 'participants',
            'description' => 'participants',
            'storage'    => 'participants',
            'type' => 'select',
            'type_config' => array
            (
                 'require_corresponding_option' => false,
                 'options' => array(),
            ),
            'widget' => 'universalchooser',
            'widget_config' => array
            (
                'class'       => 'org_openpsa_contacts_person',
                'component'   => 'org.openpsa.contacts',
                'titlefield'  => 'name',
                'idfield'     => 'id',
                'constraints' => array
                (
                    array
                    (
                        'field' => 'username',
                        'op'    => '<>',
                        'value' => '',
                    ),
                ),
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
        'orgOpenpsaAccesstype' => array (
            'title' => 'access type',
            'description' => 'access type',
            'location'    => 'orgOpenpsaAccesstype',
            'type' => 'select',
            'type_config' => array
            (
                'options' => array
                (
	                ORG_OPENPSA_ACCESSTYPE_PUBLIC => 'public',
	                ORG_OPENPSA_ACCESSTYPE_PRIVATE => 'private',
                ),
            ),
            'widget' => 'select',
			'required' => true,
        ),
        'busy' => array (
            'title' => 'dont allow overlapping',
            'description' => 'dont allow overlapping',
            'type'    => 'boolean',
            'widget'      => 'checkbox',
            'storage'    => 'busy',
            'default'     => 0,
        ),
    ),
),

'ajax' => array (
    'name'        => 'ajax',
    'description' => 'event',
    'fields'      => array (
        //Hidden field for orgOpenpsaObtype
        'object_type'  => array(
            'default'  => ORG_OPENPSA_OBTYPE_EVENT,
            'hidden'   => true,
            'storage' => 'orgOpenpsaObtype',
            'type' => 'number',
			'widget' => 'text',
        ),
        'title' => array (
            'title' => 'title',
            'description' => 'title',
            'type'    => 'text',
			'widget'	=> 'text',
            'storage'    => 'title',
            'required'    => true,
        ),
        'location' => array (
            'title' => 'location',
            'description' => 'location',
            'type'    => 'text',
			'widget'	=> 'text',
            'storage'    => 'location',
            'hidden' => true,
        ),
        'start' => array (
            'title' => 'start time',
            'description' => 'start time',
            'type'    => 'date',
            'storage'    => 'start',
            'type_config' => array
            ( 
                'storage_type' => 'UNIXTIME'
            ),
            'widget' => 'jsdate',
			'required' => true,
        ),
        'end' => array (
            'title' => 'end time',
            'description' => 'end time',
            'type'    => 'date',
            'storage'    => 'end',
            'type_config' => array
            ( 
                'storage_type' => 'UNIXTIME'
            ),
            'widget' => 'jsdate',
			'required' => true,
        ),
        'description' => array (
	        'title' => 'description',
	        'storage' => 'description',
	        'type' => 'text',
	        'type_config' => array 
	        ( 
	            'output_mode' => 'markdown' 
	        ),
	        'widget' => 'textarea',
            'hidden' => true,
        ),
        'tags' => Array
        (
            'title' => 'tags',
            'storage' => 'parameter',
            'storage' => null,
            'type' => 'tags',
            'widget' => 'text',
            'hidden' => true,
        ),
        'participants' => array (
            'title' => 'participants',
            'description' => 'participants',
            'storage'    => 'participants',
            'type' => 'select',
            'type_config' => array
            (
                 'require_corresponding_option' => false,
                 'options' => array(),
            ),
            'widget' => 'universalchooser',
            'widget_config' => array
            (
                'class'       => 'org_openpsa_contacts_person',
                'component'   => 'org.openpsa.contacts',
                'titlefield'  => 'name',
                'idfield'     => 'id',
                'constraints' => array
                (
                    array
                    (
                        'field' => 'username',
                        'op'    => '<>',
                        'value' => '',
                    ),
                ),
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
            'hidden' => true,
        ),
        'orgOpenpsaAccesstype' => array (
            'title' => 'access type',
            'description' => 'access type',
            'location'    => 'orgOpenpsaAccesstype',
            'type' => 'select',
            'type_config' => array
            (
                'options' => array
                (
	                ORG_OPENPSA_ACCESSTYPE_PUBLIC => 'public',
	                ORG_OPENPSA_ACCESSTYPE_PRIVATE => 'private',
                ),
            ),
            'widget' => 'select',
			'required' => true,
            'hidden' => true,
        ),
        'busy' => array (
            'title' => 'dont allow overlapping',
            'description' => 'dont allow overlapping',
            'type'    => 'boolean',
            'widget'      => 'checkbox',
            'storage'    => 'busy',
            'default'     => 0,
            'hidden' => true,
        ),
    ),
),