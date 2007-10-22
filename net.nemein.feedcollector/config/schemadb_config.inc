/**
 * This schema is used to show the settings form on
 * for the folder of your component
 */

'config' => array (
	'name'        => 'config',
	'description' => 'Default Configuration Schema', /* This is a topic */
	'locktimeout' => 0, /* Don't lock this, we always display the edit view */
	'fields'      => array (
		'sort_order' => array (
			'description' => 'sort_order',
			'datatype' => 'text',
			'location' => 'config',
			'config_domain' => 'net.nemein.feedcollector',
			'config_key' => 'sort_order',
			'widget' => 'select',
			'widget_select_choices' => Array (
				'' => 'default setting',
				'reverse score' => 'reverse score',
				'score' => 'score',
				'reverse title' => 'reverse title',
				'title' => 'title',
				'reverse created' => 'reverse created',
				'created' => 'created',
			),
		),
		'topic_introduction' => array
        (
            'description' => 'text to place over the content',
            'datatype' => 'text',
            'location' => 'config',
            'widget' => 'html',
            'config_key' => 'form_description',
            'config_domain' => 'net.nemein.feedcollector',
            'widget_text_height' => '4em',
            'helptext'  => 'this text comes to the top of your content',
            'start_fieldgroup' => Array
            (
                'title' => 'display settings'
            ),
        ),
        'articles_count_index' => array
        (
            'description' => 'articles count index',
            'datatype' => 'text',
            'location' => 'config',
            'config_key' => 'articles_count_index',
            'config_domain' => 'net.nemein.feedcollector',
        ),
        'articles_count_latest_default' => array
        (
            'description' => 'articles count latest default',
            'datatype' => 'text',
            'location' => 'config',
            'config_key' => 'articles_count_latest_default',
            'config_domain' => 'net.nemein.feedcollector',
            'end_fieldgroup' => '',
        ),
	)
),
