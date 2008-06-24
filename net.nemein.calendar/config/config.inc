'master_event'         => null,   // Event under which all root events are placed
'symlink_topic'        => null,
'schemadb'             => 'file:/net/nemein/calendar/config/schemadb_default.inc',
'schemadb_config'      => 'file:/net/nemein/calendar/config/schemadb_config.inc',
'schemadb_feed'        => 'file:/net/nemein/rss/config/schemadb_default.inc',

'index_count'          => 10,      // How many upcoming events to display on index
'rss_count'            => 10,      // How many upcoming events to display on index
'type_filter_upcoming' => null,   // What type (int) to filter welcome page items with
'type_filter_show_old' => false,  // Should the type filtered results include also old events
'list_from_master'     => false,  // Whether to list events from subdirectories of the master event. Disables creation
'list_from_topics'     => null,   // List of the event topics displayed in this topic
'enable_filters'       => false,  // Whether to enable GET filters
'show_events_locally'  => true,   // Whether to show events locally instead of via permalink
'list_started'         => true,   // Whether to list events that have already started

'enable_ajax_editing'  => true,
'javascript_hover'     => true,

'archive_enable' => true,
'archive_year_order'   => 'ASC',  // Order for years in archive welcome page
'archive_in_navigation' => true,
'archive_years_in_navigation' => false,

'show_navigation_pseudo_leaves' => true,   // Set this to false if you want to hide archive navigation items

'categories' => '',
'categories_in_navigation' => false,

'rss_subscription_enable' => false,
'rss_sort_rules' => array
(
    'start' => 'ASC',
),


'language' => null,
