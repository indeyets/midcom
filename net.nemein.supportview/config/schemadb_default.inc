"default" => array (

    "name"        => "default",
    "description" => "Database entry",
    "fields"      => array (
        "id" => array (
            "description" => "Database ID",
            "datatype"    => "text",
            "location"    => "id",
            "hidden"      => true,
            "readonly"     => true
        ),
        "name" => array (
            "description" => "URL name",
            "datatype"    => "text",
            "location"    => "name",
            "net_nemein_simpledb_list" => false
        ),
        "title" => array (
            "description" => "Entry name",
            "datatype"    => "text",
            "location"    => "title",
        ),
        "extra1" => array (
            "description" => "Searchable 1",
            "datatype"    => "text",
            "location"    => "extra1",
        ),
        "extra2" => array (
            "description" => "Searchable 2",
            "datatype"    => "text",
            "location"    => "extra2",
        ),
        "extra3" => array (
            "description" => "Searchable 3",
            "datatype"    => "text",
            "location"    => "extra3",
        ),
        "content" => array (
            "description" => "Description",
            "datatype"    => "text",
            "location"    => "content",
            "widget_text_inputstyle" => "longtext"
        )
    )

)