'hour_report' => array
(
    'description' => 'hour report',
    'l10n_db' => 'org.openpsa.projects',
    'fields'      => array
    (
        'date' => array
        (
            'title' => 'estimated delivery',
            'storage' => 'date',
            'type' => 'date',
            'type_config' => Array
            (
                'storage_type' => 'UNIXTIME'
            ),
            'widget' => 'jsdate',
            'widget_config' => Array
            (
                'show_time' => false,
            ),
        ),
        'hours' => array
        (
            'title' => 'hours',
            'storage' => 'hours',
            'type' => 'number',
            'widget'  => 'text',
            'required' => true,
        ),
        'invoiceable' => array
        (
            'title'   => 'invoiceable',
            'storage' => 'invoiceable',
            'type'    => 'boolean',
            'widget'  => 'checkbox',
        ),
        'approved' => array
        (
            'title'   => 'approved',
            'storage' => 'is_approved',
            'type'    => 'boolean',
            'widget'  => 'checkbox',
            'readonly' => true,
        ),
        'invoiced' => array
        (
            'title'   => 'invoiced',
            'storage' => 'is_invoiced',
            'type'    => 'boolean',
            'widget'  => 'checkbox',
            'readonly' => true,
        ),
        'person' => array
        (
            'title'   => 'reporter',
            'storage' => 'person',
            'type' => 'select',
            'type_config' => array
            (
                 'options' => org_openpsa_projects_task::get_task_resources(),
            ),
            'widget' => 'select',
        ),
        'task' => array
        (
            'title'   => 'task',
            'storage' => 'task',
            'type' => 'select',
            'type_config' => array
            (
                 'options' => org_openpsa_projects_task_resource::get_resource_tasks('id', true),
            ),
            'widget' => 'select',
        ),
        'description' => Array
        (
            'title' => 'description',
            'storage' => 'description',
            'type' => 'text',
            'type_config' => array
            (
                'output_mode' => 'markdown'
            ),
            'widget' => 'textarea',
        ),
    )
),