// Data settings
'schemadb' => 'file:/net/nemein/registrations/config/schemadb_default.inc',
'registrar_schema' => 'person',
'event_schema' => 'event',
'content_topic_guid' => null,
'event_type' => null,
'account_grp' => null,
'display_leaves' => false,
'allow_multiple' => false, // Allow registering to multiple events in single go (use 'events' key in person schema)

// Registration Mail template
'mail_registration_subject' => 'Event registration for __REGEVENT_title__',
'mail_registration_sender' => "webmaster@{$_SERVER['SERVER_NAME']}",
'mail_registration_ccs' => "webmaster@{$_SERVER['SERVER_NAME']}",
/* the body is rendered by style element compose-accept_text-mail */

// Registration Rejection Mail template
'mail_registration_reject_subject' => 'Event registration for __REGEVENT_title__ rejected',
'mail_registration_reject_sender' => "webmaster@{$_SERVER['SERVER_NAME']}",
'mail_registration_reject_ccs' => "webmaster@{$_SERVER['SERVER_NAME']}",
/* the body is rendered by style element compose-reject_text-mail */

// Operational settings
'welcome_page_list_closed' => false,

/**
 * Class to use as pricing plugin
 *
 * The class must accept configuration as constructor argument
 * and must implement process() method that will take as a
 * reference the net_nemein_registrations_registration_dba object
 */
'pricing_plugin' => false,
/**
 * Passed as configuration argument to the plugin
 *
 * The plugin is responsible for validation
 */
'pricing_plugin_config' => array(),
/**
 * Since the registration schema is merge of two schemas
 * we cannot 100% control the field order in schema only
 * in this array we can specify preferred order
 */
'merged_schema_field_order' => array(),
// Will admin_mode registration loop back to itself ?
'admin_register_loop' => false,
'admin_register_loop_clear_fields' => array('p_firstname', 'p_lastname', 'p_email'),



