"config" => array (
 "name"        => "config",
 "description" => "Default Configuration Schema", /* This is a topic */
 "locktimeout" => 0, /* Don't lock this, we display the edit view always */
 "fields"      => array (
  
  /**** Display settings ****/
   "sort_order" => array (
    "description" => "sort_order",
    "datatype" => "text",
    "location" => "config",
    "config_domain" => "de.linkm.taviewer",
    "config_key" => "sort_leaves",
    "widget" => "select",
    "widget_select_choices" => Array (
        
            'score'             => "score",
            'created'           => "created",
            'reverse created'   => "reverse created",
            'name'              => "name"           ,
            'reverse name'      => "reverse name"   ,
            /* use the "edit score" screen! */
    //      'reverse score'   => "reverse score"  ,
            'revised'           => "revised",
            'reverse revised'   => "reverse revised",
            'calendar'          => "calendar",
            'reverse calendar'  => "reverse calendar"
        ),
    ),

    
  "autoindex" => array (
   "description" => "activate autoindex",
   "helptext" => "autoindex-notes",
   "datatype" => "text",
   "location" => "config",
   "config_domain" => "de.linkm.taviewer",
   "config_key" => "autoindex",
   "widget" => "config_radiobox",
   "widget_radiobox_choices" => Array (
    "" => "default setting",
    "1" => "yes",
    "0" => "no",
   ),
   "start_fieldgroup" => Array ( "title" => "display settings" ),
  ),
  
  "auto_approved" => array (
   "description" => "auto_approved",
   "datatype" => "text",
   "location" => "config",
   "config_domain" => "de.linkm.taviewer",
   "config_key" => "auto_approved",
   "widget" => "config_radiobox",
   "widget_radiobox_choices" => Array (
    "" => "default setting",
    "1" => "yes",
    "0" => "no",
   ),
  ),
  
  "enable_aegir_symlink_article" => array (
   "description" => "enable_aegir_symlink_article",
   "datatype" => "text",
   "location" => "config",
   "config_domain" => "de.linkm.taviewer",
   "config_key" => "enable_aegir_symlink_article",
   "widget" => "config_radiobox",
   "widget_radiobox_choices" => Array (
    "" => "default setting",
    "1" => "yes",
    "0" => "no",
   ),
   "end_fieldgroup" => "",
  ),

  
  /**** Schema settings ****/
  
  "schemadb" => array (
   "description" => "schemadb",
   "datatype" => "text",
   "location" => "config",
   "config_domain" => "de.linkm.taviewer",
   "config_key" => "schemadb",
   "widget" => "select",
   "widget_select_choices" => $GLOBALS['de_linkm_taviewer_schemadbs'],
   "start_fieldgroup" => Array ( "title" => "schema settings" ),
  ),
  
  "symlink_topic" => array (
   "description" => "symlink_topic",
   "datatype" => "text",
   "location" => "config",
   "config_domain" => "de.linkm.taviewer",
   "config_key" => "symlink_topic",
   "widget" => "select",
   "widget_select_choices" => de_linkm_taviewer_symlink_topic_list(),
   "end_fieldgroup" => "",
  ),
  
 )
),
