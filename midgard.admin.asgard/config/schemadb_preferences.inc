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
            'start_fieldset' => array
            (
                'title' => 'editor options',
            ),
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
            'end_fieldset' => '',
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
            'start_fieldset' => array
            (
                'title' => 'localisation settings',
            ),
            'end_fieldset' => '',
        ),
        'navigation_type' => array
        (
            'title' => 'navigation type',
            'storage' => array
            (
                'location' => 'parameter',
                'domain' => 'midgard.admin.asgard:preferences',
                'name' => 'navigation_type',
            ),
            'type' => 'select',
            'type_config' => array
            (
                'options' => array
                (
                    '' => 'default setting',
                    'dropdown' => 'dropdown list',
                    'expanded' => 'expanded list',
                ),
            ),
            'widget' => 'select',
            'start_fieldset' => array
            (
                'title' => 'navigation options',
            ),
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
        'enable_quicklinks' => array
        (
            'title' => 'enable quicklinks',
            'storage' => array
            (
                'location' => 'parameter',
                'domain' => 'midgard.admin.asgard:preferences',
                'name' => 'enable_quicklinks',
            ),
            'type' => 'select',
            'type_config' => array
            (
                'options' => array
                (
                    '' => 'default setting',
                    'dropdown' => 'yes',
                    'expanded' => 'no',
                ),
            ),
            'widget' => 'select',
        ),
        'escape_frameset' => array
        (
            'title' => 'always display in top frame',
            'storage' => array
            (
                'location' => 'parameter',
                'domain' => 'midgard.admin.asgard:preferences',
                'name' => 'escape_frameset',
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
            'end_fieldset' => '',
        ),
    ),
),
