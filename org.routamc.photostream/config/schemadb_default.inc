'photo' => array
(
    'description' => 'photo',
    'fields'      => array
    (
        'title' => Array
        (
            'title' => 'title',
            'storage' => 'title',
            'type' => 'text',
            'widget' => 'text',
            'index_method' => 'title',
        ),
        'description' => Array
        (
            'title' => 'description',
            'storage' => 'description',
            'type' => 'text',
            'widget' => 'textarea',
        ),
        'photographer' => array
        (
            'title' => 'photographer',
            'storage' => 'photographer',
            'type' => 'select',
            'type_config' => array
            (
                 'require_corresponding_option' => false,
                 'options' => array(),
            ),
            'widget' => 'chooser',
            'widget_config' => array
            (
                'clever_class' => 'person',
                'id_field' => 'id',
                'creation_mode_enabled' => true,
                'creation_handler' => "{$_MIDGARD['self']}__mfa/asgard/object/create/chooser/midgard_person/",
                'creation_default_key' => 'lastname',
            ),
        ),
        'taken' => Array
        (
            'title' => 'taken',
            'storage' => 'taken',
            'type' => 'date',
            'type_config' => Array
            (
                'storage_type' => 'UNIXTIME'
            ),
            'widget' => 'jsdate',
        ),
        'tags' => Array
        (
            'title' => 'tags',
            'storage' => null,
            'type' => 'tags',
            'widget' => 'text',
        ),
        'rating' => Array
        (
            'title' => 'rating',
            'storage' => 'rating',
            'type' => 'select',
            'type_config' => Array
            (
                'options' => Array
                (
                    0 => '',
                    1 => '*',
                    2 => '**',
                    3 => '***',
                    4 => '****',
                    5 => '*****',
                ),
            ),
            'widget' => 'select',
        ),
        /* NOTE: You *will* want to migrate all changes made here to the same
          field in the upload schema as well */
        'photo' => Array
        (
            'title' => 'photo',
            'type' => 'photo',
            'type_config' => Array
            (
                'filter_chain'   => 'exifrotate()',
                'derived_images' => array
                (
                    // Intentionally this way, so that portraits can be taller
                    'view' => 'exifrotate();resize(500,600)',
                    // Use specific thumbnail rule to allow for exifrotate
                    'thumbnail' => 'exifrotate();resize(200,200)',
                ),
            ),
            'widget' => 'photo',
            'required' => true,
        ),
        'private' => Array
        (
            'title' => 'private',
            'description' => 'if checked only you can see this photo',
            'storage' => null,
            'type' => 'privilegeset',
            'type_config' => Array
            (
                'privileges' => Array
                (
                    Array('midgard:read', 'EVERYONE', MIDCOM_PRIVILEGE_DENY),
                ),
            ),
            'widget' => 'privilegecheckbox',
        ),
        'not_public' => Array
        (
            'title' => 'not public',
            'description' => 'if checked only logged in users can see this photo',
            'storage' => null,
            'type' => 'privilegeset',
            'type_config' => Array
            (
                'privileges' => Array
                (
                    Array('midgard:read', 'ANONYMOUS', MIDCOM_PRIVILEGE_DENY),
                ),
            ),
            'widget' => 'privilegecheckbox',
        ),
    )
),
'upload' => array
(
    'description' => 'photo upload',
    'fields'      => array
    (
        'title' => Array
        (
            'title' => 'title',
            'storage' => 'title',
            'type' => 'text',
            'widget' => 'text',
            'index_method' => 'title',
        ),
        /* NOTE: You *will* want to migrate all changes made here to the same
          field in the photo schema as well */
        'photo' => Array
        (
            'title' => 'photo',
            'type' => 'photo',
            'type_config' => Array
            (
                'filter_chain'   => 'exifrotate()',
                'derived_images' => array
                (
                    // Intentionally this way, so that portraits can be taller
                    'view' => 'exifrotate();resize(500,600)',
                    // Use specific thumbnail rule to allow for exifrotate
                    'thumbnail' => 'exifrotate();resize(200,200)',
                ),
            ),
            'widget' => 'photo',
            'required' => true,
        ),
        'description' => Array
        (
            'title' => 'description',
            'storage' => 'description',
            'type' => 'text',
            'widget' => 'textarea',
        ),
        'tags' => Array
        (
            'title' => 'tags',
            'type' => 'tags',
            'widget' => 'text',
        ),  
        'photographer' => array
        (
            'title' => 'photographer',
            'storage' => 'photographer',
            'type' => 'select',
            'type_config' => array
            (
                 'require_corresponding_option' => false,
                 'options' => array(),
            ),
            'widget' => 'chooser',
            'widget_config' => array
            (
                'clever_class' => 'person',
                'id_field' => 'id',
                'creation_mode_enabled' => true,
                'creation_handler' => "{$_MIDGARD['self']}__mfa/asgard/object/create/chooser/midgard_person/",
                'creation_default_key' => 'lastname',
            ),
            'required' => true,
        ),
        'to_gallery' => array
        (
            'title' => 'upload to gallery',
            'storage' => 'parameter',
            'type' => 'select',
            'type_config' => array
            (
                 'require_corresponding_option' => false,
                 'options' => array(),
            ),
            'widget' => 'chooser',
            'widget_config' => array
            (
                'clever_class' => 'topic',
                'id_field' => 'id',
                'constraints' => array
                (
                    array
                    (
                        'field' => 'component',
                        'op' => '=',
                        'value' => 'org.routamc.gallery',
                    ),
                    array
                    (
                        'field' => 'parameter.name',
                        'op' => '=',
                        'value' => 'gallery_type',
                    ),
                    array
                    (
                        'field' => 'parameter.value',
                        'op' => '=',
                        'value' => 10, //ORG_ROUTAMC_GALLERY_TYPE_HANDPICKED
                    ),
                ),
            ),
        ),
        'rating' => Array
        (
            'title' => 'rating',
            'storage' => 'rating',
            'type' => 'select',
            'type_config' => Array
            (
                'options' => Array
                (
                    0 => '',
                    1 => '*',
                    2 => '**',
                    3 => '***',
                    4 => '****',
                    5 => '*****',
                ),
            ),
            'widget' => 'select',
            'hidden' => true,
        ),
        'private' => Array
        (
            'title' => 'private',
            'description' => 'if checked only you can see this photo',
            'storage' => null,
            'type' => 'privilegeset',
            'type_config' => Array
            (
                'privileges' => Array
                (
                    Array('midgard:read', 'EVERYONE', MIDCOM_PRIVILEGE_DENY),
                ),
            ),
            'widget' => 'privilegecheckbox',
        ),
        'not_public' => Array
        (
            'title' => 'not public',
            'description' => 'if checked only logged in users can see this photo',
            'storage' => null,
            'type' => 'privilegeset',
            'type_config' => Array
            (
                'privileges' => Array
                (
                    Array('midgard:read', 'ANONYMOUS', MIDCOM_PRIVILEGE_DENY),
                ),
            ),
            'widget' => 'privilegecheckbox',
        ),
    )
),