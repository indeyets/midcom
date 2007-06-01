'default' => array
(
    'description' => 'tell-a-friend from',
    'operations' => Array('save' => 'Send'),

    'fields'      => array
    (
        'recipient' => Array
        (
            // COMPONENT-REQUIRED
            'title' => $_MIDCOM->i18n->get_string('recipient', 'pl.olga.tellafriend'),
            'storage' => null,
            'type' => 'text',
            'widget' => 'text',
            'required' => true,
        ),
        'sender' => Array
        (
            // COMPONENT-REQUIRED
            'title' => $_MIDCOM->i18n->get_string('sender email', 'pl.olga.tellafriend'),
            'storage' => null,
            'required' => true,
            'type' => 'text',
            'widget' => 'text',
        ),

        'sender_name' => Array
        (
            // COMPONENT-REQUIRED
            'title' => $_MIDCOM->i18n->get_string('sender name', 'pl.olga.tellafriend'),
            'storage' => null,
            'required' => true,
            'type' => 'text',
            'widget' => 'text',
        ),

        'comment' => Array
        (
            // COMPONENT-REQUIRED
            'title' => $_MIDCOM->i18n->get_string('comment', 'pl.olga.tellafriend'),
            'storage' => null,
            'type' => 'text',
            'widget' => 'textarea',
        ),
    )
), // default

