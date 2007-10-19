// Schema database to use
'schemadb' => 'file:/net/nehmer/static/config/schemadb_default.inc',
'schemadbs'      => Array
(
    'file:/net/nehmer/static/config/schemadb_default.inc' => 'Default Schema Database',
),

// A valid sort order usable with QB
'sort_order' => 'score',

// Generate a directory index if no index article is found?
'autoindex' => false,

// If not in autoindex mode, show the index article in NAP?
'indexinnav' => false,

// Automatically approve new articles ?
'auto_approve' => false,

// Use the content from another topic instead of the current one.
// Do not overwrite this except on a per-topic level!!!
'symlink_topic' => null,

// Set this to false if you want all users to have full control over URL names.
'simple_name_handling' => true,

// Set this to true if you want to enable on-site AJAX editing
'enable_ajax_editing' => false,

// Page linking system
'enable_article_links' => true,
'enable_editing_of_linked_articles' => false,
'include_linked_articles_to_feeds' => true,

// Creation callback snippet for defining site-specific function
'callback_snippet' => null,

// Callback function will be called after creation e.g. to provide interface for page linking system
// $callback_function($article, $topic)
'callback_function' => null,
