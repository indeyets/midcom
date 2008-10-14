'meetingroom' => array 
(
    'description' => 'meeting room',
    'fields'      => array 
    (
        'name' => Array
        (
            // COMPONENT-REQUIRED
            'title' => 'url name',
            'storage' => 'name',
            'type' => 'text',
            'widget' => 'text',
        ),
        'title' => array 
        (
            // COMPONENT-REQUIRED
            'title'    => 'title',
            'type'     => 'text',
            'widget'   => 'text',
            'storage'  => 'title',
            'required' => true,
        ),
        'location' => array 
        (
            'title'    => 'location',
            'type'     => 'text',
            'widget'   => 'text',
            'storage'  => 'location',
        ),
        'capacity' => array 
        (
            'title'    => 'capacity',
            'type'     => 'number',
            'widget'   => 'text',
            'storage'  => 'capacity',
        ),
        'period' => array
        (
            // COMPONENT-REQUIRED
            'title' => 'reservation period',
            'storage' => 'period',
            'type' => 'select',
            'type_config' => array
            (
                'options' => array
                (
                    'h'  => 'by hour',
                    /*'d'  => 'by day',*/
                ),
            ),
            'widget' => 'select',
        ),
        'description' => array
        (
            'title' => 'description',
            'storage' => 'description',
            'type' => 'text',
            'type_config' => array 
            ( 
                'output_mode' => 'html' 
            ),
            'widget' => 'tinymce',
        ),
        'owner' => array 
        (
            'title'   => 'person responsible',
            'storage' => 'owner',
            'required' => true,
            'type' => 'select',
            'type_config' => array
            (
                 'require_corresponding_option' => false,
                 'options' => array(),
            ),
            'widget' => 'universalchooser',
            'widget_config' => array
            (
                'class'       => 'org_openpsa_contacts_person_dba',
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
    )
),