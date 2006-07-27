"config" => array (
    "name"        => "config",
    "description" => "Default Configuration Schema", /* This is a topic */
    "locktimeout" => 0, /* Don't lock this, we display the edit view always */
    "fields"      => array (

        /**** Display settings ****/
        "sort_order" => array (
            "description" => "sort order",
            "datatype" => "text",
            "location" => "config",
            "config_domain" => "net.siriux.photos",
            "config_key" => "sort_order",
            "start_fieldgroup" => array (
                "title" => "display settings"
            ) 
        ),

        "index_cols" => array (
            "description" => "index_cols",
            "datatype" => "text",
            "number_precision" => 0,
            "location" => "config",
            "config_domain" => "net.siriux.photos",
            "config_key" => "index_cols",
        ),

        "index_rows" => array (
            "description" => "index_rows",
            "datatype" => "text",
            "number_precision" => 0,
            "location" => "config",
            "config_domain" => "net.siriux.photos",
            "config_key" => "index_rows",
        ),

        "list_subgalleries" => array (
            "description" => "list subgalleries in index",
            "datatype" => "text",
            "location" => "config",
            "config_domain" => "net.siriux.photos",
            "config_key" => "list_subgalleries",
            "widget" => "config_radiobox",
            "widget_radiobox_choices" => array (
                "" => "default setting",
                "1" => "yes",
                "0" => "no",
            ),
        ),

        "enable_flash_notes" => array (
            "description" => "enable flash notes",
            "datatype" => "text",
            "location" => "config",
            "config_domain" => "net.siriux.photos",
            "config_key" => "enable_flash_notes",
            "widget" => "config_radiobox",
            "widget_radiobox_choices" => array (
                "" => "default setting",
                "1" => "yes",
                "0" => "no",
            ),
        ),

        "create_urlname_from_title" => array (
            "description" => "create url names from titles",
            "datatype" => "text",
            "location" => "config",
            "config_domain" => "net.siriux.photos",
            "config_key" => "create_urlname_from_title",
            "widget" => "config_radiobox",
            "widget_radiobox_choices" => array (
                "" => "default setting",
                "1" => "yes",
                "0" => "no",
            ),
            "end_fieldgroup" => "",
        ),
  
        /**** Metadata settings ****/
        "description" => array (
            "description" => "description",
            "datatype" => "text",
            "location" => "config",
            "config_domain" => "net.siriux.photos",
            "config_key" => "description",
            "start_fieldgroup" => array (
                "title" => "metadata settings"
            )
        ),
  
        "keywords" => array (
            "description" => "keywords",
            "datatype" => "text",
            "location" => "config",
            "config_domain" => "net.siriux.photos",
            "config_key" => "keywords",
            "end_fieldgroup" => "",
        ),
  
        /**** Image resize settings ****/
        "view_x" => array (
            "description" => "view_x",
            "datatype" => "text",
            "number_precision" => 0,
            "location" => "config",
            "config_domain" => "net.siriux.photos",
            "config_key" => "view_x",
            "start_fieldgroup" => array ( 
                "title" => "generated image settings"
            )
        ),

        "view_y" => array (
            "description" => "view_y",
            "datatype" => "text",
            "number_precision" => 0,
            "location" => "config",
            "config_domain" => "net.siriux.photos",
            "config_key" => "view_y",
        ),

        "thumb_x" => array (
            "description" => "thumb_x",
            "datatype" => "text",
            "number_precision" => 0,
            "location" => "config",
            "config_domain" => "net.siriux.photos",
            "config_key" => "thumb_x",
        ),

        "thumb_y" => array (
            "description" => "thumb_y",
            "datatype" => "text",
            "number_precision" => 0,
            "location" => "config",
            "config_domain" => "net.siriux.photos",
            "config_key" => "thumb_y",
            "end_fieldgroup" => "",
        ),
  
        /**** RSS Stuff ****/
        "rss_enable" => array (
            "description" => "rss_enable",
            "datatype" => "boolean",
            "location" => "config",
            "config_domain" => "net.siriux.photos",
            "config_key" => "rss_enable",
            "start_fieldgroup" => array (
                "title" => "rss export settings"
            ) 
        ),

        "rss_count" => array (
            "description" => "rss_count",
            "datatype" => "text",
            "number_precision" => 0,
            "location" => "config",
            "config_domain" => "net.siriux.photos",
            "config_key" => "rss_count",
        ),

        "rss_title" => array (
            "description" => "rss_title",
            "datatype" => "text",
            "location" => "config",
            "config_domain" => "net.siriux.photos",
            "config_key" => "rss_title",
        ),

        "rss_description" => array (
            "description" => "rss_description",
            "datatype" => "text",
            "location" => "config",
            "config_domain" => "net.siriux.photos",
            "config_key" => "rss_description",
        ),

        "rss_webmaster" => array (
            "description" => "rss_webmaster",
            "datatype" => "text",
            "location" => "config",
            "config_domain" => "net.siriux.photos",
            "config_key" => "rss_webmaster",
        ),

        "rss_language" => array (
            "description" => "rss_language",
            "datatype" => "text",
            "location" => "config",
            "config_domain" => "net.siriux.photos",
            "config_key" => "rss_language",
            "end_fieldgroup" => "",
        ),
  
        /**** Schema Settings ****/
        "schema_picture" => array (
            "description" => "schema_picture",
            "datatype" => "text",
            "location" => "config",
            "config_domain" => "net.siriux.photos",
            "config_key" => "schema_picture",
            "widget" => "schemaselect",
            "widget_schemaselect_linkto" => "schemadb",
            "widget_schemaselect_default_schemapath" => "file:/net/siriux/photos/config/schemadb_default.inc",
            "start_fieldgroup" => array ( 
                "title" => "data schema settings"
            )
        ),

        "schema_upload" => array (
            "description" => "schema_upload",
            "datatype" => "text",
            "location" => "config",
            "config_domain" => "net.siriux.photos",
            "config_key" => "schema_upload",
            "widget" => "schemaselect",
            "widget_schemaselect_linkto" => "schemadb",
            "widget_schemaselect_default_schemapath" => "file:/net/siriux/photos/config/schemadb_default.inc",
        ),

        "schemadb" => array (
            "description" => "schemadb",
            "datatype" => "text",
            "location" => "config",
            "config_domain" => "net.siriux.photos",
            "config_key" => "schemadb",
            "end_fieldgroup" => "",
        ),

        /**** EXIF settings ****/
        "exif_custom_fields" => array (
            "description" => "exif_custom_fields",
            "datatype" => "text",
            "location" => "config",
            "config_domain" => "net.siriux.photos",
            "config_key" => "exif_custom_fields",
            "start_fieldgroup" => array (
                "title" => "exif settings"
            )
        ),

        "force_exif_reader" => array (
            "description" => "force_exif_reader",
            "datatype" => "text",
            "location" => "config",
            "config_domain" => "net.siriux.photos",
            "config_key" => "force_exif_reader",
            "widget" => "select",
            "widget_select_size" => 1,
            "widget_select_choices" => array (
                "" => "force_exif_reader system default",
                "off" => "force_exif_reader autodetect",
                "jhead" => "force_exif_reader jhead",
                "php" => "force_exif_reader php",
            ),
        ),

        "disable_autosync_created" => array (
            "description" => "disable_autosync_created",
            "datatype" => "boolean",
            "location" => "config",
            "config_domain" => "net.siriux.photos",
            "config_key" => "disable_autosync_created",
            "end_fieldgroup" => "",
        ),
  

        /*** Upload post processing ***/
        "filter_chain" => array (
            "description" => "filter_chain",
            "datatype" => "text",
            "location" => "config",
            "config_domain" => "net.siriux.photos",
            "config_key" => "filter_chain",
            "start_fieldgroup" => array ( 
                "title" => "post processing"
            ),
            "end_fieldgroup" => "",
        ),
	"random_cache_expire" => array(
		"description" => "Random cache expire",
		"datatype" => "text",
		"location" => "config",
		"config_domain" => "net.siriux.photos",
		"config_key" => "random_cache_expire",
 	        "start_fieldgroup" => array (
                	"title" => "random cache expires"
            	),
            	"end_fieldgroup" => "",
        ),

    ),
)
/* Schema ends here */
