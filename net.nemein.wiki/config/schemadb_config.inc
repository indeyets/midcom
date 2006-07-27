"config" => array
(
    "name"        => "config",
    "description" => "Default Configuration Schema", /* This is a topic */
    "locktimeout" => 0, /* Don't lock this, we display the edit view always */
    "fields"      => array
    (
        /* Schema settings */
        "schemadb" => array
        (
            "description" => "schema database",
            "datatype" => "text",
            "location" => "config",
            "config_domain" => "net.nemein.wiki",
            "config_key" => "schemadb",
            "start_fieldgroup" => array
            ( 
                "title" => "advanced schema and data settings"
            ),
            "end_fieldgroup" => "",
        ),
    ),
),