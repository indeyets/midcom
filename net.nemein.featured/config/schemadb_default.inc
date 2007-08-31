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
	         'options' => net_nemein_featured_viewer::get_groups(),
             ),
	     'widget' => 'select',
        ),
        'object_location' => Array
        (
	      // COMPONENT-REQUIRED
	     'title' => 'object location',
             'storage' => 'objectLocation',
             'type' => 'text',
	     'widget' => 'text',
        ),
        'defaultStyle' => Array
        (
	      // COMPONENT-REQUIRED
	     'title' => 'default style',
             'storage' => 'defaultStyle',
             'type' => 'text',
	     'widget' => 'text',
        ),
    ),
),
