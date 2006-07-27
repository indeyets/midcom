'config' => array (
 'name'        => 'config',
 'description' => 'Default Configuration Schema', /* This is a topic */
 'locktimeout' => 0, /* Don't lock this, we display the edit view always */
 'fields'      => array (

  'schemadb_account' => array (
   'description' => 'schemadb_account',
   'datatype' => 'text',
   'location' => 'config',
   'config_domain' => 'net.nehmer.account',
   'config_key' => 'schemadb_account',
  ),

  'schemadb_account_creation' => array (
   'description' => 'schemadb_account_creation',
   'datatype' => 'text',
   'location' => 'config',
   'config_domain' => 'net.nehmer.account',
   'config_key' => 'schemadb_account_creation',
  ),

  'password_minlength' => array (
   'description' => 'password_minlength',
   'datatype' => 'text',
   'location' => 'config',
   'config_domain' => 'net.nehmer.account',
   'config_key' => 'password_minlength',
  ),

  'publish_all_on_activation' => array
  (
   'description' => 'publish_all_on_activation',
   'datatype' => 'text',
   'location' => 'config',
   'config_domain' => 'net.nehmer.account',
   'config_key' => 'publish_all_on_activation',
   'widget' => 'config_radiobox',
   'widget_radiobox_choices' => Array
   (
    '' => 'default setting',
    '1' => 'yes',
    '0' => 'no',
   ),
  ),

 )
),
