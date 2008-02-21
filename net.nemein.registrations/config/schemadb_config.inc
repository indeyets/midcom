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

  'registrar_schema' => array (
   'description' => 'registrar schema',
   'datatype' => 'text',
   'location' => 'config',
   'config_domain' => 'net.nemein.registrations',
   'config_key' => 'registrar_schema',
   'widget' => 'schemaselect',
   'widget_schemaselect_linkto' => 'schemadb',
   'widget_schemaselect_default_schemapath' => 'file:/net/nemein/registrations/config/schemadb_default.inc',
  ),

  'event_schema' => array (
   'description' => 'event schema',
   'datatype' => 'text',
   'location' => 'config',
   'config_domain' => 'net.nemein.registrations',
   'config_key' => 'event_schema',
   'widget' => 'schemaselect',
   'widget_schemaselect_linkto' => 'schemadb',
   'widget_schemaselect_default_schemapath' => 'file:/net/nemein/registrations/config/schemadb_default.inc',
  ),

  'content_topic_guid' => array (
   'description' => 'content_topic_guid',
   'datatype' => 'text',
   'location' => 'config',
   'config_domain' => 'net.nemein.registrations',
   'config_key' => 'content_topic_guid',
  ),

  'event_type' => array (
   'description' => 'event type',
   'datatype' => 'text',
   'location' => 'config',
   'config_domain' => 'net.nemein.registrations',
   'config_key' => 'event_type',
  ),

  'account_grp' => array (
   'description' => 'account group guid',
   'datatype' => 'text',
   'location' => 'config',
   'config_domain' => 'net.nemein.registrations',
   'config_key' => 'account_grp',
  ),

  'welcome_page_list_closed' => array (
   'description' => 'welcome_page_list_closed',
   'datatype' => 'text',
   'location' => 'config',
   'config_domain' => 'net.nemein.registrations',
   'config_key' => 'welcome_page_list_closed',
   'widget' => 'config_radiobox',
   'widget_radiobox_choices' => Array (
    '' => 'default setting',
    '1' => 'yes',
    '0' => 'no',
   ),
  ),
 )
),
