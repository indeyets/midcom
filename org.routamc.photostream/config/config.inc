'schemadb' => 'file:/org/routamc/photostream/config/schemadb_default.inc',
'photos_per_page' => 20,

// Entry page
'entry_page' => 'lists',

// Email API settings
'api_email_enable' => false,
'api_email_schema' => 'photo',
'api_email_default_tags' => 'moblog',

// If not using the chooser widget (for whatever reason) setting this to true is highly recommended.
'to_gallery_append_static_options' => true,

/* not implemented yet
'api_metaweblog_enable' => true,
'api_metaweblog_schema' => 'photo',
*/

// Flickr synchronization rules
'flickr_api_key' => 'f68bbf3e322b2f6cb487b7c50f003fea',
'flickr_import_size' => 'Large', // In some cases you want Original

// Use AJAX for editing
'enable_ajax_editing' => true,

// Stick to the context in photo navigation (e.g. show the next and previous only from the selected user or tag group)
'navigate_with_context' => true,
// Resolve next/previous photo for use in style (can be computationally expensive for large streams!)
'load_next_prev' => true,

// Configuration schema
'schemadb_config' => 'file:/org/routamc/photostream/config/schemadb_config.inc',

// Creation callback snippet for defining site-specific function
'create_callback_snippet' => null,

// Callback function will be called after creation e.g. to provide interface for page linking system
// $create_callback_function($photo, $topic)
'create_callback_function' => null,

// Require moderating for newly uploaded photos
'moderate_uploaded_photos' => false,

// Set the email address if you wish to receive a message of new photos
'moderator_email' => null,

// Address from which the email messages will be sent
'system_mailer_address' => '',

// Key-value pairs for replacing __{$key}__ tags with the defined value for customized messages
'mail_variables' => array
(
    'URL' => substr($_MIDCOM->get_page_prefix(), 0, -1) . $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX),
    'DATE' => strftime('%c'),
),

// Moderator mail subject (will be localized)
'moderator_mail_subject' => 'new photos waiting for moderation',
'moderator_mail_body' => 'moderation_mail_body',

// Send message to the user (if applicable) when the status changes
'send_statuschange_message' => true,

// Status messages for accepted photo
'status_accepted_title' => 'your photo has been accepted',
'status_accepted_body' => 'accepted_photo_message',

// Status messages for rejected photo
'status_rejected_title' => 'your photo has been rejected',
'status_rejected_body' => 'rejected_photo_message',

// Moderate callback snippet will be evaluated after moderating a photo
'moderate_callback_snippet' => null,

// Moderate callback function will be called after moderating
// $callback_function($photo, $topic)
'moderate_callback_function' => null,

// Symlink topic
'symlink_topic' => null,
