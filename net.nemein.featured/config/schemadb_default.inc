'default' => array
(
    'description' => 'featured',
    'fields'      => array
    (
        'group_name' => Array
        (
	      // COMPONENT-REQUIRED
	     'title' => 'group name',
             'storage' => 'groupName',
             'type' => 'select',
	     'type_config' => array
	     (
	         'options' => net_nemein_featured_admin::get_groups(),
             ),
	     'widget' => 'select',
        ),
	/*
        'topic_guid' => Array
        (
	      // COMPONENT-REQUIRED
	     'title' => 'topic guid',
             'storage' => 'topicGuid',
             'type' => 'text',
	     'hidden' => true
        ),
	*/
        'object_location' => Array
        (
	      // COMPONENT-REQUIRED
	     'title' => 'object location',
             'storage' => 'objectLocation',
             'type' => 'text',
	     'widget' => 'text',
        ),
    ),
),
