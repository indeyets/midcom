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
   'config_domain' => 'net.nehmer.marketplace',
   'config_key' => 'schemadb',
  ),
*/

  'page_size' => array (
   'description' => 'page_size',
   'datatype' => 'text',
   'location' => 'config',
   'config_domain' => 'net.nehmer.marketplace',
   'config_key' => 'page_size',
  ),

  'expiration_days' => array (
   'description' => 'expiration_days',
   'datatype' => 'text',
   'location' => 'config',
   'config_domain' => 'net.nehmer.marketplace',
   'config_key' => 'expiration_days',
  ),

  'indexed' => array (
   'description' => 'indexed',
   'datatype' => 'text',
   'location' => 'config',
   'config_domain' => 'branchenbuch',
   'config_key' => 'indexed',
   'widget' => 'config_radiobox',
   'widget_radiobox_choices' => Array (
    '' => 'default setting',
    '1' => 'yes',
    '0' => 'no',
   ),
  ),
 )
),
