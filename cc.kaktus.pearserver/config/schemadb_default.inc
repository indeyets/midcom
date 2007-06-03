'channel' => array
(
    'description' => 'PEAR channel',
    'fields' => array
    (
        'name' => array
        (
            'title' => 'name',
            'storage' => 'code',
            'type' => 'text',
            'widget' => 'text',
            'hidden' => true,
        ),
        'title' => array
        (
            'title' => 'title',
            'storage' => 'title',
            'type' => 'text',
            'widget' => 'text',
        ),
        'description' => array
        (
            'title' => 'description',
            'storage' => 'description',
            'type' => 'text',
            'type_config' => array
            (
                'output_mode' => 'markdown',
            ),
            'widget' => 'textarea',
        ),
    ),
),

'package' => array
(
    'description' => 'PEAR package',
    'fields' => array
    (
        'name' => array
        (
            'title' => 'name',
            'storage' => 'code',
            'type' => 'text',
            'widget' => 'text',
            'hidden' => true,
        ),
        'title' => array
        (
            'title' => 'title',
            'storage' => 'title',
            'type' => 'text',
            'widget' => 'text',
        ),
        'description' => array
        (
            'title' => 'description',
            'storage' => 'description',
            'type' => 'text',
            'type_config' => array
            (
                'output_mode' => 'markdown',
            ),
            'widget' => 'textarea',
        ),
        'maintainers' => array
        (
            'title' => 'maintainers',
            'storage' => 'parameter',
            'type' => 'select',
            'type_config' => array
            (
                'require_corresponding_option' => false,
                'options' => array (),
                'allow_other' => true,
            ),
            'widget' => 'select',
            'widget_config' => array
            (
                'class' => 'midcom_db_person',
                'component' => 'net.nemein.personnel',
                'titlefield' => 'name',
                'idfield' => 'username',
                'searchfields' => array
                (
                    'firstname',
                    'lastname',
                ),
                'orders' => array
                (
                    array ('lastname', 'ASC')
                ),
            ),
        ),
        'versions' => array
        (
            'title' => 'versions',
            'storage' => null,
            'type' => 'blobs',
            'widget' => 'downloads',
        ),
    ),
),

'upload' => array
(
    'description' => 'upload a release',
    'fields' => array
    (
        'file' => array
        (
            'title' => 'file',
            'storage' => null,
            'type' => 'blobs',
            'widget' => 'downloads',
            'required' => true,
        ),
        'notes' => array
        (
            'title' => 'release notes',
            'storage' => 'description',
            'type' => 'text',
            'type_config' => array
            (
                'output_mode' => 'html',
            ),
            'widget' => 'textarea',
        ),
    ),
)