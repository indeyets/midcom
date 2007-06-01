"config" => array (
    "name"        => "config",
    "description" => "Default Configuration Schema", /* This is a topic */
    "locktimeout" => 0, /* Don't lock this, we display the edit view always */
    "fields"      => array (
        "root_event" => array (
            "description" => "root event guid",
            "datatype" => "text",
            "location" => "config",
            "config_domain" => "net.nemein.internalorders",
            "config_key" => "root_event",
        ),
        "user_group" => array (
            "description" => "user group guid",
            "datatype" => "text",
            "location" => "config",
            "config_domain" => "net.nemein.internalorders",
            "config_key" => "user_group",
        ),
        "admin_group" => array (
            "description" => "admin group guid",
            "datatype" => "text",
            "location" => "config",
            "config_domain" => "net.nemein.internalorders",
            "config_key" => "admin_group",
        ),
    )
),