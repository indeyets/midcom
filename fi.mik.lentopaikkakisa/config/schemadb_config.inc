'config' => array (
 'name'        => 'config',
 'description' => 'Default Configuration Schema', /* This is a topic */
 'locktimeout' => 0, /* Don't lock this, we display the edit view always */
 'fields'      => array (

  'schemadb' => array (
   'description' => 'schemadb path',
   'datatype' => 'text',
   'location' => 'config',
   'config_domain' => 'net.nemein.registrations',
   'config_key' => 'schemadb',
  ),
 )
),
