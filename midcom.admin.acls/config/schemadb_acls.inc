
'acls' => Array
(
    'description' => 'ACLs',
    'fields' => Array
    (
        'acls' => Array
        (
            'title' => 'Acls',
            'description' => '',
            'helptext' => '',
            'storage' => Array
             (
                    'location' => null,
             ),
            'type' => 'table',
            'widget' => 'table',
            'type_config' => array (
                'callback' => 'midcom_admin_acls_callback',
                'headers'  => array('','read', 'write','delete', 'create')
            ) ,
            'widget_config' => array (
                'plusrow' => false,
                'widths' => array( '15em', '3em', '3em' , '3em', '3em', '5em'),
                'types'  => array ('text','checkbox','checkbox','checkbox','checkbox')
            ) ,
        ),
     
    ), // fields
),