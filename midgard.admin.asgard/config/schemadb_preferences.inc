'default' => array
(
    'description' => 'user preferences',
    'l10n_db' => 'midgard.admin.asgard',
    'fields' => array
    (
        'tinymce_enabled' => array
        (
            'title' => 'use tinymce editor for editing content',
            'storage' => array
            (
                'location' => 'parameter',
                'domain' => 'midgard.admin.asgard:preferences',
                'name' => 'tinymce_enabled',
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
        'codepress_enabled' => array
        (
            'title' => 'use codepress editor for editing code snippets',
            'storage' => array
            (
                'location' => 'parameter',
                'domain' => 'midgard.admin.asgard:preferences',
                'name' => 'codepress_enabled',
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
        'interface_language' => array
        (
            'title' => 'interface language',
            'storage' => array
            (
                'location' => 'parameter',
                'domain' => 'midgard.admin.asgard:preferences',
                'name' => 'interface_language',
            ),
            'type' => 'select',
            'type_config' => array
            (
                'options' => midgard_admin_asgard_handler_preferences::get_languages(),
            ),
            'widget' => 'select',
        ),
        'offset' => array
        (
            'title' => 'navigation width (in pixels)',
            'storage' => array
            (
                'location' => 'parameter',
                'domain' => 'midgard.admin.asgard:preferences',
                'name' => 'offset',
            ),
            'type' => 'text',
            'widget' => 'text',
        ),
        'toolbar_mode' => array
        (
            'title' => 'toolbar mode',
            'storage' => array
            (
                'location' => 'parameter',
                'domain' => 'midgard.admin.asgard:preferences',
                'name' => 'toolbar_mode',
            ),
            'type' => 'select',
            'type_config' => array
            (
                'options' => array
                (
                    '' => 'default setting',
                    'static' => 'on top of the content area',
                    'absolute' => 'floating freely',
                ),
            ),
            'widget' => 'select',
        ),
        'toolbar_x' => array
        (
            'title' => 'toolbar position x',
            'storage' => array
            (
                'location' => 'parameter',
                'domain' => 'midgard.admin.asgard:preferences',
                'name' => 'toolbar_x',
            ),
            'type' => 'text',
            'widget' => 'text',
            'hidden' => (midgard_admin_asgard_plugin::get_preference('toolbar_mode') === 'absolute') ? false : true,
        ),
        'toolbar_y' => array
        (
            'title' => 'toolbar position y',
            'storage' => array
            (
                'location' => 'parameter',
                'domain' => 'midgard.admin.asgard:preferences',
                'name' => 'toolbar_y',
            ),
            'type' => 'text',
            'widget' => 'text',
            'hidden' => (midgard_admin_asgard_plugin::get_preference('toolbar_mode') === 'absolute') ? false : true,
        ),
    ),
),
