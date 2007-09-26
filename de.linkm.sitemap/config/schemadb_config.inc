'config' => array (
    'name'        => 'config',
    'description' => 'Default Configuration Schema', /* This is a topic */
    'locktimeout' => 0, /* Don't lock this, we display the edit view always */
    'fields'      => array 
    (  
        'display_root' => array 
        (
            'description'   => 'display root node',
            'datatype'      => 'text',
            'location'      => 'config',
            'config_domain' => 'de.linkm.sitemap',
            "config_key"    => 'display_root',
            'widget'        => 'config_radiobox',
            'widget_radiobox_choices' => array 
            (
                '' => 'default setting',
                '1' => 'yes',
                '0' => 'no',
            ),
        ),
        
        'hide_leaves' => array 
        (
            'description'   => 'hide leaves',
            'datatype'      => 'text',
            'location'      => 'config',
            'config_domain' => 'de.linkm.sitemap',
            "config_key"    => 'hide_leaves',
            'widget'        => 'config_radiobox',
            'widget_radiobox_choices' => array 
            (
                '' => 'default setting',
                '1' => 'yes',
                '0' => 'no',
            ),
        ),
        
        'leaves_first' => array 
        (
            'description'   => 'display leaves first',
            'datatype'      => 'text',
            'location'      => 'config',
            'config_domain' => 'de.linkm.sitemap',
            "config_key"    => 'leaves_first',
            'widget'        => 'config_radiobox',
            'widget_radiobox_choices' => array 
            (
                '' => 'default setting',
                '1' => 'yes',
                '0' => 'no',
            ),
        ),
        
        'root_topic' => array 
        (
            'description'   => 'root topic to use',
            'datatype'      => 'text',
            'location'      => 'config',
            'config_domain' => 'de.linkm.sitemap',
            "config_key"    => 'root_topic',
            'widget'        => 'select',
            'widget_select_choices' => de_linkm_sitemap_viewer::list_root_nodes(),
        ),
        'skip_topics' => array 
        (
            'description'   => 'skip topics',
            'datatype'      => 'multiselect',
            'location'      => 'config',
            'config_domain' => 'de.linkm.sitemap',
            "config_key"    => 'skip_topics',
            'widget'        => 'multiselect',
            "widget_select_size" => 10,
            'multiselect_selection_list' => de_linkm_sitemap_viewer::list_root_nodes(),
        ),
        'show_levels' => array 
        (
            'description'   => 'Show levels',
            'datatype'      => 'text',
            'location'      => 'config',
            'config_domain' => 'de.linkm.sitemap',
            "config_key"    => 'show_levels',
        ),
        'google_sitemap_mode' => array
        (
            'description'   => 'use google sitemap mode',
            'datatype'      => 'text',
            'location'      => 'config',
            'config_domain' => 'de.linkm.sitemap',
            'config_key'    => 'google_sitemap_mode',
            'widget'        => 'config_radiobox',
            'widget_radiobox_choices' => array
            (
                '' => 'default setting',
                '1' => 'yes',
                '0' => 'no',
            ),
        ),
    )
),