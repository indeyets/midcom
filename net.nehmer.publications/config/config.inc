// Schema database to use, indexing settings
'schemadb' => 'file:/net/nehmer/publications/config/schemadb_default.inc',
'schemadbs'      => Array
(
    'file:/net/nehmer/publications/config/schemadb_default.inc' => 'Default Schema Database',
),
'master_topic' => null,

// Page sizes: latest and index
'latest_entries' => 5,
'index_entries' => 5,
'index_order' => 'title',

// Welcome page handler
'welcome_handler' => 'index', // one of "latest", "index", "categories"

// RSS export
'rss_enable' => true,  // enable RSS export on rss/latest.xml
'rss_count' => 20, // show that many items by default (if called 'rss.xml' without any count)
'rss_title' => null,  // RSS feed title, leave null to use the topic title
'rss_description' => '',
'rss_webmaster' => '',
'rss_language' => '',
'rss_use_content' => true, // Set this to false to use the abstract field instead of the content field.

// categories are grouped
// static group index assignment recommended for data integrity reasons
// full cat ids are constructed as "{$groupid}-{$key}" to ensure uniqueness
// group identifiers must match [a-zA-Z0-9]+
// configuration like DM select + title
// callbacks need to provide local cat ids.
// a defined callback will override options.
// args field must not be omitted.
// you can leave title null, gives simple localized title.
// fields must be named category{$groupid} to make automatic schema adjusts work.
// add 'internal' => true if you want to prevent on-site listing.
// if you need separate option listings for onsite indexes (vs. DM2), add site_ prefixed option*
'categories' => Array
(
    1 => Array
    (
        'title' => 'Example Category Group',
        'options' => Array ('Example category listing', 'must be overridden in sitegroup-config'),
        'option_callback' => null, // 'some_callback_classs',
        'option_callback_args' => null, //'some_callback argument'
    ),
),

// Filter options

// The filter list contains a list of fully qualified categories for which should be filtered. Since it should
// be easily maintainable in parameter fields. Only entries listed in all categories given will be
// shown in this category. Entries will be automatically added to these categories when created.
// It is not validated during edits, to avoid problems, it is recommended you hide such fieldgroups.
// The syntax is as follows:
// $category_key-$category_value:$category_key-$category_value:...
// Note that only minimal checks are done during runtime for the correctness of this variable
// to keep the performance up.
'default_categories' => null,

// Fields hidden, use this to hide category groups whose contents are fixed. This is a comma separated
// list of fieldnames
'hide_fields' => null,