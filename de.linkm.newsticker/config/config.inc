"schemadb"       => "file:/de/linkm/newsticker/config/schemadb_default.inc",
"index_list_old" => TRUE,
"enable_details" => TRUE,
"count"          => 4,
"sort_order"     => "reverse created",
"enable_subdirs" => FALSE,
"enable_filters_get" => FALSE, // Enable text/value filters as GET parameters
"enable_datefilters_get" => FALSE, // Enable date filters as GET parameters
"enable_datefilters_path" => FALSE, // Enable date filters as in the path in format YYYY/MM/DD
"create_name_from_title" => TRUE,  // Generate filenames for articles based on the title
"enable_blogging_apis" => TRUE, // Enable MetaWebLog and Blogger remote authoring APIs

// RSS export
"rss_enable"      => TRUE,  // enable RSS export on $topic/rss.xml or $topic/rss/$count
"rss_count"       => 10,    // show that many items by default (if called "rss.xml")
"rss_title"       => "",    // RSS feed title (empty == Topic title)
"rss_description" => "",
"rss_webmaster"   => "",
"rss_language"    => "",
"rss_include_image" => FALSE, // Include the full-scale image from "image" field to RSS item instead of possible thumbnail

// Public publishing interface
"pp_enable" => FALSE, // do not enable the "publish new item" interface on-site
"pp_schema" => "guestbook", // Will use the schemadb selected above
"pp_user"   => "", // leave empty to enforce on-site authentication
"pp_pass"   => "", 

// Symlinking
"symlink_topic"  => null, /* Use the content from another topic instead of the current one. 
                           * Do not overwrite this except on a per-topic level!!! 
                           */