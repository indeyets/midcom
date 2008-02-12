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
