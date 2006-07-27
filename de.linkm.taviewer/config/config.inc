// NB! MAKE SURE NOT TO USE TABS IN THIS SNIPPET!
"schemadb"       => "file:/de/linkm/taviewer/config/schemadb_default.inc",
"schemadbs"      => Array (
    "file:/de/linkm/taviewer/config/schemadb_default.inc" => "Default Schema Database",
),
"sort_order"    => 'score', /* default from before time  */
"autoindex"      => 0, 	   /* Generate a directory index if no index article is found? */
"auto_approved"  => FALSE, /* Automatically approve new articles ? */

"symlink_topic"  => null, /* Use the content from another topic instead of the current one. 
                           * Do not overwrite this except on a per-topic level!!! 
                           */

"default_schema" => null, /* UNUSED: Some old installations list this parameter in the topic
                           * config, it is not used, but we list it here, so that component
                           * initialization does not fail in case it is there.
                           */

"enable_aegir_symlink_article" => 0, /* Support for Aegir's symlink articles */
			             /* Overrides the abstract and content fields */
