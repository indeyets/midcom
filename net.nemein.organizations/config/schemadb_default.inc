"default" => array (

    "name"        => "default",
    "description" => "Default organization record",
    "fields"      => array (
        "name" => array (
            "description" => "Index name",
            "datatype"    => "text",
            "location"    => "name",
        ),
        "official" => array (
            "description" => "Official name",
            "datatype"    => "text",
            "location"    => "official"
        ),
        "homepage" => array (
            "description" => "Web site",
            "datatype"    => "text",
            "location"    => "homepage"
        ),
        "email" => array (
            "description" => "Email address",
            "datatype"    => "text",
            "location"    => "email"
        ),
        "description" => array (
            "description" => "Description",
            "datatype"    => "text",
            "widget_text_inputstyle"=>"longtext"
        ),
        "logo" => array (
            "description" => "Logo",
            "datatype"    => "image",
            "location"    => "attachment",
            "widget_image_simple" => true,
        ),
        "inrandom" => array (
            "description" => "Allow item to be listed in random filtering",
            "datatype"    => "boolean",
            "location"    => "config",
            "config_domain" => "net.nemein.organizations",
            "config_key"  => "in_random",
        )
    )

)
