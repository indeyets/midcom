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
                'domain' => 'midgard.admin.asgard.preferences',
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
                'domain' => 'midgard.admin.asgard.preferences',
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
                'domain' => 'midgard.admin.asgard.preferences',
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
                'domain' => 'midgard.admin.asgard.preferences',
                'name' => 'offset',
            ),
            'type' => 'text',
            'widget' => 'text',
        ),
    ),
),
