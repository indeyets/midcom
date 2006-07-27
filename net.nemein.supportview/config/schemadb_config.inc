"config" => array (
    "name"        => "config",
    "description" => "Default Configuration Schema", /* This is a topic */
    "locktimeout" => 0, /* Don't lock this, we display the edit view always */
    "fields"      => array (
  
        /**** Display settings ****/
  
        "visible" => array (
            "description" => $GLOBALS["view_l10n"]->get("visible"),
            "datatype" => "text",
            "location" => "config",
            "config_domain" => "net.nemein.supportview",
            "config_key" => "visible",
            "widget" => "config_radiobox",
            "widget_radiobox_choices" => Array (
                "" => $GLOBALS["view_l10n_midcom"]->get("default setting"),
                "1" => $GLOBALS["view_l10n_midcom"]->get("yes"),
                "0" => $GLOBALS["view_l10n_midcom"]->get("no"),
            ),
            "start_fieldgroup" => Array ( 
                "title" => $GLOBALS["view_l10n"]->get("display settings") 
            ),
            "end_fieldgroup" => "",
        ),

        /**** TechSupport settings ***/

        "tt_root_topic_name" => array (
            "description" => $GLOBALS["view_l10n"]->get("techsupport root topic name"),
            "datatype" => "text",
            "location" => "config",
            "config_domain" => "net.nemein.supportview",
            "config_key" => "tt_root_topic_name",
            "start_fieldgroup" => Array ( 
                "title" => $GLOBALS["view_l10n"]->get("techsupport settings")
            ),
        ),  
        
        "require_email" => array (
            "description" => $GLOBALS["view_l10n"]->get("require emails to be sent"),
            "datatype" => "text",
            "location" => "config",
            "config_domain" => "net.nemein.supportview",
            "config_key" => "require_email",
            "widget" => "config_radiobox",
            "widget_radiobox_choices" => Array (
                "" => $GLOBALS["view_l10n_midcom"]->get("default setting"),
                "1" => $GLOBALS["view_l10n_midcom"]->get("yes"),
                "0" => $GLOBALS["view_l10n_midcom"]->get("no"),
            ),
        ),
  
        "client_domain" => array (
            "description" => $GLOBALS["view_l10n"]->get("client domain name"),
            "datatype" => "text",
            "location" => "config",
            "config_domain" => "net.nemein.supportview",
            "config_key" => "client_domain",
            "end_fieldgroup" => "",
        ),
    )
),