'schemadb' => 'file:/net/nemein/discussion/config/schemadb_default.inc',
'schemadbs' => Array (
    'file:/net/nemein/discussion/config/schemadb_default.inc' => 'Discussion Default Schema Database',
),
'display_threads' => 15,
'display_posts'   => 10,
'display_pages'   => 10,
'display_thread_mode' => 'flat', // Currently supported options 'flat', 'threaded' and 'tree'
// flat-mode shows the posts in thread on one page, everyone after another (Jaiku-style)
// threaded-mode shows the post in thread on one page, but intented
// tree-mode shows only one post at a time and a tree-view on top or under the message
'display_tree_position' => 'bottom', // Is the tree of posts in thread on what side we show the tree. Two options, 'top' or 'bottom'
// 'allow_user_change_viewmode' => false, //if true, we'll let user to change the thread mode, not supported yet
'display_tree_keys' => 'guid,id,subject,sender,sendername,senderurl,created',  // comma separated string containing all the keys you need to show in the tree
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
'api_email_silently_ignore_duplicate' => false,
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

'venetsia_import_url' => '',
'venetsia_import_forum' => '',
'venetsia_import_series' => array(),