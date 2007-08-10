/**
 * This schema is used to show the settings form on
 * for the folder of your component
 */

'config' => array 
(
	'name'        => 'config',
	'description' => 'Default Configuration Schema', /* This is a topic */
	'locktimeout' => 0, /* Don't lock this, we always display the edit view */
	'fields'      => array 
	(
        'search_first' => array
        (
            'description' => 'display search on front page',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'org.openpsa.products',
            'config_key' => 'search_first',
            'widget' => 'config_radiobox',
            'widget_radiobox_choices' => array
            (
                '' => 'default setting',
                '1' => 'yes',
                '0' => 'no',
            ),
            'start_fieldgroup' => array
            ( 
                'title' => 'display settings',
            ),
        ),
        'search_default_to_all' => array
        (
            'description' => 'search all items on default',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'org.openpsa.products',
            'config_key' => 'search_default_to_all',
            'widget' => 'config_radiobox',
            'widget_radiobox_choices' => array
            (
                '' => 'default setting',
                '1' => 'yes',
                '0' => 'no',
            ),
        ),
        'enable_components' => array
        (
            'description' => 'whether to enable adding subcomponents to products',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'org.openpsa.products',
            'config_key' => 'enable_components',
            'widget' => 'config_radiobox',
            'widget_radiobox_choices' => array
            (
                '' => 'default setting',
                '1' => 'yes',
                '0' => 'no',
            ),
        ),
        'group_list_products' => array
        (
            'description' => 'list group products',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'org.openpsa.products',
            'config_key' => 'group_list_products',
            'widget' => 'config_radiobox',
            'widget_radiobox_choices' => array
            (
                '' => 'default setting',
                '1' => 'yes',
                '0' => 'no',
            ),
        ),
        'products_per_page' => array
        (
            'description' => 'products per page',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'org.openpsa.products',
            'config_key' => 'products_per_page',
        ),        
        'show_items_in_feed' => array
        (
            'description' => 'number of items in feed',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'org.openpsa.products',
            'config_key' => 'show_items_in_feed',
        ),        
        'root_group' => array
        (
            'description' => 'root product group',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'org.openpsa.products',
            'config_key' => 'root_group',
            'widget' => 'select',
            'widget_select_choices' => org_openpsa_products_product_group_dba::list_groups(0, '', 'guid'),
            'end_fieldgroup' => '',
        ),
        'schemadb_product' => array
        (
            'description' => 'product schema database',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'org.openpsa.products',
            'config_key' => 'schemadb_product',
            'start_fieldgroup' => array
            ( 
                'title' => 'advanced schema and data settings',
            ),
        ),
        'schemadb_group' => array
        (
            'description' => 'product group schema database',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'org.openpsa.products',
            'config_key' => 'schemadb_group',
        ),
        'schemadb_businessarea' => array
        (
            'description' => 'business area schema database',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'org.openpsa.products',
            'config_key' => 'schemadb_businessarea',
            'end_fieldgroup' => '',
        ),
	)
),
