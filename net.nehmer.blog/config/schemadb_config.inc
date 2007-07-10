'config' => array
(
    'name'        => 'config',
    'description' => 'Default Configuration Schema',
    'locktimeout' => 0,
    'fields'      => array
    (
        'index_entries' => array
        (
            'description' => 'index_entries',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nehmer.blog',
            'config_key' => 'index_entries',
            'widget' => 'text',
            'start_fieldgroup' => array
            (
                'title' => 'blog settings'
            ),
        ),
        'categories' => array
        (
            'description' => 'categories',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nehmer.blog',
            'config_key' => 'categories',
            'widget' => 'text',
            'end_fieldgroup' => '',
        ),
        'schemadb' => array
        (
            'description' => 'schemadb',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nehmer.blog',
            'config_key' => 'schemadb',
            'widget' => 'select',
            'widget_select_choices' => $GLOBALS['net_nehmer_blog_schemadbs'],
            'start_fieldgroup' => array
            (
                'title' => 'schema settings',
            ),
        ),
        'comments_enable' => array
        (
            'description' => 'comments_enable',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nehmer.blog',
            'config_key' => 'comments_enable',
            'widget' => 'config_radiobox',
            'widget_radiobox_choices' => array
            (
                '' => 'default setting',
                '1' => 'yes',
                '0' => 'no',
            ),
        ),
        'ajax_enable' => array
        (
            'description' => 'ajax_enable',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nehmer.blog',
            'config_key' => 'enable_ajax_editing',
            'widget' => 'config_radiobox',
            'widget_radiobox_choices' => array
            (
                '' => 'default setting',
                '1' => 'yes',
                '0' => 'no',
            ),
        ),
        'archive_enable' => array
        (
            'description' => 'archive_enable',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nehmer.blog',
            'config_key' => 'archive_enable',
            'widget' => 'config_radiobox',
            'widget_radiobox_choices' => array
            (
                '' => 'default setting',
                '1' => 'yes',
                '0' => 'no',
            ),
        ),
        'archive_item_order' => array
        (
            'description' => 'archive item order',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nehmer.blog',
            'config_key' => 'archive_item_order',
            'widget' => 'config_radiobox',
            'widget_radiobox_choices' => array
            (
                '' => 'default setting',
                'ASC' => 'ascending',
                'DESC' => 'descending',
            ),
        ),
        'rss_subscription_enable' => array
        (
            'description' => 'rss_subscription_enable',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nehmer.blog',
            'config_key' => 'rss_subscription_enable',
            'widget' => 'config_radiobox',
            'widget_radiobox_choices' => array
            (
                '' => 'default setting',
                '1' => 'yes',
                '0' => 'no',
            ),
        ),
        'symlink_topic' => array
        (
            'description' => 'symlink_topic',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nehmer.blog',
            'config_key' => 'symlink_topic',
            'widget' => 'select',
            'widget_select_choices' => net_nehmer_blog_symlink_topic_list(),
            'end_fieldgroup' => '',
        ),
        'show_latest_in_navigation' => array
        (
            'start_fieldgroup' => array
            (
                'title' => 'navigation options',
            ),
            'description' => 'show latest items in navigation',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nehmer.blog',
            'config_key' => 'show_latest_in_navigation',
            'widget' => 'config_radiobox',
            'widget_radiobox_choices' => array
            (
                '' => 'default setting',
                '1' => 'yes',
                '0' => 'no',
            ),
        ),
        'show_navigation_pseudo_leaves' => array
        (
            'description' => 'show pseudo leaves in navigation',
            'helptext' => 'set this to no if you want to hide feeds and archive links in navigation',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nehmer.blog',
            'config_key' => 'show_navigation_pseudo_leaves',
            'widget' => 'config_radiobox',
            'widget_radiobox_choices' => array
            (
                '' => 'default setting',
                '1' => 'yes',
                '0' => 'no',
            ),
            'end_fieldgroup' => '',
        ),
        
        // **** RSS Stuff ****
        'rss_enable' => array
        (
            'description' => 'rss_enable',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nehmer.blog',
            'config_key' => 'rss_enable',
            'widget' => 'config_radiobox',
            'widget_radiobox_choices' => array
            (
                '' => 'default setting',
                '1' => 'yes',
                '0' => 'no',
            ),
            'start_fieldgroup' => array
            (
                'title' => 'rss export settings',
            ),
        ),
        'rss_count' => array
        (
            'description' => 'rss_count',
            'datatype' => 'text',
            'number_precision' => 0,
            'location' => 'config',
            'config_domain' => 'net.nehmer.blog',
            'config_key' => 'rss_count',
        ),
        'rss_title' => array
        (
            'description' => 'rss_title',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nehmer.blog',
            'config_key' => 'rss_title',
        ),
        'rss_description' => array
        (
            'description' => 'rss_description',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nehmer.blog',
            'config_key' => 'rss_description',
        ),
        'rss_webmaster' => array
        (
            'description' => 'rss_webmaster',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nehmer.blog',
            'config_key' => 'rss_webmaster',
        ),
        'rss_language' => array
        (
            'description' => 'rss_language',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nehmer.blog',
            'config_key' => 'rss_language',
            'end_fieldgroup' => '',
        ),
    ),
),
