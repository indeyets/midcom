// Data settings
'schemadb' => 'file:/net/nemein/registrations/config/schemadb_default.inc',
'registrar_schema' => 'person',
'event_schema' => 'event',
'root_event_guid' => null,
'event_type' => null,
'account_grp' => null,
'display_leaves' => false,
'allow_multiple' => false, // Allow registering to multiple events in single go (use 'events' key in person schema)

// Registration Mail template
'mail_registration_subject' => 'Event registration for __REGEVENT_title__',
'mail_registration_sender' => "webmaster@{$_SERVER['SERVER_NAME']}",
'mail_registration_body' => 'file:/net/nemein/registrations/config/registration_mail.inc',
'mail_registration_ccs' => "webmaster@{$_SERVER['SERVER_NAME']}",

// Registration Rejection Mail template
'mail_registration_reject_subject' => 'Event registration for __REGEVENT_title__ rejected',
'mail_registration_reject_sender' => "webmaster@{$_SERVER['SERVER_NAME']}",
'mail_registration_reject_body' => 'file:/net/nemein/registrations/config/registration_reject_mail.inc',
'mail_registration_reject_ccs' => "webmaster@{$_SERVER['SERVER_NAME']}",

// Operational settings
'welcome_page_list_closed' => false,

// CSV export config (leave null for csv export class default)
'csv_separator' => null,
'csv_newline' => null,