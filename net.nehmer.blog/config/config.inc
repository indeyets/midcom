// Schema database to use
'schemadb' => 'file:/net/nehmer/blog/config/schemadb_default.inc',
'schemadb_feed' => 'file:/net/nemein/rss/config/schemadb_default.inc',
'schemadbs'      => Array
(
    'file:/net/nehmer/blog/config/schemadb_default.inc' => 'Default Schema Database',
),

// Use the content from another topic instead of the current one.
// Do not overwrite this except on a per-topic level!!!
'symlink_topic' => null,

'view_in_url' => true,

// Specifies how many entries should be shown in the index page.
'index_entries' => 8,

'index_fulltext' => false,

// RSS export
'rss_enable' => true,  // enable RSS export on rss/latest.xml
'rss_count' => 20, // show that many items by default (if called 'rss.xml' without any count)
'rss_title' => null,  // RSS feed title, leave null to use the topic title
'rss_description' => '',
'rss_webmaster' => '',
'rss_language' => '',
'rss_use_content' => true, // Set this to false to use only the abstract field instead of the content field.
'rss_use_image' => false, // Set this to true to include the image field into RSS content

// Archive status
'archive_enable' => true,
'archive_item_order' => 'ASC',

'comments_enable' => false,

// Navigation
'show_latest_in_navigation' => false,  // Set this to true if you want to show latest news in navigation
'show_navigation_pseudo_leaves' => true,   // Set this to false if you want to hide archive and feeds navigation items

// Set this to true if you want to enable on-site AJAX editing
'enable_ajax_editing' => false,

// Comma-separated list of categories enabled in this blog
'categories' => '',
'categories_custom_enable' => true,

'api_email_enable' => false,
'api_email_schema' => 'default',

'api_metaweblog_enable' => true,
'api_metaweblog_schema' => 'default',

'rss_subscription_enable' => false,
'link_to_external_url' => false,