'mailbox' => Array
(
    'description' => 'Mailbox',
    'fields' => Array
    (
        'owner' => Array
        (
            'title' => 'owner',
            'description' => 'Mailbox Owner',
            'helptext' => '',

            'storage' => 'owner',
            'required' => true,
            'type' => 'select',
            'type_config' => Array
            (
                'option_callback' => 'net_nehmer_mail_callbacks_mailboxowners',
            ),
            'widget' => 'select',
        ),
        'name' => Array
        (
            'title' => 'name',
            'description' => 'This is the name of the Mailbox',
            'helptext' => '',

            'storage' => 'name',
            'required' => true,
            'type' => 'text',
            'type_config' => Array
            (
                'maxlength' => 150,
            ),
            'widget' => 'text',
        ),
        'quota' => Array
        (
            'title' => 'quota',
            'description' => 'This is the maximum mailbox count, set to 0 for no limit.',
            'helptext' => '',

            'storage' => 'quota',
            'type' => 'number',
            'type_config' => Array
            (
                'precision' => 0,
                'minimum' => 0,
            ),
            'widget' => 'text',
        ),
    ),
),

'new_mail' => Array
(
    'description' => 'New Mail',
    'operations' => Array('save' => 'send mail'),
    'fields' => Array
    (
        'receivers' => array
        (
            'title'   => 'receivers',
            'storage' => null,
            'type' => 'select',
            'type_config' => array
            (
                 'require_corresponding_option' => false,
                 'allow_multiple' => true,
                 'multiple_storagemode' => 'array',
                 'options' => array(),
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
            'required' => true,
        ),
        'subject' => Array
        (
            'title' => 'subject',
            'description' => 'This is the subject of the mail.',
            'helptext' => '',

            'storage' => 'subject',
            'required' => true,
            'type' => 'text',
            'type_config' => Array
            (
                'maxlength' => 250,
            ),
            'widget' => 'text',
            'widget_config' => Array
            (
                'size' => 30,
            ),
        ),
        'body' => Array
        (
            'title' => 'body',
            'description' => 'This is the body of the mail.',
            'helptext' => '',

            'storage' => 'body',
            'required' => true,
            'type' => 'text',
            'type_config' => Array 
            ( 
                'output_mode' => 'markdown' 
            ),
            'widget' => 'textarea',
            'widget_config' => Array
            (
                'width' => 64,
                'height' => 15,
                'wrap' => 'physical',
            ),
        ),
    ),
),
