"sort_order"          => "created",
"create_urlname_from_title" => TRUE,
"enable_flash_notes"  => FALSE,
"list_subgalleries"   => TRUE,

// How many Thumnails to show in index
"index_cols" => 4,
"index_rows" => 4,

// scales
"view_x"  => 640,  // max scaled image width
"view_y"  => 640,  // max scaled image height
"thumb_x" => 130,  // max thumb width
"thumb_y" => 130,  // max thumb height

// RSS export
"rss_enable"      => FALSE,  // enable RSS export on $topic/rss.xml or $topic/rss/$count
"rss_count"       => 10,    // show that many items by default (if called "rss.xml")
"rss_title"       => "",    // RSS feed title (empty == Topic title)
"rss_description" => "",
"rss_webmaster"   => "",
"rss_language"    => "",

// Metadata
"description" => "",
"keywords"    => "",

// Schema stuff
"schema_picture" => "picture",
"schema_upload" => "upload",
"schemadb" => "file:/net/siriux/photos/config/schemadb_default.inc",

// EXIF READER
"exif_custom_fields" => "FNumber:fnumber,ISOSpeedRatings:iso,ExposureTime:exposure,Make:make,Model:model",
"force_exif_reader" => "off", /* Can be "jhead" or "php" to force the usage of the corresponding Reader */
"disable_autosync_created" => false, /* Don't automatically sync article created timestamps to EXIF data */

// Post Processing
"filter_chain" => "",
// Random cache expire
"random_cache_expire" => "0",
