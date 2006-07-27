'pwchange' => Array
(
    'description' => 'change password',
    'fields' => Array
    (
        'oldpassword' => Array
        (
            'title' => 'old password',
            'description' => '',
            'helptext' => '',

            'storage' => 'password',
            'required' => true,
            'type' => 'text',
            'widget' => 'text',
            'widget_config' => Array
            (
                'hideinput' => true,
            ),
        ),
        'newpassword' => Array
        (
            'title' => 'new password',
            'description' => '',
            'helptext' => '',

            'storage' => 'password',
            'type' => 'password',
            'widget' => 'password',
        ),
    ),
),

'usernamechange' => Array
(
    'description' => 'change username',
    'fields' => Array
    (
        'username' => Array
        (
            'title' => 'new username',
            'description' => '',
            'helptext' => '',

            'storage' => 'username',
            'required' => true,
            'type' => 'text',
            'widget' => 'text',
        ),
    ),
),

'emailusernamechange' => Array
(
    'description' => 'change username (E-Mail mode)',
    'fields' => Array
    (
        'username' => Array
        (
            'title' => 'new email',
            'description' => '',
            'helptext' => '',

            'storage' => 'username',
            'required' => true,
            'type' => 'text',
            'widget' => 'text',
        ),
    ),
),

'lostpassword' => Array
(
    'description' => 'lostpassword',
    'fields' => Array
    (
        'username' => Array
        (
            'title' => 'username',
            'description' => '',
            'helptext' => '',

            'storage' => 'username',
            'required' => true,
            'type' => 'text',
            'widget' => 'text',
        ),
    ),
),