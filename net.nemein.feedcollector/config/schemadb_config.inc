/**
 * This schema is used to show the settings form on
 * for the folder of your component
 */

'config' => array
(
    'description' => 'Default Configuration Schema', /* This is a topic */
    'fields'      => array
    (
        'sort_order' => array
        (
            'title' => 'sort_order',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'net.nemein.feedcollector',
                'name' => 'sort_order',
            ),
            'type' => 'select',
            'type_config' => array
            (
                'options' => array
                (
                    '' => 'default setting',
                    'reverse score' => 'reverse score',
                    'score' => 'score',
                    'reverse title' => 'reverse title',
                    'title' => 'title',
                    'reverse created' => 'reverse created',
                    'created' => 'created',
                ),
            ),
            'widget' => 'select',
        ),
        'topic_introduction' => array
        (
            'title' => 'text to place over the content',
            'helptext' => 'this text comes to the top of your content',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'net.nemein.feedcollector',
                'name' => 'topic_introduction',
            ),
            'type' => 'text',
            'type_config' => array
            (
                'output_mode' => 'html',
            ),
            'widget' => 'tinymce',
            'start_fieldset' => Array
            (
                'title' => 'display settings',
            ),
        ),
        'articles_count_index' => array
        (
            'title' => 'articles count index',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'net.nemein.feedcollector',
                'name' => 'articles_count_index',
            ),
            'type' => 'text',
            'widget' => 'text',
        ),
        'articles_count_latest_default' => array
        (
            'title' => 'articles count latest default',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'net.nemein.feedcollector',
                'name' => 'articles_count_latest_default',
            ),
            'type' => 'text',
            'widget' => 'text',
            'end_fieldset' => '',
        ),
    )
),
