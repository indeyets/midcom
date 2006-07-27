"config" => array (
 "name"        => "config",
 "description" => "Default Configuration Schema", /* This is a topic */
 "locktimeout" => 0, /* Don't lock this, we display the edit view always */
 "fields"      => array (
  
  /**** Display settings ****/
  
  "index_list_old" => array (
   "description" => $GLOBALS["view_l10n"]->get("list old items in index"),
   "datatype" => "text",
   "location" => "config",
   "config_domain" => "de.linkm.newsticker",
   "config_key" => "index_list_old",
   "widget" => "config_radiobox",
   "widget_radiobox_choices" => Array (
    "" => $GLOBALS["view_l10n_midcom"]->get("default setting"),
    "1" => $GLOBALS["view_l10n_midcom"]->get("yes"),
    "0" => $GLOBALS["view_l10n_midcom"]->get("no"),
   ),
   "start_fieldgroup" => Array ( "title" => $GLOBALS["view_l10n"]->get("display settings") ),
  ),

  "enable_details" => array (
   "description" => $GLOBALS["view_l10n"]->get("enable details"),
   "datatype" => "text",
   "location" => "config",
   "config_domain" => "de.linkm.newsticker",
   "config_key" => "enable_details",
   "widget" => "config_radiobox",
   "widget_radiobox_choices" => Array (
    "" => $GLOBALS["view_l10n_midcom"]->get("default setting"),
    "1" => $GLOBALS["view_l10n_midcom"]->get("yes"),
    "0" => $GLOBALS["view_l10n_midcom"]->get("no"),
   ),
  ),

  "count" => array (
   "description" => $GLOBALS["view_l10n"]->get("index count"),
   "datatype" => "text",
   "location" => "config",
   "config_domain" => "de.linkm.newsticker",
   "config_key" => "count",
  ),
  
  "sort_order" => array (
   "description" => $GLOBALS["view_l10n"]->get("sort order"),
   "datatype" => "text",
   "location" => "config",
   "config_domain" => "de.linkm.newsticker",
   "config_key" => "sort_order",
  ),
  
  "enable_subdirs" => array (
   "description" => $GLOBALS["view_l10n"]->get("enable subdirectories"),
   "datatype" => "text",
   "location" => "config",
   "config_domain" => "de.linkm.newsticker",
   "config_key" => "enable_subdirs",
   "widget" => "config_radiobox",
   "widget_radiobox_choices" => Array (
    "" => $GLOBALS["view_l10n_midcom"]->get("default setting"),
    "1" => $GLOBALS["view_l10n_midcom"]->get("yes"),
    "0" => $GLOBALS["view_l10n_midcom"]->get("no"),
   ),
  ),

  "enable_filters_get" => array (
   "description" => $GLOBALS["view_l10n"]->get("enable filters get"),
   "datatype" => "text",
   "location" => "config",
   "config_domain" => "de.linkm.newsticker",
   "config_key" => "enable_filters_get",
   "widget" => "config_radiobox",
   "widget_radiobox_choices" => Array (
    "" => $GLOBALS["view_l10n_midcom"]->get("default setting"),
    "1" => $GLOBALS["view_l10n_midcom"]->get("yes"),
    "0" => $GLOBALS["view_l10n_midcom"]->get("no"),
   ),
  ),

  "enable_datefilters_get" => array (
   "description" => $GLOBALS["view_l10n"]->get("enable date filters get"),
   "datatype" => "text",
   "location" => "config",
   "config_domain" => "de.linkm.newsticker",
   "config_key" => "enable_datefilters_get",
   "widget" => "config_radiobox",
   "widget_radiobox_choices" => Array (
    "" => $GLOBALS["view_l10n_midcom"]->get("default setting"),
    "1" => $GLOBALS["view_l10n_midcom"]->get("yes"),
    "0" => $GLOBALS["view_l10n_midcom"]->get("no"),
   ),
  ),

  "enable_datefilters_path" => array (
   "description" => $GLOBALS["view_l10n"]->get("enable date filters path"),
   "datatype" => "text",
   "location" => "config",
   "config_domain" => "de.linkm.newsticker",
   "config_key" => "enable_datefilters_path",
   "widget" => "config_radiobox",
   "widget_radiobox_choices" => Array (
    "" => $GLOBALS["view_l10n_midcom"]->get("default setting"),
    "1" => $GLOBALS["view_l10n_midcom"]->get("yes"),
    "0" => $GLOBALS["view_l10n_midcom"]->get("no"),
   ),
   "end_fieldgroup" => "",
  ),
  
  /**** RSS Stuff ****/

  "rss_enable" => array (
   "description" => $GLOBALS["view_l10n"]->get("rss_enable"),
   "datatype" => "text",
   "location" => "config",
   "config_domain" => "de.linkm.newsticker",
   "config_key" => "rss_enable",
   "widget" => "config_radiobox",
   "widget_radiobox_choices" => Array (
    "" => $GLOBALS["view_l10n_midcom"]->get("default setting"),
    "1" => $GLOBALS["view_l10n_midcom"]->get("yes"),
    "0" => $GLOBALS["view_l10n_midcom"]->get("no"),
   ),
   "start_fieldgroup" => Array ( "title" => $GLOBALS["view_l10n"]->get("rss export settings") ),
  ),

  "rss_count" => array (
   "description" => $GLOBALS["view_l10n"]->get("rss_count"),
   "datatype" => "text",
   "number_precision" => 0,
   "location" => "config",
   "config_domain" => "de.linkm.newsticker",
   "config_key" => "rss_count",
  ),

  "rss_title" => array (
   "description" => $GLOBALS["view_l10n"]->get("rss_title"),
   "datatype" => "text",
   "location" => "config",
   "config_domain" => "de.linkm.newsticker",
   "config_key" => "rss_title",
  ),

  "rss_description" => array (
   "description" => $GLOBALS["view_l10n"]->get("rss_description"),
   "datatype" => "text",
   "location" => "config",
   "config_domain" => "de.linkm.newsticker",
   "config_key" => "rss_description",
  ),

  "rss_webmaster" => array (
   "description" => $GLOBALS["view_l10n"]->get("rss_webmaster"),
   "datatype" => "text",
   "location" => "config",
   "config_domain" => "de.linkm.newsticker",
   "config_key" => "rss_webmaster",
  ),

  "rss_language" => array (
   "description" => $GLOBALS["view_l10n"]->get("rss_language"),
   "datatype" => "text",
   "location" => "config",
   "config_domain" => "de.linkm.newsticker",
   "config_key" => "rss_language",
  ),
  "rss_include_image" => array (
   "description" => $GLOBALS["view_l10n"]->get("rss_include_image"),
   "datatype" => "text",
   "location" => "config",
   "config_domain" => "de.linkm.newsticker",
   "config_key" => "rss_include_image",
   "widget" => "config_radiobox",
   "widget_radiobox_choices" => Array (
    "" => $GLOBALS["view_l10n_midcom"]->get("default setting"),
    "1" => $GLOBALS["view_l10n_midcom"]->get("yes"),
    "0" => $GLOBALS["view_l10n_midcom"]->get("no"),
   ),
   "end_fieldgroup" => "",
  ),



  /**** Public Publishing settings ****/


  "pp_enable" => array (
   "description" => $GLOBALS["view_l10n"]->get("enable public posting"),
   "datatype" => "text",
   "location" => "config",
   "config_domain" => "de.linkm.newsticker",
   "config_key" => "pp_enable",
   "widget" => "config_radiobox",
   "widget_radiobox_choices" => Array (
    "" => $GLOBALS["view_l10n_midcom"]->get("default setting"),
    "1" => $GLOBALS["view_l10n_midcom"]->get("yes"),
    "0" => $GLOBALS["view_l10n_midcom"]->get("no"),
   ),
   "start_fieldgroup" => Array ( "title" => $GLOBALS["view_l10n"]->get("public posting") ),
  ),

  "pp_schema" => array (
   "description" => $GLOBALS["view_l10n"]->get("public posting schema"),
   "datatype" => "text",
   "location" => "config",
   "config_domain" => "de.linkm.newsticker",
   "config_key" => "pp_schema",
   "widget" => "schemaselect",
   "widget_schemaselect_linkto" => "schemadb",
   "widget_schemaselect_default_schemapath" => "file:/de/linkm/newsticker/config/schemadb_default.inc",
  ),

  "pp_user" => array (
   "description" => $GLOBALS["view_l10n"]->get("public posting user"),
   "datatype" => "text",
   "location" => "config",
   "config_domain" => "de.linkm.newsticker",
   "config_key" => "pp_user",
  ),

  "pp_pass" => array (
   "description" => $GLOBALS["view_l10n"]->get("public posting password"),
   "datatype" => "text",
   "location" => "config",
   "config_domain" => "de.linkm.newsticker",
   "config_key" => "pp_pass",
   "widget_text_inputstyle" => "password",
   "end_fieldgroup" => "",
  ),

  /**** Schema settings ****/
  
  "schemadb" => array (
   "description" => $GLOBALS["view_l10n"]->get("schema database"),
   "datatype" => "text",
   "location" => "config",
   "config_domain" => "de.linkm.newsticker",
   "config_key" => "schemadb",
   "start_fieldgroup" => Array ( "title" => $GLOBALS["view_l10n"]->get("advanced schema and data settings") ),
  ),
  
  "create_name_from_title" => array (
   "description" => $GLOBALS["view_l10n"]->get("create name from title"),
   "datatype" => "text",
   "location" => "config",
   "config_domain" => "de.linkm.newsticker",
   "config_key" => "create_name_from_title",
   "widget" => "config_radiobox",
   "widget_radiobox_choices" => Array (
    "" => $GLOBALS["view_l10n_midcom"]->get("default setting"),
    "1" => $GLOBALS["view_l10n_midcom"]->get("yes"),
    "0" => $GLOBALS["view_l10n_midcom"]->get("no"),
   ),
  ),

  "enable_blogging_apis" => array (
   "description" => $GLOBALS["view_l10n"]->get("enable blogging apis"),
   "datatype" => "text",
   "location" => "config",
   "config_domain" => "de.linkm.newsticker",
   "config_key" => "enable_blogging_apis",
   "widget" => "config_radiobox",
   "widget_radiobox_choices" => Array (
    "" => $GLOBALS["view_l10n_midcom"]->get("default setting"),
    "1" => $GLOBALS["view_l10n_midcom"]->get("yes"),
    "0" => $GLOBALS["view_l10n_midcom"]->get("no"),
   ),
  ),

  "symlink_topic" => array (
   "description" => $GLOBALS["view_l10n"]->get("symlink_topic"),
   "datatype" => "text",
   "location" => "config",
   "config_domain" => "de.linkm.newsticker",
   "config_key" => "symlink_topic",
   "widget" => "select",
   "widget_select_choices" => de_linkm_newsticker_symlink_topic_list(),
   "end_fieldgroup" => "",
  ),
  
 )
),