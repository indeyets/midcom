'config' => array (
 'name'        => 'config',
 'description' => 'Default Configuration Schema', /* This is a topic */
 'locktimeout' => 0, /* Don't lock this, we display the edit view always */
 'fields'      => array (
  
  'default_quota' => array (
   'description' => 'default_quota',
   'datatype' => 'text',
   'location' => 'config',
   'config_domain' => 'net.nehmer.mail',
   'config_key' => 'default_quota',
   'widget' => 'select',
   'widget_select_choices' => Array (
    '' => 'default setting',
    '10' => '10',
    '25' => '25',
    '50' => '50',
    '100' => '100',
    '500' => '500',
    '0' => 'no limit',
   ),
  ),

 )
),
