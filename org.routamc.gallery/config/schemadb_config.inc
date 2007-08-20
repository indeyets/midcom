/**
 * This schema is used to show the settings form on
 * for the folder of your component
 */
'config' => array
(
    'description' => 'configuration',
    'fields'      => array
    (
        'type' => array
        (
            'title'   => 'photo selection method',
            'storage' => array
            (
                'location' => 'configuration',
                'domain'   => 'org.routamc.gallery',
                'name'     => 'gallery_type',
            ),
            'type' => 'select',
            'type_config' => array
            (
                'options' => array
                (
                    ORG_ROUTAMC_GALLERY_TYPE_HANDPICKED => 'hand picked photos',
                    ORG_ROUTAMC_GALLERY_TYPE_TAGS       => 'photos with tags',
                    //ORG_ROUTAMC_GALLERY_TYPE_TIME       => 'photos in time window',
                ),
            ),
            'widget' => 'select',
        ),
        'handpicked_photos' => array
        (
            'title' => 'hand picked photos',
            'type' => 'mnrelation',
            'type_config' => array
            (
                 'require_corresponding_option' => false,
                 'options' => array(),
                 'mapping_class_name' => 'org_routamc_gallery_photolink_dba',
                 'master_fieldname' => 'node',
                 'master_is_id' => true,
                 'member_fieldname' => 'photo',
            ),
            'widget' => 'universalchooser',
            'widget_config' => array
            (
                'class'       => 'org_routamc_photostream_photo_dba',
                'component'   => 'org.routamc.photostream',
                'titlefield'  => 'thumbnail_html',
                //'titlefield'  => 'title',
                'idfield'     => 'id',
                'constraints' => array
                (
                ),
                'searchfields'  => array
                (
                    'title',
                    'description',
                ),
                'orders'        => array
                (
                    array('taken' => 'DESC'),
                ),
            ),
        ),
        'photos_per_page' => array
        (
            'title' => 'photos per page',
            'storage' => array
            (
                'location' => 'configuration',
                'domain'   => 'org.routamc.gallery',
                'name'     => 'photos_per_page',
            ),
            'type' => 'text',
            'widget' => 'text',
        ),
        'tags' => Array
        (
            'title' => 'tags',
            'type' => 'tags',
            'widget' => 'text',
        ),
        'sort_order' => array
        (
            'title' => 'sort order',
            'storage' => array
            (
                'location' => 'configuration',
                'domain'   => 'org.routamc.gallery',
                'name'     => 'sort_order',
            ),
            'type' => 'select',
            'type_config' => array
            (
                'options' => array
                (
                    '' => 'default setting',
                    'hidden' => 'hide items from navigation',
                    'reverse score' => 'score',
                    'taken' => 'taken',
                    'reverse taken' => 'reverse taken',
                    'edited' => 'edited',
                    'reverse created' => 'reverse edited',
                ),
            ),
            'widget' => 'select',
        ),
        'navigation_items' => array
        (
            'title' => 'number of navigation items',
            'storage' => array
            (
                'location' => 'configuration',
                'domain'   => 'org.routamc.gallery',
                'name'     => 'navigation_items',
            ),
            'type' => 'text',
            'widget' => 'text',
            'default' => 0,
        ),
        'enable_ajax_editing' => array
        (
            'title' => 'enable ajax editing',
            'storage' => array
            (
                'location' => 'configuration',
                'domain'   => 'org.routamc.gallery',
                'name'     => 'enable_ajax_editing',
            ),
            'type' => 'select',
            'type_config' => array
            (
                'options' => array
                (
                    '' => 'default setting',
                    '1' => 'yes',
                    '0' => 'no',
                ),
            ),
            'widget' => 'select',
        ),
    ),
),
