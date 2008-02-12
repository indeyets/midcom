'schemadb' => 'file:/net/nemein/discussion/config/schemadb_default.inc',
'schemadbs' => Array (
    'file:/net/nemein/discussion/config/schemadb_default.inc' => 'Discussion Default Schema Database',
),
'display_threads' => 15,
'display_posts'   => 10,
'display_pages'   => 10,
'display_thread_mode' => 'flat', // Currently supported options 'flat' and 'threaded'
'new_message_status_anon' => 4,
'new_message_status_user' => 5,
'latest_item_count' => 10,
'order_threads_by' => 'latestposttime',
/**
 * format: property/header  => array of search/replace rules for preg_replace
 * rules are executed in order added (ie specifying key numbers will not change order)
 */
'email_import_rewrites' => array
(
    'subject' => array
    (
        // remove typical [list-name] -marker from beginning of subject
        array
        (
            'search' => '/^\[.*?\]\s*/',
            'replace' => '',
        ),
    ),
    'from' => array
    (
        // de-obfuscate sender address from form 'namepart<space>at<space>domainpart<space>(fullname)' to standard form
        array
        (
            'search' => '/(.*?)\sat\s(.*?)\s\((.*)\)/',
            'replace' => '"$3" <$1@$2>',
        ),
    ),
),
'api_email_enable' => false,
'api_email_strict_parent' => false,
'api_email_use_force' => false,
'api_email_duplicate_is_fatal' => true,
/* override these only on topic level! */
'email_out_enable' => false,
'email_out_address' => '',
'email_out_from' => '',
'email_out_subject_prefix' => '',

'auto_quote_on_reply' => false,

'autoapprove' => false,

// GUIDs of moderators to notify
'moderators' => '',

'remove_unpopular_threads' => false,

'popularity_favourites_enable' => true,
'popularity_favourites_modifier' => 1,
'popularity_buries_enable' => true,
'popularity_buries_modifier' => -2,
'popularity_posts_enable' => true,
'popularity_posts_modifier' => 2,
'popularity_badpost_modifier' => -1,

'create_thread_from_quickpoll' => false,
'create_thread_from_quickpoll_forum' => '',