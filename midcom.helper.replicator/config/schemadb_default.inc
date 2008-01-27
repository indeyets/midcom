'mirror' => array
(
    'description' => 'mirroring subscription',
    'l10n_db'     => 'midcom.helper.replicator',
    'fields'      => array
    (
        'title' => Array
        (
            // COMPONENT-REQUIRED
            'title' => 'title',
            'storage' => 'title',
            'required' => true,
            'type' => 'text',
            'widget' => 'text',
        ),
        'description' => Array
        (
            'title' => 'description',
            'storage' => 'description',
            'type' => 'text',
            'widget' => 'textarea',
        ),
        'status' => Array
        (
            // COMPONENT-REQUIRED
            'title' => 'status',
            'storage' => 'status',
            'type' => 'select',
            'type_config' => array
            (
                'options' => array
                (
                    MIDCOM_REPLICATOR_AUTOMATIC => 'automatic',
                    MIDCOM_REPLICATOR_MANUAL    => 'manual',
                    MIDCOM_REPLICATOR_DISABLED  => 'disabled',
                ),
            ),
            'widget' => 'select',
        ),
        'filters' => Array
        (
            'title' => 'xml filters',
            'storage' => 'filtersSerialized',
            'type' => 'text',
            'widget' => 'textarea',
            'hidden'  => (!$_MIDCOM->auth->admin),
        ),
        'transporter' => Array
        (
            // COMPONENT-REQUIRED
            'title' => 'transporter',
            'storage' => 'transporter',
            'type' => 'select',
            'type_config' => array
            (
                'options' => midcom_helper_replicator_transporter::list_transports(),
            ),
            'widget' => 'select',
        ),
    )
),

'staging2live' => array
(
    'description' => 'staging2live subscription',
    'l10n_db'     => 'midcom.helper.replicator',
    'fields'      => array
    (
        'title' => Array
        (
            // COMPONENT-REQUIRED
            'title' => 'title',
            'storage' => 'title',
            'required' => true,
            'type' => 'text',
            'widget' => 'text',
        ),
        'description' => Array
        (
            'title' => 'description',
            'storage' => 'description',
            'type' => 'text',
            'widget' => 'textarea',
        ),
        'status' => Array
        (
            // COMPONENT-REQUIRED
            'title' => 'status',
            'storage' => 'status',
            'type' => 'select',
            'type_config' => array
            (
                'options' => array
                (
                    MIDCOM_REPLICATOR_AUTOMATIC => 'automatic',
                    MIDCOM_REPLICATOR_MANUAL    => 'manual',
                    MIDCOM_REPLICATOR_DISABLED  => 'disabled',
                ),
            ),
            'widget' => 'select',
        ),
        'filters' => Array
        (
            'title' => 'xml filters',
            'storage' => 'filtersSerialized',
            'type' => 'text',
            'widget' => 'textarea',
            'hidden'  => (!$_MIDCOM->auth->admin),
        ),
        'transporter' => Array
        (
            // COMPONENT-REQUIRED
            'title' => 'transporter',
            'storage' => 'transporter',
            'type' => 'select',
            'type_config' => array
            (
                'options' => midcom_helper_replicator_transporter::list_transports(),
            ),
            'widget' => 'select',
        ),
    )
),

'staging2live_typefilter' => array
(
    'description' => 'staging2live subscription with type filter',
    'l10n_db'     => 'midcom.helper.replicator',
    'fields'      => array
    (
        'title' => Array
        (
            // COMPONENT-REQUIRED
            'title' => 'title',
            'storage' => 'title',
            'required' => true,
            'type' => 'text',
            'widget' => 'text',
        ),
        'description' => Array
        (
            'title' => 'description',
            'storage' => 'description',
            'type' => 'text',
            'widget' => 'textarea',
        ),
        'status' => Array
        (
            // COMPONENT-REQUIRED
            'title' => 'status',
            'storage' => 'status',
            'type' => 'select',
            'type_config' => array
            (
                'options' => array
                (
                    MIDCOM_REPLICATOR_AUTOMATIC => 'automatic',
                    MIDCOM_REPLICATOR_MANUAL    => 'manual',
                    MIDCOM_REPLICATOR_DISABLED  => 'disabled',
                ),
            ),
            'widget' => 'select',
        ),
        'filters' => Array
        (
            'title' => 'xml filters',
            'storage' => 'filtersSerialized',
            'type' => 'text',
            'widget' => 'textarea',
            'hidden'  => (!$_MIDCOM->auth->admin),
        ),
        'transporter' => Array
        (
            // COMPONENT-REQUIRED
            'title' => 'transporter',
            'storage' => 'transporter',
            'type' => 'select',
            'type_config' => array
            (
                'options' => midcom_helper_replicator_transporter::list_transports(),
            ),
            'widget' => 'select',
        ),
    )
),


