'config' => array (
 'name'        => 'config',
 'description' => 'Default Configuration Schema', /* This is a topic */
 'locktimeout' => 0, /* Don't lock this, we display the edit view always */
 'fields'      => array (

/*
  'schemadb' => array (
   'description' => 'schemadb',
   'datatype' => 'text',
   'location' => 'config',
   'config_domain' => 'net.nehmer.branchenbuch',
   'config_key' => 'schemadb',
  ),
*/

  'default_root_category' => array (
   'description' => 'default_root_category',
   'datatype' => 'text',
   'location' => 'config',
   'config_domain' => 'net.nehmer.branchenbuch',
   'config_key' => 'default_root_category',
  ),

  'category_list_page_size' => array (
   'description' => 'category_list_page_size',
   'datatype' => 'text',
   'location' => 'config',
   'config_domain' => 'net.nehmer.branchenbuch',
   'config_key' => 'category_list_page_size',
  ),

  'index_to' => array (
   'description' => 'index_to',
   'datatype' => 'text',
   'location' => 'config',
   'config_domain' => 'net.nehmer.branchenbuch',
   'config_key' => 'index_to',
   'widget' => 'select',
   'widget_select_choices' => net_nehmer_branchenbuch_index_to_topic_list(),
   'end_fieldgroup' => '',
  ),

 )
),
