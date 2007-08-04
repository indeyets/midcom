/* The schema database to use */
'schemadb' => 'file:/net/nehmer/mail/config/schemadb_default.inc',

'default_quota' => 100,

'enable_notifications' => true,
'notification_mail_sender' => null, // null means sender == receiver
'notification_mail_subject' => 'notification_mail_subject', // Will be localized
'notification_mail_abstract_body' => 'notification_mail_abstract_body', // Will be localized
'notification_mail_body' => 'notification_mail_body', // Will be localized

'enable_live_preview' => true,