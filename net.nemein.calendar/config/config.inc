'master_event'         => null,   // Event under which all root events are placed
'root_event'           => null,   // Root event of a particular topic
'schemadb'             => 'file:/net/nemein/calendar/config/schemadb_default.inc',

'index_count'          => 5,      // How many upcoming events to display on index
'rss_count'            => 10,      // How many upcoming events to display on index
'type_filter_upcoming' => null,   // What type (int) to filter welcome page items with
'list_from_master'     => false,  // Whether to list events from subdirectories of the master event. Disables creation
'enable_filters'       => false,  // Whether to enable GET filters
'show_events_locally'  => true,   // Whether to show events locally instead of via permalink
'list_started'         => true,   // Whether to list events that have already started

'enable_ajax_editing'  => true,
'javascript_hover'     => true,

'archive_enable' => true,
'archive_year_order'   => 'ASC',  // Order for years in archive welcome page

'show_navigation_pseudo_leaves' => true,   // Set this to false if you want to hide archive navigation items

'categories' => '',