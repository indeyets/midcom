'config' => array
(
 'name'        => 'config',
 'description' => 'Default Configuration Schema',
 'locktimeout' => 0,
 'fields'      => array (

  'welcome_handler' => array
  (
   'description' => 'welcome_handler',
   'datatype' => 'text',
   'location' => 'config',
   'config_domain' => 'net.nehmer.publications',
   'config_key' => 'welcome_handler',
   'widget' => 'select',
   'widget_select_choices' => Array
   (
     '' => 'default setting',
     'latest' => 'welcome_handler_latest',
     'index' => 'welcome_handler_index',
     'categories' => 'welcome_handler_categories',
   ),
  ),

  'latest_entries' => array
  (
   'description' => 'index count',
   'datatype' => 'text',
   'location' => 'config',
   'config_domain' => 'net.nehmer.publications',
   'config_key' => 'latest_entries',
   'widget' => 'text',
  ),
  'index_entries' => array
  (
   'description' => 'alpha count',
   'datatype' => 'text',
   'location' => 'config',
   'config_domain' => 'net.nehmer.publications',
   'config_key' => 'index_entries',
   'widget' => 'text',
  ),
  'index_order' => array
  (
   'description' => 'index order',
   'datatype' => 'text',
   'location' => 'config',
   'config_domain' => 'net.nehmer.publications',
   'config_key' => 'index_order',
   'widget' => 'text',
  ),

  'schemadb' => array
  (
   'description' => 'schemadb',
   'datatype' => 'text',
   'location' => 'config',
   'config_domain' => 'net.nehmer.publications',
   'config_key' => 'schemadb',
   'widget' => 'select',
   'widget_select_choices' => $GLOBALS['net_nehmer_publications_schemadbs'],
   'start_fieldgroup' => Array ( 'title' => 'schema settings' ),
   'end_fieldgroup' => '',
  ),

  // **** RSS Stuff ****

  'rss_enable' => array
  (
   'description' => 'rss_enable',
   'datatype' => 'text',
   'location' => 'config',
   'config_domain' => 'net.nehmer.publications',
   'config_key' => 'rss_enable',
   'widget' => 'config_radiobox',
   'widget_radiobox_choices' => Array
   (
    '' => 'default setting',
    '1' => 'yes',
    '0' => 'no',
   ),
   'start_fieldgroup' => Array ( 'title' => 'rss export settings' ),
  ),

  'rss_count' => array (
   'description' => 'rss_count',
   'datatype' => 'text',
   'number_precision' => 0,
   'location' => 'config',
   'config_domain' => 'net.nehmer.publications',
   'config_key' => 'rss_count',
  ),

  'rss_title' => array (
   'description' => 'rss_title',
   'datatype' => 'text',
   'location' => 'config',
   'config_domain' => 'net.nehmer.publications',
   'config_key' => 'rss_title',
  ),

  'rss_description' => array (
   'description' => 'rss_description',
   'datatype' => 'text',
   'location' => 'config',
   'config_domain' => 'net.nehmer.publications',
   'config_key' => 'rss_description',
  ),

  'rss_webmaster' => array
  (
   'description' => 'rss_webmaster',
   'datatype' => 'text',
   'location' => 'config',
   'config_domain' => 'net.nehmer.publications',
   'config_key' => 'rss_webmaster',
  ),

  'rss_language' => array
  (
   'description' => 'rss_language',
   'datatype' => 'text',
   'location' => 'config',
   'config_domain' => 'net.nehmer.publications',
   'config_key' => 'rss_language',
   'end_fieldgroup' => '',
  ),

 ),
),
