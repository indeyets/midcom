'config' => array
(
    'name'        => 'config',
    'description' =>  'Default Configuration Schema', /* This is a topic */
    'locktimeout' => 0, /* Don't lock this, we display the edit view always */
    'fields'      => array
    (
  
        /**** Display settings ****/
        'index_count' => array
        (
            'title' =>  'index count',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'net.nemein.calendar',
                'name' => 'index_count',
            ),
            'type' => 'text',
            'widget' => 'text',
            'start_fieldset' => array
            (
                'title' => 'display settings',
            ),
        ),
        
        'categories' => array
        (
            'title' =>  'categories',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'net.nemein.calendar',
                'name' => 'categories',
            ),
            'type' => 'text',
            'widget' => 'text',
        ),

        
        'language' => array
        (
            'title' => 'language',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'net.nemein.calendar',
                'name' => 'language',
            ),
            'type' => 'select',
            'type_config' => array
            (
                'options' => array_merge(array('' => 'default setting'), $_MIDCOM->i18n->list_languages()),
            ),
            'widget' => 'select',
        ),
        
        'type_filter_upcoming' => array
        (
            'title' =>  'type filter for upcoming',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'net.nemein.calendar',
                'name' => 'type_filter_upcoming',
            ),
            'type' => 'text',
            'widget' => 'text',
        ),

        'enable_filters' => array
        (
            'title' =>  'enable get filters',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'net.nemein.calendar',
                'name' => 'enable_filters',
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
  
        'list_from_master' => array
        (
            'title' =>  'list events from master event',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'net.nemein.calendar',
                'name' => 'list_from_master',
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
        'list_from_folders' => array
        (
            'title' => 'list events from folders',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'net.nemein.calendar',
                'name' => 'list_from_folders',
            ),
            'type' => 'select',
            'type_config' => array
            (
                'require_corresponding_option' => false,
                'allow_multiple' => true,
                'options' => array (),
                'multiple_storagemode' => 'imploded_wrapped',
            ),
            'widget' => 'chooser',
            'widget_config' => array
            (
                'class'       => 'midcom_db_topic',
                'component'   => 'net.nemein.calendar',
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
                        'value' => 'net.nemein.calendar',
                    ),
                ),
                'result_headers' => array
                (
                    array
                    (
                        'name' => 'extra',
                        'title' => 'name',
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
        ),

        'show_events_locally' => array
        (
            'title' =>  'show events locally',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'net.nemein.calendar',
                'name' => 'show_events_locally',
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

        'archive_year_order' => array
        (
            'title' =>  'archive year order',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'net.nemein.calendar',
                'name' => 'archive_year_order',
            ),
            'type' => 'select',
            'type_config' => array
            (
                'options' => array
                (
                    '' => 'default setting',
                    'DESC' => 'DESC',
                    'ASC' => 'ASC',
                ),
            ),
            'widget' => 'select',
            'end_fieldset' => '',
        ),
        
        'javascript_hover' => array
        (
            'start_fieldset' => array
            (
                'title' => 'calendar widget',
            ),
            'title' =>  'use javascript to emulate hovering effect',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'net.nemein.calendar',
                'name' => 'javascript_hover',
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
        
        'rss_subscription_enable' => array
        (
            'title' =>  'enable rss subscription',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'net.nemein.calendar',
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
            'start_fieldset' => array
            (
               'title' => 'rss subscriptions',
            ),
        ),
        
        'rss_count' => array
        (
            'title' =>  'rss item count',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'net.nemein.calendar',
                'name' => 'rss_count',
            ),
            'type' => 'text',
            'widget' => 'text',
            'end_fieldset' => 1,
        ),
        
         /**** Schema settings ****/
        'schemadb' => array
        (
            'title' =>  'schema database',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'net.nemein.calendar',
                'name' => 'schemadb',
            ),
            'type' => 'text',
            'widget' => 'text',
            'start_fieldset' => array
            (
                'title' => 'advanced schema and data settings'
            ),
        ),
        
        'symlink_topic' => array
        (
            'title' => 'symlink topic',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'net.nemein.calendar',
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
                'component'   => 'net.nemein.calendar',
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
                        'value' => 'net.nemein.calendar',
                    ),
                ),
                'result_headers' => array
                (
                    array
                    (
                        'name' => 'extra',
                        'title' => 'title',
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
    ),
),