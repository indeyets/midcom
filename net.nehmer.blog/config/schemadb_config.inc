'config' => array
(
    'name'        => 'config',
    'description' => 'Default Configuration Schema',
    'fields'      => array
    (
        'index_entries' => array
        (
            'title' => 'index_entries',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'net.nehmer.blog',
                'name' => 'index_entries',
            ),
            'type' => 'text',
            'widget' => 'text',
            'start_fieldset' => array
            (
                'title' => 'blog settings'
            ),
        ),
        'categories' => array
        (
            'title' => 'categories',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'net.nehmer.blog',
                'name' => 'categories',
            ),
            'type' => 'text',
            'widget' => 'text',
        ),
        'enable_scheduled_publishing' => array
        (
            'title' => 'enable scheduled publishing',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'net.nehmer.blog',
                'name' => 'enable_scheduled_publishing',
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
        'language' => array
        (
            'title' => 'language',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'net.nehmer.blog',
                'name' => 'language',
            ),
            'type' => 'select',
            'type_config' => array
            (
                'options' => array_merge(array('' => 'default setting'), $_MIDCOM->i18n->list_languages()),
            ),
            'widget' => 'select',
            'readonly' => net_nehmer_blog_viewer::disable_language_select(),
            'end_fieldset' => '',
        ),
        'schemadb' => array
        (
            'title' => 'schemadb',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'net.nehmer.blog',
                'name' => 'schemadb',
            ),
            'type' => 'select',
            'type_config' => array
            (
                'options' => $this->_request_data['config']->get('schemadbs'),
            ),
            'widget' => 'select',
            'start_fieldgroup' => array
            (
                'title' => 'schema settings',
            ),
        ),
        'comments_enable' => array
        (
            'title' => 'comments_enable',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'net.nehmer.blog',
                'name' => 'comments_enable',
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
        'ajax_enable' => array
        (
            'title' => 'ajax_enable',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'net.nehmer.blog',
                'name' => 'ajax_enable',
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
        'archive_enable' => array
        (
            'title' => 'archive_enable',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'net.nehmer.blog',
                'name' => 'archive_enable',
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
        'archive_item_order' => array
        (
            'title' => 'archive item order',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'net.nehmer.blog',
                'name' => 'archive_item_order',
            ),
            'type' => 'select',
            'type_config' => array
            (
                'options' => array
                (
                    '' => 'default setting',
                    'ASC' => 'ascending',
                    'DESC' => 'descending',
                ),
            ),
            'widget' => 'select',
        ),
        'rss_subscription_enable' => array
        (
            'title' => 'archive_enable',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'net.nehmer.blog',
                'name' => 'rss_subscription_enable',
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
        'link_to_external_url' => array
        (
            'title' => 'archive_enable',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'net.nehmer.blog',
                'name' => 'link_to_external_url',
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
        'symlink_topic' => array
        (
            'title' => 'symlink_topic',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'net.nehmer.blog',
                'name' => 'symlink_topic',
            ),
            'type' => 'select',
            'type_config' => array
            (
                'require_corresponding_option' => false,
                'options' => array(),
                'allow_other' => true,
                'allow_multiple' => false,
            ),
            'widget' => 'chooser',
            'widget_config' => array
            (
                'class'       => 'midcom_db_topic',
                'component'   => 'net.nehmer.blog',
                'titlefield'  => 'extra',
                'idfield'     => 'guid',
                'searchfields' => array
                (
                    array
                    (
                        'title' => 'ASC',
                    ),
                    array
                    (
                        'extra' => 'ASC',
                    ),
                    array
                    (
                        'component' => 'ASC',
                    ),
                    array
                    (
                        'name' => 'ASC',
                    ),
                ),
                'constraints' => array
                (
                    array
                    (
                        'field' => 'extra',
                        'op' => '<>',
                        'value' => '',
                    ),
                    array
                    (
                        'field' => 'component',
                        'op' => '=',
                        'value' => 'net.nehmer.blog',
                    ),
                ),
                'result_headers' => array
                (
                    array
                    (
                        'name' => 'extra',
                        'title' => 'extra',
                    ),
                    array
                    (
                        'name' => 'name',
                        'title' => 'name',
                    ),
                    array
                    (
                        'name' => 'component',
                        'title' => 'component',
                    ),
                ),
                'orders' => array
                (
                    array
                    (
                        'title' => 'ASC',
                    ),
                    array
                    (
                        'extra' => 'ASC',
                    ),
                    array
                    (
                        'name' => 'ASC',
                    ),
                ),
            ),
            'end_fieldset' => 1,
        ),
        'show_latest_in_navigation' => array
        (
            'title' => 'show latest items in navigation',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'net.nehmer.blog',
                'name' => 'show_latest_in_navigation',
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
            'start_fieldgroup' => array
            (
                'title' => 'navigation options',
            ),
        ),
        'show_navigation_pseudo_leaves' => array
        (
            'title' => 'show pseudo leaves in navigation',
            'helptext' => 'set this to no if you want to hide feeds and archive links in navigation',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'net.nehmer.blog',
                'name' => 'show_navigation_pseudo_leaves',
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
        'view_in_url' => array
        (
            'title' => 'use view prefix in url',
            'helptext' => 'set to no for de.linkm.newsticker compatibility',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'net.nehmer.blog',
                'name' => 'view_in_url',
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
            'end_fieldset' => 1,
        ),
        
        // **** RSS Stuff ****
        'rss_enable' => array
        (
            'title' => 'rss_enable',
            'helptext' => 'set to no for de.linkm.newsticker compatibility',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'net.nehmer.blog',
                'name' => 'rss_enable',
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
            'start_fieldgroup' => array
            (
                'title' => 'rss export settings',
            ),
        ),
        'rss_count' => array
        (
            'title' => 'rss_count',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'net.nehmer.blog',
                'name' => 'rss_count',
            ),
            'type' => 'number',
            'widget' => 'text',
        ),
        'rss_title' => array
        (
            'title' => 'rss_title',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'net.nehmer.blog',
                'name' => 'rss_title',
            ),
            'type' => 'number',
            'widget' => 'text',
        ),
        'rss_description' => array
        (
            'title' => 'rss_description',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'net.nehmer.blog',
                'name' => 'rss_description',
            ),
            'type' => 'number',
            'widget' => 'text',
        ),
        'rss_webmaster' => array
        (
            'title' => 'rss_webmaster',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'net.nehmer.blog',
                'name' => 'rss_webmaster',
            ),
            'type' => 'number',
            'widget' => 'text',
        ),
        'rss_language' => array
        (
            'title' => 'rss_language',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'net.nehmer.blog',
                'name' => 'rss_language',
            ),
            'type' => 'number',
            'widget' => 'text',
        ),
    ),
),