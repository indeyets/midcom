'schemadb_account' => 'file:/net/nehmer/account/config/schemadb_account.inc',
'schemadb_account_creation' => 'file:/net/nehmer/account/config/schemadb_account_creation.inc',

// Features
'allow_register' => true,
'allow_change_username' => true,
'allow_cancel_membership' => true,
'allow_publish' => true,
'allow_view_by_username' => true, // the paranoid may want to disable this as it allows determining username validity

// add group id to automatically assign the user to it
'assign_to_group' => null,

'username_is_email' => false,
'register_allow_types' => null,

// This option is not yet completly implemented.
'fallback_type' => null, 

'password_minlength' => 6,
'on_cancel_membership' => null,
// 'on_cancel_membership' => Array
// (
//     'callback' => 'callback_function_name',
//     'autoload_snippet' => 'snippet_name', // optional
//     'autoload_file' => 'filename', // optional
// ),
'on_activate_account' => null,
// 'on_activate_account' => Array
// (
//     'callback' => 'callback_function_name',
//     'autoload_snippet' => 'snippet_name', // optional
//     'autoload_file' => 'filename', // optional
// ),
'account_activation_timeout' => 7, // days
'publish_all_on_activation' => false,

// Avatar Sizes
'avatar_x' => 190,
'avatar_y' => 190,
'avatar_thumbnail_x' => 50,
'avatar_thumbnail_y' => 50,

// Onsite Welcome Mail
'have_net_nehmer_mail' => false,
'welcome_mail_subject' => 'welcome_mail_subject', // Will be localized
'welcome_mail_body' => 'welcome_mail_body', // Will be localized
'welcome_mail_sender' => '', // Sender and receiver is identical, put in a guid otherwise.

// Avtivaton / Password Emails
'activation_mail_sender' => null, // Means sender == receiver
'activation_mail_subject' => 'activation_mail_subject', // Will be localized
'activation_mail_body' => 'activation_mail_body', // Will be localized

'password_reset_mail_sender' => null, // Means sender == receiver
'password_reset_mail_subject' => 'password_reset_mail_subject', // Will be localized
'password_reset_mail_body' => 'password_reset_mail_body', // Will be localized

// see the docs of the viewer/admin classes for details about the plugin handlers
'plugins' => array
(
    'notifications' => Array
    (
        'class' => 'org_openpsa_notifications_preferences',
        'src' => 'file:/org/openpsa/notifications/preferences.php',
        'name' => 'Notification preferences',
        'config' => null,
    ),
),
'admin_plugins' => null,

/**
 * How many invite email field should be shown
 */
 'email_fields' => 5,

/**
 * Invite email subject
 */
 'email_subject' => "invitation subject",

