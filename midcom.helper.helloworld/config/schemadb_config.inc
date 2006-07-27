// 2DO: Add l10n strings

'config' => array (
	'name'        => 'config',
	'description' => 'Default Configuration Schema', /* This is a topic */
	'locktimeout' => 0, /* Don't lock this, we always display the edit view */
	'fields'      => array (
	 
		/* This is the field for the sort order selection. Note, that the description
		 * and the widget fields will be translated automatically.
		 *
		 * Name and config_key must match the corresponding configuration key, the domain
		 * must match your component.
		 *
		 * Name of the field and its description is kept as-is, as the clear-text name is
		 * set through the l10n database of this component.
		 */
		'sort_order' => array (
			'description' => 'sort_order',
			'datatype' => 'text',
			'location' => 'config',
			'config_domain' => 'midcom.helper.helloworld',
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
		
		// 2DO: schemadb, schema
	)
),