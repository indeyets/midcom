// schema to use, must have an entry 'comment', which is used.
'schemadb' => 'file:/net/nehmer/comments/config/schemadb_default.inc',

// Always protect submission with a captcha
'use_captcha' => false,

// Protect submission with a captcha if anonymous only.
'use_captcha_if_anonymous' => true,

// The captcha config to use
'captcha_config' => Array(),

// Allow anonymous posting.
'allow_anonymous' => true,

// Rating mode
'ratings_enable' => false,
'ratings_cache_to_object' => false,
'ratings_cache_to_object_property' => 'score',
'ratings_cache_to_object_property_metadata' => false,
'ratings_cache_total' => false,