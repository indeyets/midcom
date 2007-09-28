// Schema database to use
'schemadb' => 'file:/net/nemein/bannedwords/config/schemadb_default.inc',
'schemadbs'      => Array
(
    'file:/net/nemein/bannedwords/config/schemadb_default.inc' => 'Default Schema Database',
),


/**
 * These configure how to show the censored words.
 * Default is "***Censored***"
 */
'censored_start'   => '***',
'censored_end'     => '***',
'censored_word_id' => 'censored',
'censored_custom_word' => '',

/**
 * Global word libary is in sg0 and can be used by 
 * any website. Local library is only useable by
 * the current sitegroup
 */
'use_global_word_library' => true,
'use_local_word_library'  => true,

